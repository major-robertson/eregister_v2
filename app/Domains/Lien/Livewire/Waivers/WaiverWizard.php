<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Support\SigningLink;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\Actions\SendWaiverForSignature;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\Actions\GenerateWaiver;
use App\Domains\Lien\Waivers\Actions\StoreSignedCopy;
use App\Domains\Lien\Waivers\ResolvedWaiverForm;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverFormUnavailable;
use App\Domains\Lien\Waivers\WaiverIntent;
use App\Domains\Lien\Waivers\WaiverNurture;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Services\GooglePlacesService;
use App\Support\Analytics\Gtag;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Five-step waiver builder: direction fork, project pick, guided type
 * selector (with a power-user grid of the state's statutory forms), details,
 * then review. Reaching review auto-saves the waiver as a draft (metered on
 * the free tier — going back and forth updates the same draft rather than
 * consuming another slot), and the review actions — download or send for
 * e-signature — operate on that saved draft. When the free allowance is
 * exhausted the review still renders but nothing persists, and both actions
 * open the upgrade modal instead.
 *
 * The waiver's state is frozen from the project's jobsite_state at save so a
 * later project edit can't silently change which state's form it claims to be.
 */
class WaiverWizard extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public int $totalSteps = 5;

    // Deep-link locks: a project/kind handed in via the URL (the project page's
    // waiver actions) fixes those steps, so the wizard skips them.
    public bool $projectLocked = false;

    public bool $kindLocked = false;

    // Step 1: Direction fork
    public string $direction = '';

    // Step 2: Project (?project= deep link from the project page)
    #[Url('project')]
    public string $projectId = '';

    // ?kind= deep link (the project page's four waiver-type cards) preseeds $kind.
    #[Url('kind')]
    public string $presetKind = '';

    // Step 3: Waiver type. Guided answers map onto the four canonical kinds.
    public string $paymentType = '';

    public string $paymentReceived = '';

    public string $kind = '';

    /** Shown when a guided answer landed on a disabled kind and was redirected. */
    public ?string $redirectNotice = null;

    // Step 4: Details
    public ?string $amount = null;

    public ?string $through_date = null;

    public ?string $invoice_number = null;

    public ?string $check_maker = null;

    public ?string $check_number = null;

    public ?string $exceptions = null;

    /**
     * Collected only when the resolved form is invalid without a formal legal
     * description (MO's residential unconditional final, § 429.016.27);
     * prefilled from the project and hidden for every other form.
     */
    public ?string $legal_description = null;

    // Counterparty
    public string $contactId = '';

    public bool $showContactModal = false;

    /** When set, the contact modal edits this contact instead of creating one. */
    public ?int $editingContactId = null;

    public ?string $contact_company = null;

    public ?string $contact_first_name = null;

    public ?string $contact_last_name = null;

    public ?string $contact_email = null;

    public ?string $contact_phone = null;

    public ?string $contact_address1 = null;

    public ?string $contact_address2 = null;

    public ?string $contact_city = null;

    public ?string $contact_state = null;

    public ?string $contact_county = null;

    public ?string $contact_zip = null;

    // Owner modal: adds or edits the project's owner party without leaving
    // the wizard. Every waiver requires an owner (the forms identify who owns
    // the property), but only the name is required here — PartyManager still
    // demands the full mailing address when a lien filing needs it. One name
    // field only: an entity owner's name goes in the same blank.
    public bool $showOwnerModal = false;

    /** When set, the owner modal edits this party instead of creating one. */
    public ?int $editingOwnerPartyId = null;

    public ?string $owner_name = null;

    public ?string $owner_address1 = null;

    public ?string $owner_address2 = null;

    public ?string $owner_city = null;

    public ?string $owner_state = null;

    public ?string $owner_county = null;

    public ?string $owner_zip = null;

    /**
     * The draft auto-saved when the user reached the review step; review
     * actions operate on it, and re-entering review updates it in place.
     */
    public ?int $savedWaiverId = null;

    /** Executed copy chosen on review — the paper/notary path. */
    public $signedFile = null;

    public bool $showUpsellModal = false;

    /**
     * Which gate opened the upsell modal; drives its heading. 'save' = the
     * monthly free allowance ran out; 'esign' = the action needs e-signature,
     * which is Pro.
     */
    public string $upsellContext = 'save';

    /** Free plan: shown once the unsigned PDF has been downloaded. */
    public bool $showSignPrompt = false;

    /**
     * Carried in from the marketing-page starter (WaiverIntent): the waiver
     * type is applied the moment a project fixes the state; the state seeds
     * the inline project form.
     */
    public string $intentKind = '';

    public string $intentState = '';

    // Step 2: inline project creation. A waiver-first signup arrives with no
    // project, so the jobsite is collected right here instead of sending them
    // through the three-screen project wizard.
    public bool $creatingProject = false;

    public string $project_name = '';

    public ?string $project_address1 = null;

    public ?string $project_address2 = null;

    public ?string $project_city = null;

    public string $project_state = '';

    public ?string $project_zip = null;

    public ?string $project_county = null;

    public string $project_property_class = '';

    public string $project_role = '';

    /**
     * Optional. The anchor most states use for the preliminary notice
     * deadline, so the project can show (and the follow-up email can name) a
     * real date instead of "add your dates".
     */
    public ?string $project_first_furnish_date = null;

    /**
     * One "your role on this job" question standing in for ProjectForm's two
     * role-capture facts. The facts are stored too, so the project edits and
     * derives its claimant type exactly like one built in ProjectForm.
     *
     * @var array<string, array{label: string, hint: string, facts: array{0: string, 1: string}}>
     */
    public const PROJECT_ROLES = [
        'gc' => [
            'label' => 'General / prime contractor',
            'hint' => 'Hired directly by the property owner.',
            'facts' => ['both', 'owner'],
        ],
        'subcontractor' => [
            'label' => 'Subcontractor',
            'hint' => 'Hired by the general contractor.',
            'facts' => ['both', 'direct_contractor'],
        ],
        'sub_subcontractor' => [
            'label' => 'Sub-subcontractor',
            'hint' => 'Hired by another subcontractor.',
            'facts' => ['both', 'subcontractor'],
        ],
        'supplier' => [
            'label' => 'Material supplier',
            'hint' => 'Supplied materials to a contractor on the job.',
            'facts' => ['materials_only', 'direct_contractor'],
        ],
    ];

    public function mount(): void
    {
        // Validate the ?project= deep link; silently drop anything that isn't
        // one of this business's completed projects.
        if ($this->projectId !== '' && $this->selectedProject() === null) {
            $this->projectId = '';
        }

        // A project handed in via ?project= (the project page's waiver actions)
        // is fixed for the whole flow, so the project step is skipped.
        $this->projectLocked = $this->projectId !== '';

        // ?kind= (the four type cards) additionally preselects the waiver type.
        // Only lock the type step when that kind actually resolved for this
        // project's state; selectKind() no-ops on invalid/unavailable kinds, so
        // a stale link just leaves the user on the guided selector.
        if ($this->presetKind !== '' && $this->projectLocked) {
            $this->selectKind($this->presetKind);
            $this->kindLocked = $this->kind !== '';
        }

        // The marketing-page starter's choices (state, send vs collect, waiver
        // type) rode along in the session through registration; consume them
        // so the wizard opens where the landing page left off. A ?project=
        // deep link is a different entry and ignores them.
        $intent = WaiverIntent::pull();

        if ($intent !== null && ! $this->projectLocked) {
            $this->intentKind = $intent['kind'] ?? '';
            $this->intentState = $intent['state'] ?? '';
            $this->project_state = $this->intentState;

            if ($intent['direction'] !== null) {
                $this->direction = $intent['direction'];
                $this->step = 2;
            }
        }

        // Nothing to pick from yet: open the inline project form straight
        // away rather than a dead-end "create a project first".
        $this->creatingProject = ! $this->projectLocked && ! $this->completedProjectsQuery()->exists();
    }

    // ------------------------------------------------------------------
    // Navigation
    // ------------------------------------------------------------------

    public function nextStep(): void
    {
        $this->validateStep();

        $next = $this->step;
        do {
            $next++;
        } while ($next < $this->totalSteps && $this->stepIsSkipped($next));

        $this->step = min($next, $this->totalSteps);

        // Arriving at the details step: seed the legal description from the
        // project so MO residential users start from what's already on file.
        if ($this->step === 4 && blank($this->legal_description)) {
            $this->legal_description = $this->selectedProject()?->legal_description;
        }

        // Arriving at review: the waiver saves itself (or updates the draft
        // from a previous visit). No separate save button.
        if ($this->step === 5) {
            $this->autoSave();
        }
    }

    public function previousStep(): void
    {
        $prev = $this->step;
        do {
            $prev--;
        } while ($prev > 1 && $this->stepIsSkipped($prev));

        $this->step = max($prev, 1);
    }

    public function goToStep(int $step): void
    {
        // Only allow jumping back to an already-completed, non-skipped step.
        if ($step < $this->step && $step >= 1 && ! $this->stepIsSkipped($step)) {
            $this->step = $step;
        }
    }

    /**
     * Steps the deep link already answered: project (2) when ?project= locked it
     * and waiver type (3) when ?kind= locked it. Skipped in navigation and shown
     * as complete in the progress rail.
     */
    public function stepIsSkipped(int $step): bool
    {
        return ($step === 2 && $this->projectLocked)
            || ($step === 3 && $this->kindLocked);
    }

    protected function validateStep(): void
    {
        $rules = $this->getStepRules($this->step);

        if ($rules !== []) {
            $this->validate($rules);
        }

        if ($this->step === 2 && $this->selectedProject() === null) {
            throw ValidationException::withMessages([
                'projectId' => 'Select one of your projects.',
            ]);
        }

        if ($this->step === 3 && ! ($this->availableKinds()[$this->kind]['enabled'] ?? false)) {
            throw ValidationException::withMessages([
                'kind' => 'That waiver type is not available in '.$this->stateName().'. Pick another type.',
            ]);
        }

        if ($this->step === 4) {
            $this->assertProjectHasOwner();
            $this->assertCollectContactSignable();
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function getStepRules(int $step): array
    {
        return match ($step) {
            1 => ['direction' => ['required', Rule::enum(WaiverDirection::class)]],
            2 => ['projectId' => ['required', 'string']],
            3 => ['kind' => ['required', Rule::enum(WaiverKind::class)]],
            4 => $this->getDetailsRules(),
            default => [],
        };
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function getDetailsRules(): array
    {
        // The amount is required: every waiver here is tied to a specific
        // payment (states like KY only honor partial waivers exchanged for
        // payments actually made). Dates and check details stay nullable —
        // several statutory forms are legitimately exchanged with those
        // blank. No separate signer fields: you sign your own provide
        // waivers, and on collect waivers the contact signs (see
        // assertCollectContactSignable for the email requirement). The
        // contact is required in both directions so the form's customer
        // blank (provide) or claimant identity (collect) is never empty.
        $rules = [
            // The input shows thousands separators, so validate the
            // de-formatted value instead of using the bare numeric rule.
            'amount' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                $raw = str_replace([',', '$', ' '], '', (string) $value);

                if (! is_numeric($raw)) {
                    $fail('Enter the payment amount as a number.');
                } elseif ((float) $raw < 0) {
                    $fail('The amount can\'t be negative.');
                } elseif ((float) $raw > 99999999) {
                    $fail('The amount is too large.');
                }
            }],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'exceptions' => ['nullable', 'string', 'max:2000'],
            'contactId' => ['required', 'string'],
            // Only MO's residential unconditional final form demands a formal
            // legal description — a street address doesn't track that form.
            'legal_description' => $this->resolvedForm()?->requiresLegalDescription
                ? ['required', 'string', 'max:2000']
                : ['nullable', 'string', 'max:2000'],
        ];

        if (! $this->isFinalKind()) {
            // Progress waivers are scoped by their through date; without one
            // the release has no boundary, so it's required.
            $rules['through_date'] = ['required', 'date'];
        }

        if ($this->isConditionalKind()) {
            $rules['check_maker'] = ['nullable', 'string', 'max:255'];
            $rules['check_number'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'contactId.required' => $this->direction === WaiverDirection::Collect->value
                ? 'Select or add the contact giving you this waiver.'
                : 'Select or add the contact who receives this waiver — their name prints in the form\'s customer blank.',
            'legal_description.required' => 'This form is only valid with the property\'s legal description — a street address alone doesn\'t satisfy it.',
        ];
    }

    /**
     * Every waiver form identifies the property owner, so a waiver can't be
     * generated until the project has an owner party.
     */
    private function assertProjectHasOwner(): void
    {
        if ($this->selectedProject()?->ownerParty() !== null) {
            return;
        }

        throw ValidationException::withMessages([
            'owner' => 'Add the property owner — the waiver form identifies who owns the property.',
        ]);
    }

    /**
     * Collect waivers are signed by the counterparty, so the signature
     * request needs somewhere to go: a selected contact with an email.
     */
    private function assertCollectContactSignable(): void
    {
        if ($this->direction !== WaiverDirection::Collect->value) {
            return;
        }

        $contact = $this->selectedContact();

        if ($contact === null || blank($contact->email)) {
            throw ValidationException::withMessages([
                'contactId' => 'Choose a contact with an email address — the signature request is sent there.',
            ]);
        }
    }

    private function validateAllSteps(): void
    {
        $rules = [];

        foreach (range(1, 4) as $step) {
            $rules = array_merge($rules, $this->getStepRules($step));
        }

        $this->validate($rules);

        if ($this->selectedProject() === null) {
            throw ValidationException::withMessages(['projectId' => 'Select one of your projects.']);
        }

        if (! ($this->availableKinds()[$this->kind]['enabled'] ?? false)) {
            throw ValidationException::withMessages([
                'kind' => 'That waiver type is not available in '.$this->stateName().'.',
            ]);
        }

        $this->assertProjectHasOwner();
        $this->assertCollectContactSignable();
    }

    // ------------------------------------------------------------------
    // Step 1: direction
    // ------------------------------------------------------------------

    public function selectDirection(string $direction): void
    {
        if (WaiverDirection::tryFrom($direction) === null) {
            return;
        }

        $this->direction = $direction;
    }

    // ------------------------------------------------------------------
    // Step 2: project
    // ------------------------------------------------------------------

    public function updatedProjectId(): void
    {
        // The state drives which forms exist, so a project switch resets the
        // type selection — and the legal description, which belongs to the
        // previous project's property (re-seeded when step 4 is reached).
        $this->kind = '';
        $this->paymentType = '';
        $this->paymentReceived = '';
        $this->redirectNotice = null;
        $this->legal_description = null;

        $this->applyIntentKind();
    }

    /** Projects the wizard can build a waiver on. */
    private function completedProjectsQuery(): Builder
    {
        return LienProject::query()
            ->whereNotNull('wizard_completed_at')
            ->whereNotNull('jobsite_state');
    }

    public function selectedProject(): ?LienProject
    {
        if ($this->projectId === '') {
            return null;
        }

        return $this->completedProjectsQuery()
            ->where('public_id', $this->projectId)
            ->first();
    }

    /**
     * The starter's waiver type is applied once, the first time a project
     * fixes the state; selectKind() no-ops when the state doesn't use it.
     */
    private function applyIntentKind(): void
    {
        if ($this->intentKind === '' || $this->state() === null) {
            return;
        }

        $this->selectKind($this->intentKind);
        $this->intentKind = '';
    }

    public function startNewProject(): void
    {
        $this->creatingProject = true;

        if ($this->project_state === '') {
            $this->project_state = $this->intentState;
        }
    }

    public function cancelNewProject(): void
    {
        $this->creatingProject = false;
        $this->resetValidation(array_keys($this->projectRules()));
    }

    /**
     * Google Places pick for the inline project form's street-address input
     * (see livewire.lien._places-autocomplete).
     *
     * @param  array<string, mixed>  $addressData
     */
    public function updateProjectAddressFromAutocomplete(array $addressData): void
    {
        $this->project_address1 = $addressData['line1'] ?? null;
        $this->project_city = $addressData['city'] ?? null;
        $this->project_state = strtoupper((string) ($addressData['state'] ?? '')) ?: $this->project_state;
        $this->project_zip = $addressData['zip'] ?? null;
        $this->project_county = $addressData['county'] ?? null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function projectRules(): array
    {
        return [
            'project_name' => ['nullable', 'string', 'max:255'],
            'project_address1' => ['required', 'string', 'max:255'],
            'project_address2' => ['nullable', 'string', 'max:255'],
            'project_city' => ['required', 'string', 'max:255'],
            'project_state' => ['required', 'string', Rule::in(array_keys(WaiverStateRegistry::STATE_NAMES))],
            'project_zip' => ['nullable', 'string', 'max:10'],
            'project_county' => ['nullable', 'string', 'max:255'],
            'project_property_class' => ['required', Rule::in(['residential', 'commercial', 'government'])],
            'project_role' => ['required', Rule::in(array_keys(self::PROJECT_ROLES))],
            // Same rule as ProjectForm.
            'project_first_furnish_date' => ['nullable', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Create the project from the inline form and move on: to the type step,
     * or straight to details when the starter already chose the type.
     */
    public function createProject(): void
    {
        $this->validate($this->projectRules(), [
            'project_address1.required' => 'Enter the jobsite street address.',
            'project_city.required' => 'Enter the jobsite city.',
            'project_state.required' => 'Pick the jobsite state.',
            'project_state.in' => 'Pick the jobsite state.',
            'project_property_class.required' => 'Pick the property type.',
            'project_role.required' => 'Pick your role on this job.',
            'project_first_furnish_date.before_or_equal' => 'Your first day on the job can\'t be in the future.',
        ]);

        [$providedType, $hiredBy] = self::PROJECT_ROLES[$this->project_role]['facts'];
        $state = strtoupper($this->project_state);
        $name = trim($this->project_name) !== ''
            ? trim($this->project_name)
            : trim($this->project_address1).', '.trim($this->project_city);

        // Same geocode fallback as ProjectForm (the delegated autocomplete
        // hands over the parsed address, not the place id).
        $geo = app(GooglePlacesService::class)->geocodeAddress(implode(', ', array_filter([
            $this->project_address1, $this->project_city, $state, $this->project_zip,
        ]))) ?? [];

        $project = LienProject::create([
            'business_id' => Auth::user()->currentBusiness()->id,
            'created_by_user_id' => Auth::id(),
            'name' => $name,
            'provided_type' => $providedType,
            'hired_by' => $hiredBy,
            'claimant_type' => ClaimantType::derive($providedType, $hiredBy)->value,
            'property_class' => $this->project_property_class,
            'property_context' => 'unknown',
            'jobsite_address1' => $this->project_address1,
            'jobsite_address2' => $this->project_address2 ?: null,
            'jobsite_city' => $this->project_city,
            'jobsite_state' => $state,
            'jobsite_zip' => $this->project_zip ?: null,
            'jobsite_county' => $this->project_county ?: ($geo['county'] ?? null),
            'jobsite_county_google' => $geo['county'] ?? ($this->project_county ?: null),
            'jobsite_place_id' => $geo['place_id'] ?? null,
            'jobsite_formatted_address' => $geo['formatted_address'] ?? null,
            'jobsite_lat' => $geo['lat'] ?? null,
            'jobsite_lng' => $geo['lng'] ?? null,
            'noc_status' => 'unknown',
            'first_furnish_date' => $this->project_first_furnish_date ?: null,
            'wizard_completed_at' => now(),
        ]);

        // As in ProjectForm: deadline rows exist from day one, so the project
        // page can show what's still missing to track lien deadlines.
        app(DeadlineCalculator::class)->calculateForProject($project->fresh());

        $this->creatingProject = false;
        $this->projectId = $project->public_id;
        $this->updatedProjectId();

        // Advance exactly like a manual pick, skipping the type step when the
        // starter already answered it.
        $this->nextStep();

        if ($this->kind !== '') {
            $this->nextStep();
        }
    }

    public function state(): ?string
    {
        return $this->selectedProject()?->jobsite_state;
    }

    private function stateName(): string
    {
        $state = $this->state();

        return $state !== null
            ? (WaiverStateRegistry::STATE_NAMES[strtoupper($state)] ?? $state)
            : 'this state';
    }

    // ------------------------------------------------------------------
    // Step 3: waiver type
    // ------------------------------------------------------------------

    public function updatedPaymentType(): void
    {
        $this->applyGuidedSelection();
    }

    public function updatedPaymentReceived(): void
    {
        $this->applyGuidedSelection();
    }

    /**
     * Map the two plain-English answers to a canonical kind, steering to the
     * state's redirect_kind (with an explanation) when the guided answer
     * lands on a form the state doesn't use.
     */
    private function applyGuidedSelection(): void
    {
        $this->redirectNotice = null;

        if ($this->paymentType === '' || $this->paymentReceived === '' || $this->state() === null) {
            return;
        }

        // Conditional = the payment hasn't actually cleared yet.
        $guided = match (true) {
            $this->paymentType === 'progress' && $this->paymentReceived === 'no' => WaiverKind::ConditionalProgress,
            $this->paymentType === 'progress' => WaiverKind::UnconditionalProgress,
            $this->paymentReceived === 'no' => WaiverKind::ConditionalFinal,
            default => WaiverKind::UnconditionalFinal,
        };

        $kinds = $this->availableKinds();
        $entry = $kinds[$guided->value];

        if ($entry['enabled']) {
            $this->kind = $guided->value;

            return;
        }

        $redirect = $entry['redirect_kind'];

        if ($redirect !== null && ($kinds[$redirect]['enabled'] ?? false)) {
            $this->kind = $redirect;
            $this->redirectNotice = trim(($entry['disabled_reason'] ?? $guided->label().' is not used in '.$this->stateName().'.')
                .' We selected the state\'s equivalent: '.$kinds[$redirect]['title'].'.');

            return;
        }

        $this->kind = '';
        $this->redirectNotice = $entry['disabled_reason']
            ?? $guided->label().' is not available in '.$this->stateName().'.';
    }

    /** Power-user grid selection; disabled entries are unclickable in the blade. */
    public function selectKind(string $kind): void
    {
        $case = WaiverKind::tryFrom($kind);

        if ($case === null || ! ($this->availableKinds()[$kind]['enabled'] ?? false)) {
            return;
        }

        $this->kind = $kind;
        $this->redirectNotice = null;

        // Keep the guided answers in sync so flipping back is not confusing.
        $this->paymentType = $case->isFinal() ? 'final' : 'progress';
        $this->paymentReceived = $case->isConditional() ? 'no' : 'yes';
    }

    /**
     * @return array<string, array{kind: WaiverKind, enabled: bool, title: string, disabled_reason: ?string, redirect_kind: ?string}>
     */
    public function availableKinds(): array
    {
        $state = $this->state();

        if ($state === null) {
            return [];
        }

        return app(WaiverFormResolver::class)->availableKinds($state);
    }

    /**
     * @return array<string, mixed>
     */
    public function stateRules(): array
    {
        $state = $this->state();

        return $state !== null ? WaiverStateRegistry::for($state) : [];
    }

    public function resolvedForm(): ?ResolvedWaiverForm
    {
        $state = $this->state();
        $kindCase = WaiverKind::tryFrom($this->kind);

        if ($state === null || $kindCase === null) {
            return null;
        }

        try {
            return app(WaiverFormResolver::class)->resolve($state, $kindCase, $this->selectedProject()?->property_class);
        } catch (WaiverFormUnavailable) {
            return null;
        }
    }

    /** Reformat the amount with thousands separators as soon as it's entered. */
    public function updatedAmount(): void
    {
        $raw = str_replace([',', '$', ' '], '', (string) $this->amount);

        if ($raw !== '' && is_numeric($raw)) {
            $this->amount = number_format((float) $raw, 2);
        }
    }

    /** The amount as a float, tolerant of the display formatting; null when blank/invalid. */
    public function amountFloat(): ?float
    {
        $raw = str_replace([',', '$', ' '], '', (string) $this->amount);

        return $raw !== '' && is_numeric($raw) ? (float) $raw : null;
    }

    public function isFinalKind(): bool
    {
        return WaiverKind::tryFrom($this->kind)?->isFinal() ?? false;
    }

    public function isConditionalKind(): bool
    {
        return WaiverKind::tryFrom($this->kind)?->isConditional() ?? false;
    }

    // ------------------------------------------------------------------
    // Step 4: counterparty contact
    // ------------------------------------------------------------------

    public function selectedContact(): ?LienContact
    {
        if ($this->contactId === '') {
            return null;
        }

        return LienContact::query()->find($this->contactId);
    }

    public function openContactModal(): void
    {
        $this->resetContactForm();
        $this->showContactModal = true;
    }

    /**
     * Edit the currently selected contact in the modal — mainly so a missing
     * email (which blocks collect waivers) can be fixed without leaving the
     * wizard and losing its state.
     */
    public function editSelectedContact(): void
    {
        $contact = $this->selectedContact();

        if ($contact === null) {
            return;
        }

        $this->resetContactForm();
        $this->editingContactId = $contact->id;
        $this->contact_company = $contact->company_name;
        $this->contact_first_name = $contact->first_name;
        $this->contact_last_name = $contact->last_name;
        $this->contact_email = $contact->email;
        $this->contact_phone = $contact->phone;
        $this->contact_address1 = $contact->address_line1;
        $this->contact_address2 = $contact->address_line2;
        $this->contact_city = $contact->city;
        $this->contact_state = $contact->state;
        $this->contact_county = $contact->county;
        $this->contact_zip = $contact->postal_code;
        $this->showContactModal = true;
    }

    public function closeContactModal(): void
    {
        $this->showContactModal = false;
        $this->resetContactForm();
    }

    public function saveContact(): void
    {
        // A contact needs a company OR a person's name — not both. No field is
        // individually required; the error surfaces on the company field.
        $this->validate([
            'contact_company' => ['nullable', 'required_without_all:contact_first_name,contact_last_name', 'string', 'max:255'],
            'contact_first_name' => ['nullable', 'string', 'max:255'],
            'contact_last_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address1' => ['nullable', 'string', 'max:255'],
            'contact_address2' => ['nullable', 'string', 'max:255'],
            'contact_city' => ['nullable', 'string', 'max:255'],
            'contact_state' => ['nullable', 'string', 'max:2'],
            'contact_county' => ['nullable', 'string', 'max:255'],
            'contact_zip' => ['nullable', 'string', 'max:10'],
        ], [
            'contact_company.required_without_all' => 'Enter a company name or a first/last name.',
        ]);

        // Blank inputs become real nulls so company-less/name-less contacts
        // read cleanly.
        $attributes = array_map(fn ($value) => $value === '' ? null : $value, [
            'company_name' => $this->contact_company,
            'first_name' => $this->contact_first_name,
            'last_name' => $this->contact_last_name,
            'email' => $this->contact_email,
            'phone' => $this->contact_phone,
            'address_line1' => $this->contact_address1,
            'address_line2' => $this->contact_address2,
            'city' => $this->contact_city,
            'state' => $this->contact_state ? strtoupper($this->contact_state) : null,
            'county' => $this->contact_county,
            'postal_code' => $this->contact_zip,
        ]);

        if ($this->editingContactId !== null) {
            // The global business scope keeps this tenant-safe.
            $contact = LienContact::query()->findOrFail($this->editingContactId);
            $contact->update($attributes);

            Flux::toast(text: 'Contact updated.', variant: 'success');
        } else {
            // business_id auto-fills from the BelongsToBusiness creating hook.
            $contact = LienContact::create([
                ...$attributes,
                'created_by_user_id' => Auth::id(),
            ]);

            Flux::toast(text: 'Contact added.', variant: 'success');
        }

        $this->contactId = (string) $contact->id;
        $this->closeContactModal();
    }

    private function resetContactForm(): void
    {
        $this->editingContactId = null;
        $this->contact_company = null;
        $this->contact_first_name = null;
        $this->contact_last_name = null;
        $this->contact_email = null;
        $this->contact_phone = null;
        $this->contact_address1 = null;
        $this->contact_address2 = null;
        $this->contact_city = null;
        $this->contact_state = null;
        $this->contact_county = null;
        $this->contact_zip = null;
        $this->resetValidation([
            'contact_company', 'contact_first_name', 'contact_last_name', 'contact_email', 'contact_phone',
            'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_county', 'contact_zip',
        ]);
    }

    /**
     * Google Places pick for the contact modal's street-address input
     * (see livewire.lien._places-autocomplete).
     *
     * @param  array<string, mixed>  $addressData
     */
    public function updateContactAddressFromAutocomplete(array $addressData): void
    {
        $this->contact_address1 = $addressData['line1'] ?? null;
        $this->contact_city = $addressData['city'] ?? null;
        $this->contact_state = $addressData['state'] ?? null;
        $this->contact_county = $addressData['county'] ?? null;
        $this->contact_zip = $addressData['zip'] ?? null;
    }

    // ------------------------------------------------------------------
    // Step 4: property owner
    // ------------------------------------------------------------------

    public function openOwnerModal(): void
    {
        $this->resetOwnerForm();
        $this->showOwnerModal = true;
    }

    /** Edit the project's existing owner party in place, prefilled. */
    public function editOwner(): void
    {
        $owner = $this->selectedProject()?->ownerParty();

        if ($owner === null) {
            return;
        }

        $this->resetOwnerForm();
        $this->editingOwnerPartyId = $owner->id;
        $this->owner_name = $owner->company_name ?: $owner->name;
        $this->owner_address1 = $owner->address1;
        $this->owner_address2 = $owner->address2;
        $this->owner_city = $owner->city;
        $this->owner_state = $owner->state;
        $this->owner_county = $owner->county;
        $this->owner_zip = $owner->zip;
        $this->showOwnerModal = true;
    }

    public function closeOwnerModal(): void
    {
        $this->showOwnerModal = false;
        $this->resetOwnerForm();
    }

    public function saveOwner(): void
    {
        $project = $this->selectedProject();

        if ($project === null) {
            return;
        }

        $this->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_address1' => ['nullable', 'string', 'max:255'],
            'owner_address2' => ['nullable', 'string', 'max:255'],
            'owner_city' => ['nullable', 'string', 'max:255'],
            'owner_state' => ['nullable', 'string', 'max:2'],
            'owner_county' => ['nullable', 'string', 'max:255'],
            'owner_zip' => ['nullable', 'string', 'max:10'],
        ], attributes: [
            'owner_name' => 'owner name',
        ]);

        $attributes = array_map(fn ($value) => $value === '' ? null : $value, [
            'name' => $this->owner_name,
            'address1' => $this->owner_address1,
            'address2' => $this->owner_address2,
            'city' => $this->owner_city,
            'state' => $this->owner_state ? strtoupper($this->owner_state) : null,
            'county' => $this->owner_county,
            'zip' => $this->owner_zip,
        ]);

        if ($this->editingOwnerPartyId !== null) {
            $owner = $project->parties()->findOrFail($this->editingOwnerPartyId);
            // The single name field replaces whichever field was displayed;
            // clear company_name so the edited name is what prints.
            $owner->update([...$attributes, 'company_name' => null]);

            Flux::toast(text: 'Property owner updated.', variant: 'success');
        } else {
            LienParty::create([
                ...$attributes,
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'role' => 'owner',
            ]);

            Flux::toast(text: 'Property owner added to the project.', variant: 'success');
        }

        $this->resetValidation(['owner']);
        $this->closeOwnerModal();
    }

    private function resetOwnerForm(): void
    {
        $this->editingOwnerPartyId = null;
        $this->owner_name = null;
        $this->owner_address1 = null;
        $this->owner_address2 = null;
        $this->owner_city = null;
        $this->owner_state = null;
        $this->owner_county = null;
        $this->owner_zip = null;
        $this->resetValidation([
            'owner_name', 'owner_address1', 'owner_address2',
            'owner_city', 'owner_state', 'owner_county', 'owner_zip',
        ]);
    }

    /**
     * Google Places pick for the owner modal's street-address input.
     *
     * @param  array<string, mixed>  $addressData
     */
    public function updateOwnerAddressFromAutocomplete(array $addressData): void
    {
        $this->owner_address1 = $addressData['line1'] ?? null;
        $this->owner_city = $addressData['city'] ?? null;
        $this->owner_state = $addressData['state'] ?? null;
        $this->owner_county = $addressData['county'] ?? null;
        $this->owner_zip = $addressData['zip'] ?? null;
    }

    // ------------------------------------------------------------------
    // Step 5: auto-save + actions
    // ------------------------------------------------------------------

    /**
     * The draft persisted when review was reached; null when the free
     * allowance was exhausted (or the row has since moved past Draft).
     */
    public function savedWaiver(): ?LienWaiver
    {
        if ($this->savedWaiverId === null) {
            return null;
        }

        return LienWaiver::query()->find($this->savedWaiverId);
    }

    /**
     * Reaching review saves the waiver as a draft and generates its PDF.
     * Re-entering review after edits updates the same draft (one meter slot
     * per wizard run, not per visit). Over the free limit nothing persists —
     * review still renders, and the actions pitch the upgrade instead.
     */
    private function autoSave(): void
    {
        $business = Auth::user()->currentBusiness();

        $existing = $this->savedWaiver();

        // Auto-save leaves the row Generated (PDF built); anything past that
        // (sent, signed, voided) is no longer this wizard run's to edit.
        if ($existing !== null && in_array($existing->status, [WaiverStatus::Draft, WaiverStatus::Generated], true)) {
            $existing->update($this->waiverAttributes());
            $this->syncProjectLegalDescription();
            app(GenerateWaiver::class)->execute($existing);

            return;
        }

        $this->savedWaiverId = null;

        // The free-tier cap is a check-then-create, so serialize it per
        // business: two tabs must not both slip past the monthly limit.
        $lock = Cache::lock("waiver-save-{$business->id}", 10);

        try {
            $lock->block(5);

            if (! WaiverEntitlements::canSaveWaiver($business, Auth::user())) {
                return;
            }

            $waiver = LienWaiver::create($this->waiverAttributes());
        } finally {
            optional($lock)->release();
        }

        $this->savedWaiverId = $waiver->id;
        $this->syncProjectLegalDescription();
        app(GenerateWaiver::class)->execute($waiver);

        // Free plan: follow up by email on getting it signed (no-op for Pro).
        // Rescued: email bookkeeping must never cost someone their waiver.
        rescue(fn () => WaiverNurture::onWaiverSaved($waiver, Auth::user(), $business));

        // GA4 funnel: the activation event (a finished, downloadable waiver).
        $this->js(Gtag::eventJs('waiver_generated', [
            'state' => $waiver->state,
            'direction' => $waiver->direction->value,
            'waiver_kind' => $waiver->kind->value,
        ]));
    }

    /** Stream the saved draft's PDF; over the free limit, pitch the upgrade. */
    public function downloadPdf(WaiverGenerator $generator): ?StreamedResponse
    {
        $this->validateAllSteps();

        $waiver = $this->savedWaiver();

        if ($waiver === null) {
            $this->upsellContext = 'save';
            $this->showUpsellModal = true;

            return null;
        }

        try {
            $bytes = $generator->render($waiver)->generatePdfContent();
        } catch (WaiverFormUnavailable $e) {
            $this->addError('kind', $e->getMessage());

            return null;
        }

        $filename = $generator->filename($waiver);

        // The unsigned PDF is theirs to keep; this is the moment to offer the
        // step that finishes the job.
        $this->showSignPrompt = ! WaiverEntitlements::canUseEsign(Auth::user()->currentBusiness(), Auth::user());

        return response()->streamDownload(function () use ($bytes): void {
            echo $bytes;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * E-sign the saved draft. E-signature is Pro, so without a seat this
     * pitches the upgrade. With one, a provide waiver (the user signs their
     * own) goes straight into the signing ceremony, and a collect waiver
     * emails the signer. When the send fails (esign policy, missing signer
     * email, ...) the waiver is already saved; surface the message on the
     * show page instead of losing work.
     */
    public function saveAndSend(SendWaiverForSignature $send): void
    {
        $this->validateAllSteps();

        $form = $this->resolvedForm();

        if ($form === null || ! $form->esignAllowed) {
            $this->addError('kind', $form?->esignDisabledReason
                ?? 'This state requires in-person execution, so e-signing is unavailable. Download the waiver, sign on paper, then upload the signed copy.');

            return;
        }

        $waiver = $this->savedWaiver();

        if ($waiver === null) {
            $this->upsellContext = 'save';
            $this->showUpsellModal = true;

            return;
        }

        if (! WaiverEntitlements::canUseEsign(Auth::user()->currentBusiness(), Auth::user())) {
            $this->upsellContext = 'esign';
            $this->showUpsellModal = true;

            // GA4 funnel: a free user asked to sign or send. The strongest
            // sign of intent to pay that we see before checkout.
            $this->js(Gtag::eventJs('esign_upsell_shown', [
                'source' => 'wizard',
                'direction' => $this->direction,
                'state' => $waiver->state,
            ]));

            return;
        }

        try {
            $request = $send->execute($waiver, Auth::user());
        } catch (EsignException $e) {
            session()->flash('esign_error', $e->getMessage());
            $this->redirect(route('lien.waivers.show', $waiver), navigate: true);

            return;
        }

        // Your own waiver: sign it now rather than waiting for the invitation
        // email, which is still sent as a way back in.
        if ($request->signer_user_id === Auth::id()) {
            $this->redirect(SigningLink::for($request));

            return;
        }

        Flux::toast(text: 'Waiver sent for signature.', variant: 'success');

        $this->redirect(route('lien.waivers.show', $waiver), navigate: true);
    }

    /**
     * The paper path from review: upload the executed copy — required in
     * notary/witness states where e-signing is unavailable, and available
     * anywhere the counterparty signed a printout.
     */
    public function uploadSigned(StoreSignedCopy $store): void
    {
        $waiver = $this->savedWaiver();

        if ($waiver === null) {
            $this->upsellContext = 'save';
            $this->showUpsellModal = true;

            return;
        }

        $this->validate([
            'signedFile' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $store->execute($waiver, $this->signedFile);

        Flux::toast(text: 'Signed copy uploaded.', variant: 'success');

        $this->redirect(route('lien.waivers.show', $waiver), navigate: true);
    }

    /**
     * Attribute payload for the auto-saved draft (create and update alike).
     * State is snapshotted from the project.
     *
     * @return array<string, mixed>
     */
    private function waiverAttributes(): array
    {
        $project = $this->selectedProject();
        $contact = $this->selectedContact();
        $user = Auth::user();
        $provide = $this->direction === WaiverDirection::Provide->value;

        return [
            'business_id' => $user->currentBusiness()->id,
            'project_id' => $project->id,
            'created_by_user_id' => $user->id,
            'direction' => $this->direction,
            'kind' => $this->kind,
            'status' => WaiverStatus::Draft,
            'state' => strtoupper($project->jobsite_state),
            'amount_cents' => $this->amountFloat() !== null
                ? (int) round($this->amountFloat() * 100)
                : null,
            'through_date' => $this->isFinalKind() ? null : ($this->through_date ?: null),
            'invoice_number' => $this->invoice_number ?: null,
            'check_maker' => $this->isConditionalKind() ? ($this->check_maker ?: null) : null,
            'check_number' => $this->isConditionalKind() ? ($this->check_number ?: null) : null,
            'exceptions' => $this->exceptions ?: null,
            'legal_description' => $this->legal_description ?: null,
            'lien_contact_id' => $contact?->id,
            'counterparty_company' => $contact?->company_name,
            'counterparty_name' => ($contact && $contact->personName() !== '') ? $contact->personName() : null,
            'counterparty_email' => $contact?->email,
            'counterparty_phone' => $contact?->phone,
            // provide: the current user signs their own waiver. collect: the
            // contact signs (assertCollectContactSignable guarantees an email).
            'signer_name' => $provide ? $user->name : (($contact && $contact->personName() !== '') ? $contact->personName() : $contact?->company_name),
            'signer_email' => $provide ? $user->email : $contact?->email,
            'signer_title' => null,
        ];
    }

    /**
     * A legal description typed in the wizard is worth keeping on the project
     * too — the next waiver (and the lien filing flow) prefills from it.
     * Never overwrites a value the project already has.
     */
    private function syncProjectLegalDescription(): void
    {
        $project = $this->selectedProject();

        if ($project !== null && filled($this->legal_description) && blank($project->legal_description)) {
            $project->update(['legal_description' => $this->legal_description]);
        }
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------

    public function render(): View
    {
        $business = Auth::user()->currentBusiness();
        $project = $this->selectedProject();

        $projects = $this->completedProjectsQuery()
            ->orderBy('name')
            ->get();

        $contacts = LienContact::query()
            ->orderBy('company_name')
            ->get();

        return view('livewire.lien.waivers.waiver-wizard', [
            'directions' => WaiverDirection::cases(),
            'projects' => $projects,
            'projectRoles' => self::PROJECT_ROLES,
            'contacts' => $contacts,
            'project' => $project,
            'stateRules' => $this->stateRules(),
            'kinds' => $this->availableKinds(),
            'form' => $this->resolvedForm(),
            'canSave' => WaiverEntitlements::canSaveWaiver($business, Auth::user()),
            'canEsign' => WaiverEntitlements::canUseEsign($business, Auth::user()),
            'proMonthly' => '$'.number_format(config('lien_waivers.prices.monthly.amount_cents') / 100),
            // Rides along to checkout so paying brings them back to this waiver.
            'savedWaiverPublicId' => $this->savedWaiver()?->public_id,
            'hasPaidAccess' => WaiverEntitlements::hasPaidAccess($business, Auth::user()),
            'remainingFreeSaves' => WaiverEntitlements::remainingFreeSaves($business),
            'freeSavesLimit' => WaiverEntitlements::freeSavesLimit(),
            // Seat-aware upsell: a seatless member of a subscribed business
            // needs a seat, not a second subscription.
            'businessSubscribed' => WaiverEntitlements::isSubscribed($business),
            'canManageSeats' => WaiverEntitlements::canManageSeats($business, Auth::user()),
            'stepTitles' => [
                1 => 'Direction',
                2 => 'Project',
                3 => 'Waiver Type',
                4 => 'Details',
                5 => 'Review',
            ],
        ])->layout('components.layouts.portal', ['title' => 'New Lien Waiver']);
    }
}

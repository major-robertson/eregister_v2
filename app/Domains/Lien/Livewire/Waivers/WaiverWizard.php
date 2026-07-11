<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\Actions\SendWaiverForSignature;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\Actions\GenerateWaiver;
use App\Domains\Lien\Waivers\ResolvedWaiverForm;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverFormUnavailable;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Five-step waiver builder: direction fork, project pick, guided type
 * selector (with a power-user grid of the state's statutory forms), details,
 * then review with three exits: free download (never persisted), save to
 * project (metered on the free tier), or save + send for e-signature (paid).
 *
 * The waiver's state is frozen from the project's jobsite_state at save so a
 * later project edit can't silently change which state's form it claims to be.
 */
class WaiverWizard extends Component
{
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

    // Counterparty
    public string $contactId = '';

    public bool $showContactModal = false;

    public ?string $contact_company = null;

    public ?string $contact_name = null;

    public ?string $contact_email = null;

    public ?string $contact_phone = null;

    public ?string $contact_address1 = null;

    public ?string $contact_address2 = null;

    public ?string $contact_city = null;

    public ?string $contact_state = null;

    public ?string $contact_zip = null;

    public bool $showUpsellModal = false;

    /** Which gate opened the upsell modal; drives its heading. */
    public string $upsellContext = 'save';

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

        if ($this->step === 4) {
            $this->seedCheckMaker();
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
        // assertCollectContactSignable for the email requirement).
        $rules = [
            'amount' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'exceptions' => ['nullable', 'string', 'max:2000'],
            'contactId' => ['nullable', 'string'],
        ];

        if (! $this->isFinalKind()) {
            $rules['through_date'] = ['nullable', 'date'];
        }

        if ($this->isConditionalKind()) {
            $rules['check_maker'] = ['nullable', 'string', 'max:255'];
            $rules['check_number'] = ['nullable', 'string', 'max:100'];
        }

        return $rules;
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
        // type selection.
        $this->kind = '';
        $this->paymentType = '';
        $this->paymentReceived = '';
        $this->redirectNotice = null;
    }

    public function selectedProject(): ?LienProject
    {
        if ($this->projectId === '') {
            return null;
        }

        return LienProject::query()
            ->where('public_id', $this->projectId)
            ->whereNotNull('wizard_completed_at')
            ->whereNotNull('jobsite_state')
            ->first();
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

    /**
     * On provide conditional waivers the counterparty is the payer, so
     * selecting a contact seeds the expected-check maker.
     */
    public function updatedContactId(): void
    {
        if ($this->selectedContact() !== null) {
            $this->seedCheckMaker();
        }
    }

    /**
     * Conditional waivers can identify the expected check. Seed the maker with
     * the payer when it's known: on collect waivers you are the payer; on
     * provide waivers the counterparty pays, so it fills from the contact.
     * Never overwrites something the user already typed.
     */
    private function seedCheckMaker(): void
    {
        if (! $this->isConditionalKind() || filled($this->check_maker)) {
            return;
        }

        if ($this->direction === WaiverDirection::Collect->value) {
            $this->check_maker = Auth::user()->currentBusiness()->name;
        } elseif ($contact = $this->selectedContact()) {
            $this->check_maker = $contact->company_name;
        }
    }

    public function openContactModal(): void
    {
        $this->resetContactForm();
        $this->showContactModal = true;
    }

    public function closeContactModal(): void
    {
        $this->showContactModal = false;
        $this->resetContactForm();
    }

    /**
     * Quick-fill the new-contact modal from the project's customer party:
     * on provide-direction waivers the counterparty is almost always the
     * customer who hired you.
     */
    public function useProjectCustomer(): void
    {
        $customer = $this->selectedProject()?->customerParty();

        if ($customer === null) {
            Flux::toast(text: 'This project has no customer party yet. Add one on the project page first.', variant: 'warning');

            return;
        }

        $this->resetContactForm();
        $this->contact_company = $customer->company_name ?: $customer->name;
        $this->contact_name = $customer->name;
        $this->contact_email = $customer->email;
        $this->contact_phone = $customer->phone;
        $this->contact_address1 = $customer->address1;
        $this->contact_address2 = $customer->address2;
        $this->contact_city = $customer->city;
        $this->contact_state = $customer->state;
        $this->contact_zip = $customer->zip;
        $this->showContactModal = true;
    }

    public function saveContact(): void
    {
        $this->validate([
            'contact_company' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_address1' => ['nullable', 'string', 'max:255'],
            'contact_address2' => ['nullable', 'string', 'max:255'],
            'contact_city' => ['nullable', 'string', 'max:255'],
            'contact_state' => ['nullable', 'string', 'max:2'],
            'contact_zip' => ['nullable', 'string', 'max:10'],
        ]);

        // business_id auto-fills from the BelongsToBusiness creating hook.
        $contact = LienContact::create([
            'created_by_user_id' => Auth::id(),
            'company_name' => $this->contact_company,
            'contact_name' => $this->contact_name,
            'email' => $this->contact_email,
            'phone' => $this->contact_phone,
            'address_line1' => $this->contact_address1,
            'address_line2' => $this->contact_address2,
            'city' => $this->contact_city,
            'state' => $this->contact_state ? strtoupper($this->contact_state) : null,
            'postal_code' => $this->contact_zip,
        ]);

        $this->contactId = (string) $contact->id;
        // Direct property writes don't fire Livewire's updated hook; run the
        // contact prefill (check maker) explicitly.
        $this->updatedContactId();
        $this->closeContactModal();

        Flux::toast(text: 'Contact added.', variant: 'success');
    }

    private function resetContactForm(): void
    {
        $this->contact_company = null;
        $this->contact_name = null;
        $this->contact_email = null;
        $this->contact_phone = null;
        $this->contact_address1 = null;
        $this->contact_address2 = null;
        $this->contact_city = null;
        $this->contact_state = null;
        $this->contact_zip = null;
        $this->resetValidation([
            'contact_company', 'contact_name', 'contact_email', 'contact_phone',
            'contact_address1', 'contact_address2', 'contact_city', 'contact_state', 'contact_zip',
        ]);
    }

    // ------------------------------------------------------------------
    // Step 5: actions
    // ------------------------------------------------------------------

    /**
     * Free path: render straight from wizard state and stream; the waiver is
     * never persisted, so downloads don't touch the free-save meter.
     */
    public function downloadPdf(WaiverGenerator $generator): ?StreamedResponse
    {
        $this->validateAllSteps();

        $waiver = $this->buildUnsavedWaiver();

        try {
            $bytes = $generator->render($waiver)->generatePdfContent();
        } catch (WaiverFormUnavailable $e) {
            $this->addError('kind', $e->getMessage());

            return null;
        }

        $filename = $generator->filename($waiver);

        return response()->streamDownload(function () use ($bytes): void {
            echo $bytes;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Metered path: persist the waiver + generate its PDF. Free tier gets
     * four saves per calendar month; over the limit we pitch the upgrade
     * instead of failing.
     */
    public function save(GenerateWaiver $generate): void
    {
        $this->validateAllSteps();

        $business = Auth::user()->currentBusiness();

        // The free-tier cap is a check-then-create, so serialize it per business:
        // two tabs (or a double-submit) must not both slip past the 4/month
        // limit. The lock is short; block briefly rather than racing.
        $lock = Cache::lock("waiver-save-{$business->id}", 10);

        try {
            $lock->block(5);

            if (! WaiverEntitlements::canSaveWaiver($business)) {
                $this->upsellContext = 'save';
                $this->showUpsellModal = true;

                return;
            }

            $waiver = LienWaiver::create($this->waiverAttributes());
        } finally {
            optional($lock)->release();
        }

        $waiver = $generate->execute($waiver);

        Flux::toast(text: 'Waiver saved to project.', variant: 'success');

        $this->redirect(route('lien.waivers.show', $waiver), navigate: true);
    }

    /**
     * Paid path: persist, generate, then send for e-signature. When the send
     * fails (esign policy, missing signer email, ...) the waiver is already
     * saved; surface the message on the show page instead of losing work.
     */
    public function saveAndSend(GenerateWaiver $generate, SendWaiverForSignature $send): void
    {
        $business = Auth::user()->currentBusiness();

        if (! WaiverEntitlements::canUseEsign($business)) {
            $this->upsellContext = 'esign';
            $this->showUpsellModal = true;

            return;
        }

        $this->validateAllSteps();

        $form = $this->resolvedForm();

        if ($form === null || ! $form->esignAllowed) {
            $this->addError('kind', $form?->esignDisabledReason
                ?? 'This state requires in-person execution, so e-signing is unavailable. Download the waiver, sign on paper, then upload the signed copy.');

            return;
        }

        $waiver = LienWaiver::create($this->waiverAttributes());
        $waiver = $generate->execute($waiver);

        try {
            $send->execute($waiver, Auth::user());
            Flux::toast(text: 'Waiver sent for signature.', variant: 'success');
        } catch (EsignException $e) {
            session()->flash('esign_error', $e->getMessage());
        }

        $this->redirect(route('lien.waivers.show', $waiver), navigate: true);
    }

    /**
     * Shared attribute payload for both the persisted row and the free
     * download's throwaway instance. State is snapshotted from the project.
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
            'amount_cents' => $this->amount !== null && $this->amount !== ''
                ? (int) round(((float) $this->amount) * 100)
                : null,
            'through_date' => $this->isFinalKind() ? null : ($this->through_date ?: null),
            'invoice_number' => $this->invoice_number ?: null,
            'check_maker' => $this->isConditionalKind() ? ($this->check_maker ?: null) : null,
            'check_number' => $this->isConditionalKind() ? ($this->check_number ?: null) : null,
            'exceptions' => $this->exceptions ?: null,
            'lien_contact_id' => $contact?->id,
            'counterparty_company' => $contact?->company_name,
            'counterparty_name' => $contact?->contact_name,
            'counterparty_email' => $contact?->email,
            'counterparty_phone' => $contact?->phone,
            // provide: the current user signs their own waiver. collect: the
            // contact signs (assertCollectContactSignable guarantees an email).
            'signer_name' => $provide ? $user->name : ($contact?->contact_name ?: $contact?->company_name),
            'signer_email' => $provide ? $user->email : $contact?->email,
            'signer_title' => null,
        ];
    }

    private function buildUnsavedWaiver(): LienWaiver
    {
        $waiver = new LienWaiver($this->waiverAttributes());
        $waiver->setRelation('project', $this->selectedProject());
        $waiver->setRelation('contact', $this->selectedContact());

        return $waiver;
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------

    public function render(): View
    {
        $business = Auth::user()->currentBusiness();
        $project = $this->selectedProject();

        $projects = LienProject::query()
            ->whereNotNull('wizard_completed_at')
            ->whereNotNull('jobsite_state')
            ->orderBy('name')
            ->get();

        $contacts = LienContact::query()
            ->orderBy('company_name')
            ->get();

        return view('livewire.lien.waivers.waiver-wizard', [
            'directions' => WaiverDirection::cases(),
            'projects' => $projects,
            'contacts' => $contacts,
            'project' => $project,
            'stateRules' => $this->stateRules(),
            'kinds' => $this->availableKinds(),
            'form' => $this->resolvedForm(),
            'canSave' => WaiverEntitlements::canSaveWaiver($business),
            'canEsign' => WaiverEntitlements::canUseEsign($business),
            'hasPaidAccess' => WaiverEntitlements::hasPaidAccess($business),
            'remainingFreeSaves' => WaiverEntitlements::remainingFreeSaves($business),
            'freeSavesLimit' => WaiverEntitlements::freeSavesLimit(),
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

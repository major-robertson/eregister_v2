<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\BusinessDays;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\RuleDateMath;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Domains\Lien\Services\RushTier;
use App\Domains\Lien\Support\PartyAddress;
use App\Domains\Lien\Support\PropertyLookup;
use App\Domains\Lien\Support\ZipStateRanges;
use App\Mail\AttorneyReferral;
use App\Models\EmailSequence;
use App\Models\Price;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingWizard extends Component
{
    use NormalizesLienInput;
    use WithFileUploads;

    /** Documents with a deadline panel, and a block once the deadline has passed. */
    private const DEADLINE_DOCUMENTS = ['mechanics_lien', 'prelim_notice', 'noi'];

    /** Notices are served; liens are recorded. */
    private const NOTICES = ['prelim_notice', 'noi'];

    /** The work dates, in the order the Work step asks for them. */
    private const WORK_DATE_LABELS = [
        'first_furnish_date' => 'First Furnish Date',
        'last_furnish_date' => 'Last Furnish Date',
        'completion_date' => 'Completion Date',
    ];

    /** Their noi rule rows count back from filing where the statute counts forward from last furnishing (EREG-91). */
    private const NOI_LEAD_TIME_UNRELIABLE_STATES = ['KY', 'MD'];

    /** Who a notice of intent was served on. */
    private const NOI_SERVED_TO = [
        'owner' => 'The property owner',
        'gc' => 'The general contractor',
        'both' => 'Both',
    ];

    /** The amount breakdown fields and their project columns, in the order the Work step asks for them. */
    private const BREAKDOWN_FIELDS = [
        'base_contract_amount' => 'base_contract_amount_cents',
        'change_orders' => 'change_orders_cents',
        'uncompleted_work' => 'uncompleted_work_cents',
        'credits_deductions' => 'credits_deductions_cents',
        'payments_received' => 'payments_received_cents',
    ];

    public LienProject $project;

    public LienProjectDeadline $deadline;

    public LienFiling $filing;

    public int $step = 1;

    public int $totalSteps = 4;

    // Step 3: Property Details
    public string $is_public_project = 'unknown';

    public ?string $project_type_category = null;

    public ?string $has_legal_description = null;

    public ?string $legal_description = null;

    public ?string $has_apn = null;

    public ?string $apn = null;

    // "I need help finding it": a lien or release customer without the legal
    // description or parcel number pays now and enters it on the filing page
    // (stored as lien_filings.property_help_requested_at).
    public bool $property_help = false;

    public ?string $multiple_parcels = null;

    public bool $owner_is_tenant = false;

    // Step 2: "Is the property leased to someone else?" (yes | no; stored on
    // the project). A yes offers to add the tenant, for the record only.
    public ?string $property_leased = null;

    // Step 3: Important Dates
    public ?string $first_furnish_date = null;

    public ?string $last_furnish_date = null;

    public ?string $completion_date = null;

    // Step 3: The lien being released (releases only; stored in
    // document_details_json.original_lien, not payload_json)
    public ?string $original_lien_recorded_at = null;

    public ?string $original_lien_reference = null;

    public ?string $original_lien_book = null;

    public ?string $original_lien_page = null;

    public ?string $original_lien_other_info = null;

    // Step 3: Notice of intent served (mechanics liens in states that require
    // one; stored in document_details_json, not payload_json)
    public ?string $noi_served = null; // yes | no | not_applicable

    public ?string $noi_served_on = null;

    public ?string $noi_served_to = null;

    public bool $noi_confirmed = false;

    // Step 3: Amount & Contract
    public ?float $amount_claimed = null;

    public ?string $description_of_work = null;

    // Amount breakdown, in dollars (stored on the project in cents, and in
    // the payload_json snapshot at checkout)
    public ?string $has_written_contract = null;

    public ?string $base_contract_amount = null;

    public ?string $change_orders = null;

    public ?string $credits_deductions = null;

    public ?string $payments_received = null;

    public ?string $uncompleted_work = null;

    // Why the amount claimed differs from the balance due (stored on the project)
    public ?string $claim_difference_reason = null;

    // Step 5: Service choice
    public string $service_level = 'full_service';

    // Step 6: Review
    public bool $disclaimerAccepted = false;

    // The customer attested the claim holds no fees, interest or penalties
    // (liens and notices of intent).
    public bool $claimAttested = false;

    // The customer ticked the expedite terms (an expedite rush fee only).
    public bool $rushAcknowledged = false;

    // Checkout sent the customer back because the rush fee changed.
    public bool $rushChanged = false;

    // The customer added the optional Rush (outside the required tiers).
    public bool $rushElected = false;

    public array $warnings = [];

    // File uploads
    public array $attachments = [];

    // Attorney referral (HI, MD, DE mechanics liens)
    public bool $referralSent = false;

    // Party management modal
    public bool $showPartyModal = false;

    public ?int $editingPartyId = null;

    public string $partyRole = 'owner';

    public string $partyName = '';

    public ?string $partyCompanyName = null;

    public ?string $partyAddress1 = null;

    public ?string $partyAddress2 = null;

    public ?string $partyCity = null;

    public ?string $partyState = null;

    public ?string $partyZip = null;

    public ?string $partyEmail = null;

    public ?string $partyPhone = null;

    // The customer ticked "This is really their mailing address" for a
    // general contractor, subcontractor or customer at the jobsite address.
    public bool $partySameAsJobsiteConfirmed = false;

    // The customer ticked "This address is correct as typed" for a ZIP code
    // that seems to be in another state.
    public bool $partyAddressConfirmed = false;

    // Track owner address source: copied_from_jobsite | manual | researched
    public ?string $ownerAddressSource = null;

    // The balance due before a breakdown amount changed (this request only).
    private ?int $balanceBeforeUpdate = null;

    public function mount(LienProject $project, LienProjectDeadline $deadline): void
    {
        Gate::authorize('view', $project);

        $this->project = $project;
        $this->deadline = $deadline;

        $filings = $project->filings()
            ->where('project_deadline_id', $deadline->id)
            ->latest('id')
            ->get();

        // Already paid for: an old link (history, a bookmark, a stale project
        // page) opens that order, never a blank second one. Canceled and
        // refunded orders don't count, so those customers can start over.
        $paid = $filings->first(fn (LienFiling $filing): bool => ! in_array($filing->status, [
            FilingStatus::Draft,
            FilingStatus::AwaitingPayment,
            FilingStatus::Canceled,
            FilingStatus::Refunded,
        ], true));

        if ($paid) {
            session()->flash('message', "You've already paid for this filing. Here's where it stands.");
            $this->redirect(route('lien.filings.show', $paid));

            return;
        }

        // Resume the order furthest along, or start a new draft.
        $this->filing = $filings->first(fn (LienFiling $filing): bool => $filing->status === FilingStatus::AwaitingPayment)
            ?? $filings->first(fn (LienFiling $filing): bool => $filing->status === FilingStatus::Draft)
            ?? $this->startDraft();

        $this->populateFromProject();
        $this->populateFromFiling();

        // Checkout found a new rush fee that needs the customer's OK: back to
        // the review step of an order that already got past it once.
        if (request()->query('rush') === 'changed' && $this->filing->status === FilingStatus::AwaitingPayment) {
            $this->step = $this->totalSteps;
            $this->rushChanged = true;
        }
    }

    /**
     * Every way into a filing lands here (the project page's buttons, the
     * deadline email's link, old links), so every new draft gets the
     * "finish your filing" emails.
     */
    private function startDraft(): LienFiling
    {
        $filing = LienFiling::create([
            'public_id' => Str::ulid()->toBase32(),
            'business_id' => $this->project->business_id,
            'project_id' => $this->project->id,
            'document_type_id' => $this->deadline->document_type_id,
            'project_deadline_id' => $this->deadline->id,
            'jurisdiction_state' => $this->project->jobsite_state,
            'jurisdiction_county' => $this->project->jobsite_county,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => auth()->id(),
        ]);

        EmailSequence::startFor(
            'abandon_checkout',
            $filing,
            auth()->user(),
            $this->project->business,
            route('lien.filings.start', ['project' => $this->project, 'deadline' => $this->deadline])
        );

        return $filing;
    }

    private function populateFromProject(): void
    {
        // Populate saved values from the project
        $this->project_type_category = $this->project->property_class;
        $this->legal_description = $this->project->legal_description;
        $this->apn = $this->project->apn;
        $this->owner_is_tenant = (bool) $this->project->owner_is_tenant;
        $this->property_leased = match ($this->project->property_leased) {
            true => 'yes',
            false => 'no',
            default => $this->project->parties()->where('role', PartyRole::Tenant->value)->exists() ? 'yes' : null,
        };
        $this->has_written_contract = $this->project->has_written_contract !== null
            ? ($this->project->has_written_contract ? '1' : '0')
            : null;

        // Set yes/no toggles based on whether values exist
        if ($this->legal_description) {
            $this->has_legal_description = 'yes';
        }
        if ($this->apn) {
            $this->has_apn = 'yes';
        }

        // Dates
        $this->first_furnish_date = $this->project->first_furnish_date?->format('Y-m-d');
        $this->last_furnish_date = $this->project->last_furnish_date?->format('Y-m-d');
        $this->completion_date = $this->project->completion_date?->format('Y-m-d');

        // Amounts: the project holds them, and staff may have corrected them.
        foreach (self::BREAKDOWN_FIELDS as $field => $column) {
            $cents = $this->project->{$column};
            $this->{$field} = $cents === null ? null : number_format($cents / 100, 2, '.', '');
        }

        $this->claim_difference_reason = $this->project->claim_difference_reason;
    }

    private function populateFromFiling(): void
    {
        // The amount saved on this filing, else the balance due (for a
        // preliminary notice, the contract and its change orders).
        if ($this->filing->amount_claimed_cents) {
            $this->amount_claimed = $this->filing->amount_claimed_cents / 100;
        } else {
            $prefill = $this->amountMode() === 'estimate'
                ? $this->contractTotalCents()
                : $this->project->balanceDueCents();

            $this->amount_claimed = $prefill !== null && $prefill > 0 ? $prefill / 100 : null;
        }

        $this->description_of_work = $this->filing->description_of_work;
        $this->service_level = $this->filing->service_level?->value ?? 'full_service';
        $this->rushElected = (bool) $this->filing->rush_elected;
        $this->property_help = $this->filing->property_help_requested_at !== null;

        // Load any existing payload data
        $payload = $this->filing->payload_json ?? [];
        if (isset($payload['property_details'])) {
            $this->is_public_project = $payload['property_details']['is_public_project'] ?? 'unknown';
            $this->has_legal_description = $payload['property_details']['has_legal_description'] ?? null;
            $this->has_apn = $payload['property_details']['has_apn'] ?? null;
            $this->multiple_parcels = $payload['property_details']['multiple_parcels'] ?? null;
        }

        if ($this->isLienRelease()) {
            $this->populateOriginalLien();
        }

        if ($this->noiRequirement() !== null) {
            $this->populateNoiAnswer();
        }
    }

    /**
     * The notice-of-intent answer the customer already gave on this filing.
     * A "No" is not stored, so it is asked again.
     */
    private function populateNoiAnswer(): void
    {
        $details = $this->filing->document_details_json ?? [];

        if (! empty($details['noi_served_on'])) {
            $this->noi_served = 'yes';
            $this->noi_served_on = $details['noi_served_on'];
            $this->noi_served_to = $details['noi_served_to'] ?? null;
            $this->noi_confirmed = ! empty($details['noi_confirmed_at']);
        } elseif (! empty($details['noi_not_applicable_reason'])) {
            $this->noi_served = 'not_applicable';
        }
    }

    /**
     * The lien being released: what the customer (or an admin) already saved
     * on this filing, else the date and number of the lien we recorded on
     * this project, so they only check them.
     */
    private function populateOriginalLien(): void
    {
        $original = $this->storedOriginalLien();

        $recordedLien = empty($original['recording_reference']) && empty($original['recorded_at'])
            ? $this->recordedLien()
            : null;

        $this->original_lien_recorded_at = $original['recorded_at'] ?? $recordedLien?->recorded_at?->eastern()->format('Y-m-d');
        $this->original_lien_reference = $original['recording_reference'] ?? $recordedLien?->recording_reference;
        $this->original_lien_book = $original['book'] ?? null;
        $this->original_lien_page = $original['page'] ?? null;
        $this->original_lien_other_info = $original['other_info'] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function storedOriginalLien(): array
    {
        $original = ($this->filing->document_details_json ?? [])['original_lien'] ?? null;

        return is_array($original) ? $original : [];
    }

    /**
     * The mechanics lien we recorded on this project, if any.
     */
    private function recordedLien(): ?LienFiling
    {
        return $this->project->latestRecordedLien($this->filing);
    }

    private function isLienRelease(): bool
    {
        return $this->deadline->documentType?->slug === 'lien_release';
    }

    /**
     * Whether this document must name the property by its legal description
     * or parcel number: mechanics liens and lien releases, at every service
     * level (LienFiling::PROPERTY_ID_DOCUMENTS). Notices leave it optional.
     */
    private function requiresPropertyIdentification(): bool
    {
        return in_array($this->deadline->documentType?->slug, LienFiling::PROPERTY_ID_DOCUMENTS, true);
    }

    /**
     * Whether the Property step holds a legal description or parcel number
     * that updateFiling() saves on the project.
     */
    private function hasPropertyIdentification(): bool
    {
        return ($this->has_legal_description === 'yes' && filled($this->legal_description))
            || ($this->has_apn === 'yes' && filled($this->apn));
    }

    /**
     * When the customer chose "I need help finding it": kept from the first
     * time they chose it, cleared once they enter either one, and never set
     * for a document that doesn't require it.
     */
    private function propertyHelpRequestedAt(): ?\DateTimeInterface
    {
        if (! $this->property_help || ! $this->requiresPropertyIdentification() || $this->hasPropertyIdentification()) {
            return null;
        }

        return $this->filing->property_help_requested_at ?? now();
    }

    /**
     * How the Work step asks about money:
     * - claim: liens and notices of intent. The full breakdown, the balance
     *   due it gives, the amount claimed (a reason when the two differ), and
     *   at review the no-fees attestation.
     * - estimate: preliminary notices. The estimated total price only.
     * - basic: demand letters and releases. The amount, with an optional
     *   breakdown.
     */
    private function amountMode(): string
    {
        $slug = $this->deadline->documentType?->slug;

        return match (true) {
            in_array($slug, LienFiling::CLAIM_DOCUMENTS, true) => 'claim',
            $slug === 'prelim_notice' => 'estimate',
            default => 'basic',
        };
    }

    /**
     * Dollars as entered, in cents; null when blank or not a number.
     */
    private function toCents(mixed $dollars): ?int
    {
        if ($dollars === null || ! is_numeric($dollars)) {
            return null;
        }

        return (int) round((float) $dollars * 100);
    }

    private function amountClaimedCents(): ?int
    {
        $cents = $this->toCents($this->amount_claimed);

        return $cents !== null && $cents > 0 ? $cents : null;
    }

    /**
     * The balance due from the breakdown as entered: contract amount plus
     * change orders, minus work not done, credits and payments, with blanks
     * as 0. Null until the contract amount is entered.
     */
    private function balanceDueCents(): ?int
    {
        $base = $this->toCents($this->base_contract_amount);

        if ($base === null) {
            return null;
        }

        return $base
            + ($this->toCents($this->change_orders) ?? 0)
            - ($this->toCents($this->uncompleted_work) ?? 0)
            - ($this->toCents($this->credits_deductions) ?? 0)
            - ($this->toCents($this->payments_received) ?? 0);
    }

    /**
     * The project's contract amount with its change orders, or null.
     */
    private function contractTotalCents(): ?int
    {
        return $this->project->base_contract_amount_cents === null
            ? null
            : $this->project->base_contract_amount_cents + ($this->project->change_orders_cents ?? 0);
    }

    /**
     * Before a breakdown amount changes, note the balance due, so updated()
     * can tell whether the claim still follows it.
     */
    public function updating(string $property, mixed $value): void
    {
        if (array_key_exists($property, self::BREAKDOWN_FIELDS)) {
            $this->balanceBeforeUpdate = $this->balanceDueCents();
        }
    }

    /**
     * A customer who says they have the legal description no longer needs
     * the "I need help finding it" choice, which is then hidden.
     */
    public function updatedHasLegalDescription(mixed $value): void
    {
        if ($value === 'yes') {
            $this->property_help = false;
        }
    }

    /**
     * The same for the parcel number.
     */
    public function updatedHasApn(mixed $value): void
    {
        if ($value === 'yes') {
            $this->property_help = false;
        }
    }

    /**
     * The amount claimed starts as the balance due and follows it while the
     * customer changes the breakdown, until they enter a different amount.
     */
    public function updated(string $property, mixed $value): void
    {
        if (! array_key_exists($property, self::BREAKDOWN_FIELDS) || $this->amountMode() !== 'claim') {
            return;
        }

        $balance = $this->balanceDueCents();
        $claim = $this->toCents($this->amount_claimed);

        if ($balance !== null && ($claim === null || $claim === $this->balanceBeforeUpdate)) {
            $this->amount_claimed = $balance / 100;
        }
    }

    /**
     * The state's entry in database/data/lien_variants/notice_of_intent.php
     * when this is a mechanics lien in a state that requires a notice of
     * intent from every claimant ('required') or from some ('required_some').
     * Null otherwise, and for liens only an attorney can file.
     *
     * @return array<string, mixed>|null
     */
    private function noiRequirement(): ?array
    {
        if ($this->deadline->documentType?->slug !== 'mechanics_lien' || $this->requiresAttorneyReferral) {
            return null;
        }

        $entry = NoticeOfIntentStatePage::entries()[strtoupper((string) $this->project->jobsite_state)] ?? null;

        return in_array($entry['status'] ?? null, ['required', 'required_some'], true) ? $entry : null;
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->updateFiling();

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }

        // Checkout redirects when nothing blocks it, so show these on arrival.
        if ($this->step === $this->totalSteps) {
            $this->warnings = $this->workDateWarnings();
        }
    }

    /**
     * Work dates that look wrong but can be right: a last furnish date after
     * the completion date (punch-list work can follow substantial
     * completion). Only where the Work step asks for the completion date.
     *
     * @return list<string>
     */
    private function workDateWarnings(): array
    {
        if (! $this->completion_date || ! $this->last_furnish_date || ! array_key_exists('completion_date', $this->requiredDateFields)) {
            return [];
        }

        try {
            $completion = CarbonImmutable::parse($this->completion_date)->startOfDay();
            $lastFurnish = CarbonImmutable::parse($this->last_furnish_date)->startOfDay();
        } catch (\Throwable) {
            return [];
        }

        return $completion->lessThan($lastFurnish)
            ? ['Your last furnish date is after the completion date. Check both dates.']
            : [];
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= $this->totalSteps && $step <= $this->step) {
            $this->step = $step;
        }
    }

    /**
     * Get the required date fields for this filing: the first and last
     * furnish dates (always required), plus any date the state's deadline
     * rules run from.
     *
     * @return array<string, string> Map of field name => human label
     */
    public function getRequiredDateFieldsProperty(): array
    {
        $docSlug = $this->deadline->documentType->slug;

        $rules = LienDeadlineRule::where('state', $this->project->jobsite_state)
            ->whereHas('documentType', fn ($q) => $q->where('slug', $docSlug))
            ->where(function ($q) {
                $claimant = $this->project->claimant_type;
                if ($claimant) {
                    $q->where('claimant_type', $claimant->value)
                        ->orWhere('claimant_type', 'any');
                } else {
                    $q->where('claimant_type', 'any');
                }
            })
            ->where(function ($q) {
                $scope = $this->project->property_class;
                if ($scope && $scope !== 'government') {
                    $q->where('effective_scope', $scope)
                        ->orWhere('effective_scope', 'both');
                }
            })
            ->get();

        $labels = config('lien.missing_field_labels', []);
        $fields = [
            'first_furnish_date' => $labels['first_furnish_date'] ?? 'first furnish date',
            'last_furnish_date' => $labels['last_furnish_date'] ?? 'last furnish date',
        ];

        foreach ($rules as $rule) {
            $fieldName = $rule->trigger_event->value;
            if (in_array($fieldName, ['completion_date', 'last_furnish_date', 'first_furnish_date'])) {
                $fields[$fieldName] ??= $labels[$fieldName] ?? $fieldName;
            }
        }

        return $fields;
    }

    private function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'project_type_category' => ['required', 'in:residential,commercial,government'],
            ], [
                'project_type_category.required' => 'Please select a property type.',
            ]),
            2 => $this->validatePartiesStep(),
            3 => $this->validateWorkStep(),
            default => null,
        };
    }

    private function validateWorkStep(): void
    {
        $rules = [
            'has_written_contract' => ['required', 'in:0,1'],
            'amount_claimed' => ['required', 'numeric', 'min:0.01'],
            'description_of_work' => ['required', 'string', 'min:10'],
        ];

        $messages = [
            'has_written_contract.required' => 'Please indicate whether the work was done pursuant to a written contract.',
        ];

        $notTooOld = $this->notMoreThanThreeYearsAgo();

        foreach ($this->requiredDateFields as $fieldName => $label) {
            $rules[$fieldName] = ['required', 'date', 'before_or_equal:today', $notTooOld];
            $messages["{$fieldName}.required"] = in_array($fieldName, ['first_furnish_date', 'last_furnish_date'], true)
                ? "The {$label} is required."
                : "The {$label} is required to file in this state.";
        }

        if ($this->first_furnish_date) {
            $rules['last_furnish_date'][] = 'after_or_equal:first_furnish_date';
        }

        $attributes = [];

        if ($this->amountMode() === 'claim') {
            $rules += [
                'base_contract_amount' => ['required', 'numeric', 'min:0'],
                'change_orders' => ['nullable', 'numeric'],
                'uncompleted_work' => ['nullable', 'numeric', 'min:0'],
                'credits_deductions' => ['required', 'numeric', 'min:0'],
                'payments_received' => ['required', 'numeric', 'min:0'],
                'claim_difference_reason' => [Rule::requiredIf($this->claimDiffersFromBalance), 'nullable', 'string', 'max:1000'],
            ];

            $messages += [
                'base_contract_amount.required' => 'Enter the original contract amount.',
                'credits_deductions.required' => 'Enter the credits and deductions. Enter 0 if there are none.',
                'payments_received.required' => 'Enter the payments received. Enter 0 if there are none.',
                'amount_claimed.required' => 'Enter the amount you are claiming.',
                'amount_claimed.min' => 'The amount you are claiming must be more than $0.',
                'claim_difference_reason.required' => 'Tell us why the amount claimed is different from the balance due.',
            ];

            $attributes += [
                'base_contract_amount' => 'original contract amount',
                'change_orders' => 'change orders',
                'uncompleted_work' => 'value of work not done',
                'credits_deductions' => 'credits and deductions',
                'payments_received' => 'payments received',
                'amount_claimed' => 'amount you are claiming',
                'claim_difference_reason' => 'reason',
            ];
        } elseif ($this->amountMode() === 'estimate') {
            $messages += [
                'amount_claimed.required' => 'Enter the estimated total price.',
                'amount_claimed.min' => 'The estimated total price must be more than $0.',
            ];

            $attributes['amount_claimed'] = 'estimated total price';
        }

        if (($noi = $this->noiRequirement()) !== null) {
            $rules += [
                'noi_served' => ['required', Rule::in($noi['status'] === 'required_some' ? ['yes', 'no', 'not_applicable'] : ['yes', 'no'])],
                'noi_served_on' => ['exclude_unless:noi_served,yes', 'required', 'date', 'before_or_equal:today'],
                'noi_served_to' => ['exclude_unless:noi_served,yes', 'required', Rule::in(array_keys(self::NOI_SERVED_TO))],
                'noi_confirmed' => ['exclude_unless:noi_served,yes', 'accepted'],
            ];

            $messages += [
                'noi_served.required' => 'Tell us whether the notice of intent has been served.',
                'noi_served_on.required' => 'Enter the date the notice of intent was served.',
                'noi_served_on.before_or_equal' => "The date served can't be in the future.",
                'noi_served_to.required' => 'Choose who received the notice of intent.',
                'noi_confirmed.accepted' => 'Confirm the notice of intent was served.',
            ];

            $attributes += [
                'noi_served' => 'answer',
                'noi_served_on' => 'date served',
                'noi_served_to' => 'who received it',
            ];
        }

        if ($this->isLienRelease()) {
            $rules += [
                'original_lien_recorded_at' => ['required', 'date', 'before_or_equal:today'],
                'original_lien_reference' => ['required_without_all:original_lien_book,original_lien_page', 'nullable', 'string', 'max:255'],
                'original_lien_book' => ['required_with:original_lien_page', 'nullable', 'string', 'max:50'],
                'original_lien_page' => ['required_with:original_lien_book', 'nullable', 'string', 'max:50'],
                'original_lien_other_info' => ['nullable', 'string', 'max:1000'],
            ];

            $messages += [
                'original_lien_recorded_at.required' => 'Enter the date the lien was recorded.',
                'original_lien_recorded_at.before_or_equal' => "The recorded date can't be in the future.",
                'original_lien_reference.required_without_all' => 'Enter the document number, or the book and page.',
                'original_lien_book.required_with' => 'Enter the book as well as the page.',
                'original_lien_page.required_with' => 'Enter the page as well as the book.',
            ];

            $attributes += [
                'original_lien_recorded_at' => 'recorded date',
                'original_lien_reference' => 'document number',
                'original_lien_book' => 'book',
                'original_lien_page' => 'page',
                'original_lien_other_info' => 'other identifying information',
            ];
        }

        $this->validate($rules, $messages, $attributes);
    }

    /**
     * Customers type the wrong year: a work date more than three years back
     * is almost always one.
     */
    private function notMoreThanThreeYearsAgo(): Closure
    {
        $earliest = now()->eastern()->startOfDay()->subYears(3);

        return function (string $attribute, mixed $value, Closure $fail) use ($earliest): void {
            try {
                $date = CarbonImmutable::parse((string) $value, BusinessDays::TIMEZONE)->startOfDay();
            } catch (\Throwable) {
                return; // the date rule reports it
            }

            if ($date->lessThan($earliest)) {
                $fail('That date is more than 3 years ago. Check the year.');
            }
        };
    }

    /**
     * Validate the Parties step: a property owner is always required, and a
     * General Contractor (and Subcontractor, where applicable) is required when
     * the claimant is downstream of the GC. The party the claimant contracted
     * with (and a customer party) needs a full mailing address, and the GC,
     * a subcontractor or the customer can't use the jobsite address unless
     * the customer confirmed it (the owner can). All gaps are collected so they surface
     * together; party gaps under "party_{id}", shown on that party's card.
     */
    private function validatePartiesStep(): void
    {
        $messages = [];

        if (! $this->project->ownerParty()) {
            $messages['owner_required'] = 'Property owner is required to continue.';
        }

        $claimantType = $this->project->claimant_type;

        if ($claimantType?->requiresGcParty() && ! $this->project->gcParty()) {
            $messages['gc_required'] = 'Add the General Contractor or direct contractor you contracted under to continue.';
        }

        if ($claimantType?->requiresSubcontractorParty() && ! $this->project->subcontractorParty()) {
            $messages['subcontractor_required'] = 'Add the Subcontractor you contracted under to continue.';
        }

        $hiringParty = $this->project->hiringParty();

        foreach ($this->project->parties()->get() as $party) {
            $hiredYou = $party->role === PartyRole::Customer || $party->is($hiringParty);

            if ($hiredYou && ($missing = $party->missingMailingAddressParts()) !== []) {
                $messages['party_'.$party->id][] = "{$this->partyLabel($party)} hired you, so we need their full mailing address. Add their ".$this->listInWords($missing).'.';
            }

            if ($this->project->mailingAddressMustDifferFromJobsite($party->role)
                && ! $party->address_same_as_jobsite_confirmed
                && $party->matchesJobsite($this->project)) {
                $messages['party_'.$party->id][] = "The address for {$this->partyLabel($party)} is the jobsite address. Edit it to their mailing address. If the jobsite really is their mailing address, edit the party and tick the box.";
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    /**
     * A party as the Parties step names it: "Acme Builders (general contractor)".
     */
    private function partyLabel(LienParty $party): string
    {
        $name = $party->displayName() ?: 'This party';

        return $party->role === null ? $name : "{$name} ({$party->role->phrase()})";
    }

    /**
     * "street address, city and ZIP code".
     *
     * @param  list<string>  $items
     */
    private function listInWords(array $items): string
    {
        $last = array_pop($items);

        return $items === [] ? (string) $last : implode(', ', $items).' and '.$last;
    }

    /**
     * Validate the review step before checkout.
     * Collects errors via addError() and warnings via $this->warnings.
     */
    private function validateReviewStep(): void
    {
        $this->warnings = $this->workDateWarnings();

        $docType = $this->deadline->documentType->slug;
        $owner = $this->project->ownerParty();

        // Liens and releases name the property by its legal description or
        // parcel number at every service level (EREG-136). "I need help
        // finding it" lets the customer pay now and enter it on the filing
        // page; staff never look it up.
        if ($this->requiresPropertyIdentification() && ! $this->hasPropertyIdentification() && ! $this->property_help) {
            $this->addError('property_identification',
                'Add the legal description or the parcel number on the Property step. If you can\'t find either, choose "I need help finding it" there.');
        }

        // Mechanics Lien + Self-Serve: require the owner
        if ($docType === 'mechanics_lien' && $this->service_level === 'self_serve') {
            if (! $owner || ! $owner->hasMailingAddress()) {
                $this->addError('owner_party',
                    'Owner name and mailing address required for Self-Serve mechanics lien.');
            }
        }

        // Prelim notice: warn if missing owner
        if ($docType === 'prelim_notice') {
            if (! $owner) {
                $this->warnings[] = 'Consider adding the property owner for complete service.';
            }
        }

        // NOI: warn only
        if ($docType === 'noi') {
            if (! $owner) {
                $this->warnings[] = 'Adding the property owner is recommended but not required.';
            }
        }

        // Check jurisdiction-specific warnings for lien-related filings
        if (in_array($docType, ['mechanics_lien', 'lien_enforcement'])) {
            $this->validateJurisdictionRights();
        }

        // Past the deadline: no path reaches checkout.
        if ($this->deadlineGate()['passed'] ?? false) {
            $this->addError('deadline_passed', $docType === 'noi'
                ? "We're unable to send this notice of intent: the lien deadline has passed."
                : "We're unable to file this lien: the deadline has passed.");
        }

        if ($this->noiRequirement() !== null) {
            if ($this->noi_served === null) {
                $this->addError('noi_answer', 'Go back to the Work step and tell us whether the notice of intent has been served.');
            } elseif ($this->noi_served === 'yes' && (! $this->noi_served_on || ! $this->noi_served_to || ! $this->noi_confirmed)) {
                $this->addError('noi_answer', 'Go back to the Work step and confirm the notice of intent.');
            } elseif (($block = $this->noiBlock()) !== null) {
                $this->addError('noi_block', $block);
            }
        }
    }

    /**
     * Why the customer's notice-of-intent answer stops checkout, or null: no
     * notice served yet, or one served too late for the state's lead time
     * (the day count on the state's lien_deadline_rules noi rows) to run out
     * before the lien deadline. No day count on the rows, no lead-time check.
     */
    private function noiBlock(): ?string
    {
        if ($this->noiRequirement() === null) {
            return null;
        }

        $state = $this->stateName();

        if ($this->noi_served === 'no') {
            return "{$state} requires a notice of intent before the lien. Order the notice of intent first.";
        }

        if ($this->noi_served !== 'yes' || ! $this->noi_served_on) {
            return null;
        }

        $leadDays = $this->noiLeadDays();
        $lienDeadline = $this->deadline->fresh()?->due_date;

        if ($leadDays === null || $lienDeadline === null) {
            return null;
        }

        try {
            $servedOn = CarbonImmutable::parse($this->noi_served_on)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $deadline = CarbonImmutable::parse($lienDeadline->format('Y-m-d'));

        if ($servedOn->addDays($leadDays)->lessThanOrEqualTo($deadline)) {
            return null;
        }

        return "The notice of intent was served on {$servedOn->format('F j, Y')}. {$state} requires {$leadDays} days between the notice and the lien, which runs past the lien deadline of {$deadline->format('F j, Y')}.";
    }

    /**
     * Days the state requires between the notice of intent and the lien: the
     * offset on its noi deadline rule rows (every row carries the same
     * number), or null when no row has one or the rows can't be trusted.
     */
    private function noiLeadDays(): ?int
    {
        if (in_array(strtoupper((string) $this->project->jobsite_state), self::NOI_LEAD_TIME_UNRELIABLE_STATES, true)) {
            return null;
        }

        $days = LienDeadlineRule::where('state', $this->project->jobsite_state)
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
            ->where('offset_days', '>', 0)
            ->max('offset_days');

        return $days === null ? null : (int) $days;
    }

    /**
     * The link that starts this project's notice of intent, or null.
     */
    private function noiOrderUrl(): ?string
    {
        $noi = $this->project->getDeadlineForType('noi');

        return $noi ? route('lien.filings.start', ['project' => $this->project, 'deadline' => $noi]) : null;
    }

    /**
     * What step 4 shows about the deadline of a mechanics lien, preliminary
     * notice or notice of intent. Null for other documents, and when there
     * is nothing to show or block.
     *
     * - panel: the document's own deadline and submit-by date, shown when its
     *   deadline row has a rule (otherwise it is optional, with no deadline).
     * - passed: checkout is blocked. A mechanics lien past its own deadline;
     *   a notice of intent once the project's lien deadline has passed (the
     *   lien it announces can no longer be filed). Its own row is not used:
     *   some states' noi rows count the wrong way (EREG-91).
     * - late: a preliminary notice past its usual deadline. Never blocked,
     *   since a late notice can still protect part of a claim in some states.
     *
     * The submit-by date is the deadline less the county's recording lead
     * (config lien.recording_lead_business_days), counted back from the last
     * business day on or before the deadline, since the county only takes
     * filings on business days.
     *
     * @return array{document: string, slug: string, isNotice: bool, panel: bool, known: bool, passed: bool, late: bool, lienDeadline: ?CarbonImmutable, deadline?: CarbonImmutable, method?: string, mustSubmitBy?: CarbonImmutable, businessDaysLeft?: int, datesUsed?: array<string, string>}|null
     */
    private function deadlineGate(): ?array
    {
        $slug = $this->deadline->documentType?->slug;

        if (! in_array($slug, self::DEADLINE_DOCUMENTS, true)) {
            return null;
        }

        $deadline = $this->deadline->fresh() ?? $this->deadline;
        $hasRule = $deadline->deadline_rule_id !== null;
        $today = now()->eastern()->startOfDay();
        $lienDeadline = $slug === 'noi' ? $this->lienDeadlineDate() : null;

        $gate = [
            'document' => $this->deadline->documentType->name,
            'slug' => $slug,
            'isNotice' => in_array($slug, self::NOTICES, true),
            'panel' => $hasRule,
            'known' => false,
            'passed' => false,
            'late' => false,
            'lienDeadline' => $lienDeadline,
        ];

        $ownDeadline = $hasRule ? app(StepStatusCalculator::class)->forDeadline($deadline)->deadlineDate : null;

        if ($ownDeadline !== null) {
            $due = CarbonImmutable::createFromFormat('!Y-m-d', $ownDeadline->format('Y-m-d'), BusinessDays::TIMEZONE);
            $method = $this->recordingMethod();
            $lead = max(0, (int) config("lien.recording_lead_business_days.{$method}", $method === 'mail' ? 5 : 1));
            $lastBusinessDay = BusinessDays::isBusinessDay($due) ? $due : BusinessDays::subtract($due, 1);
            $mustSubmitBy = BusinessDays::subtract($lastBusinessDay, $lead);

            $gate = array_replace($gate, [
                'known' => true,
                'deadline' => $due,
                'method' => $method,
                'mustSubmitBy' => $mustSubmitBy,
                'businessDaysLeft' => BusinessDays::between($today, $mustSubmitBy),
            ]);
        }

        $ownDeadlinePassed = isset($gate['deadline']) && $gate['deadline']->lessThan($today);

        $gate['passed'] = match ($slug) {
            'mechanics_lien' => $ownDeadlinePassed,
            'noi' => $lienDeadline !== null && $lienDeadline->lessThan($today),
            default => false,
        };
        $gate['late'] = $slug === 'prelim_notice' && $ownDeadlinePassed;

        if ($gate['passed'] || $gate['late']) {
            $gate['datesUsed'] = $this->datesUsed($deadline);
        }

        return $gate['panel'] || $gate['passed'] ? $gate : null;
    }

    /**
     * The rush fee step 4 shows under the deadline panel, or null: a
     * full-service order whose submit-by date is 6 or fewer business days
     * away, or already gone, while its deadline is still ahead (RushTier),
     * and whose tier has an active fee row. Worked out on every
     * render from today's date, so a customer who comes back a day later
     * sees that day's fee. An expedite fee carries the terms to tick.
     *
     * @param  array<string, mixed>|null  $gate  deadlineGate()
     * @return array{tier: string, label: string, price: Price, businessDaysLeft: int, reason: string, acknowledgement: ?string}|null
     */
    private function rushFee(?array $gate): ?array
    {
        if ($gate === null || $gate['passed'] || ! isset($gate['mustSubmitBy'])) {
            return null;
        }

        $tier = RushTier::for($gate['slug'], $this->service_level, $gate['mustSubmitBy'], $gate['deadline'] ?? null);
        $price = RushTier::price($tier);

        if ($tier === null || $price === null) {
            return null;
        }

        return [
            'tier' => $tier,
            'label' => RushTier::label($tier),
            'price' => $price,
            'businessDaysLeft' => $gate['businessDaysLeft'],
            'reason' => RushTier::reason($gate['businessDaysLeft'], $gate['slug'], $tier),
            'acknowledgement' => RushTier::requiresAcknowledgement($tier) ? RushTier::acknowledgement($gate['slug']) : null,
        ];
    }

    /**
     * The optional Rush step 4 offers, or null: a full-service order on a
     * document that pays rush fees, outside the required tiers (7 or more
     * business days left, or no deadline known), with an active
     * lien/rush_fee/rush row (RushTier::electable()).
     *
     * @param  array<string, mixed>|null  $gate  deadlineGate()
     * @return array{price: Price, label: string}|null
     */
    private function rushUpsell(?array $gate): ?array
    {
        if ($gate['passed'] ?? false) {
            return null;
        }

        $electable = RushTier::electable(
            $this->deadline->documentType?->slug,
            $this->service_level,
            $gate['mustSubmitBy'] ?? null,
            $gate['deadline'] ?? null,
        );
        $price = $electable ? RushTier::price(RushTier::RUSH) : null;

        return $price ? ['price' => $price, 'label' => RushTier::electLabel($price)] : null;
    }

    /**
     * The project's mechanics lien deadline row (the one with a rule, most
     * recently calculated), or null.
     */
    private function lienDeadlineRow(): ?LienProjectDeadline
    {
        return $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->whereNotNull('deadline_rule_id')
            ->latest('updated_at')
            ->latest('id')
            ->first();
    }

    /**
     * The project's mechanics lien deadline as a calendar date, or null when
     * it is not known.
     */
    private function lienDeadlineDate(): ?CarbonImmutable
    {
        $lien = $this->lienDeadlineRow();
        $date = $lien ? app(StepStatusCalculator::class)->forDeadline($lien)->deadlineDate : null;

        return $date ? CarbonImmutable::createFromFormat('!Y-m-d', $date->format('Y-m-d'), BusinessDays::TIMEZONE) : null;
    }

    /**
     * How the county takes the document: 'mail' when the county or state
     * data file (database/data/lien_counties, lien_documents) says its office
     * takes mail, else 'erecord' (e-recording, an office that takes either,
     * or nothing known).
     */
    private function recordingMethod(): string
    {
        $state = strtoupper((string) ($this->filing->jurisdiction_state ?: $this->project->jobsite_state));

        if ($state === '') {
            return 'erecord';
        }

        $method = LienDocumentRegistry::for($state)['recording']['filing_office']['method'] ?? null;
        $county = LienDocumentRegistry::county($state, LienCountyKey::forFiling($this->filing));
        $method = $county['recording']['filing_office']['method'] ?? $method;

        return $method === 'mail' ? 'mail' : 'erecord';
    }

    /**
     * The project dates the deadline was computed from, label => date, in
     * Work-step order. A notice of intent counts back from the lien
     * deadline, so it lists the lien rule's dates. With nothing to go on,
     * every work date the project has.
     *
     * @return array<string, string>
     */
    private function datesUsed(LienProjectDeadline $deadline): array
    {
        $source = $deadline;

        if ($this->deadline->documentType?->slug === 'noi') {
            $source = $this->lienDeadlineRow();
        }

        $fields = [];

        if ($source?->rule) {
            $anchorLogic = LienStateRule::query()->find($this->project->jobsite_state)?->lien_anchor_logic;

            // The engine reads the first furnish date as the contract date.
            $fields = array_map(
                fn (string $field) => $field === 'contract_date' ? 'first_furnish_date' : $field,
                RuleDateMath::inputs(RuleDateMath::fromModel($source->rule), $anchorLogic),
            );
        }

        $dates = [];

        foreach (self::WORK_DATE_LABELS as $field => $label) {
            if (in_array($field, $fields, true) && $this->project->{$field}) {
                $dates[$label] = $this->project->{$field}->format('F j, Y');
            }
        }

        if (($source?->status_meta['noc_shortened'] ?? false) && $this->project->noc_filed_date) {
            $dates['Notice of Completion Filed'] = $this->project->noc_filed_date->format('F j, Y');
        }

        if ($dates === []) {
            foreach (self::WORK_DATE_LABELS as $field => $label) {
                if ($this->project->{$field}) {
                    $dates[$label] = $this->project->{$field}->format('F j, Y');
                }
            }
        }

        return $dates;
    }

    /**
     * The demand letter offered once the deadline has passed: the link that
     * starts it on this project and its price, or null when the project has
     * no demand-letter step.
     *
     * @return array{url: string, price: ?string}|null
     */
    private function demandLetterOffer(): ?array
    {
        $demand = $this->project->getDeadlineForType('demand_letter');

        if (! $demand) {
            return null;
        }

        try {
            $price = Price::resolveLien('demand_letter', 'full_service', $this->project->jobsite_state)->formattedAmount();
        } catch (InvalidArgumentException) {
            $price = null;
        }

        return [
            'url' => route('lien.filings.start', ['project' => $this->project, 'deadline' => $demand]),
            'price' => $price,
        ];
    }

    /**
     * Validate claimant has lien rights and check property restrictions.
     */
    private function validateJurisdictionRights(): void
    {
        $stateRule = LienStateRule::where('state', $this->project->jobsite_state)->first();

        if (! $stateRule) {
            return;
        }

        // 1. Claimant rights check
        $claimantType = $this->project->claimant_type?->value;
        $hasRights = match ($claimantType) {
            'gc' => $stateRule->gc_has_lien_rights,
            'subcontractor' => $stateRule->sub_has_lien_rights,
            'sub_sub_contractor' => $stateRule->subsub_has_lien_rights,
            'supplier_to_owner' => $stateRule->supplier_owner_has_lien_rights,
            'supplier_to_contractor' => $stateRule->supplier_gc_has_lien_rights,
            'supplier_to_subcontractor' => $stateRule->supplier_sub_has_lien_rights,
            default => true,
        };

        if (! $hasRights) {
            $this->warnings[] = "Your claimant type may not have lien rights in {$stateRule->state}.";
        }

        // 2. Property restriction checks
        $propertyContext = $this->project->property_context ?? 'unknown';

        if ($propertyContext === 'tenant_improvement' && ! $stateRule->tenant_project_lien_allowed) {
            $this->warnings[] = "Tenant improvements may not be lienable in {$stateRule->state}.";
        }

        if ($propertyContext === 'tenant_improvement' && $stateRule->tenant_project_restrictions !== 'none') {
            $this->warnings[] = "Tenant lien restrictions apply: {$stateRule->tenant_project_restrictions}";
        }

        if ($propertyContext === 'owner_occupied' && $stateRule->owner_occupied_restriction_type !== 'none') {
            $this->warnings[] = "Owner-occupied restrictions: {$stateRule->owner_occupied_restriction_type}";
        }

        // Notarization and oath requirements are handled by the service team
        // for Full Service filings, so we don't surface them as warnings.
    }

    private function updateFiling(): void
    {
        $projectData = [
            'legal_description' => $this->has_legal_description === 'yes' ? $this->legal_description : null,
            'apn' => $this->has_apn === 'yes' ? $this->apn : null,
            'owner_is_tenant' => $this->owner_is_tenant,
            'property_leased' => match ($this->property_leased) {
                'yes' => true,
                'no' => false,
                default => null,
            },
            'property_class' => $this->project_type_category,
            'has_written_contract' => $this->has_written_contract === '1',
        ];

        // Past the Parties step, an unanswered "who hired you" follows the
        // hiring party the step required, so the documents name it too.
        if ($this->step >= 2 && blank($this->project->hired_by) && ($hiredBy = $this->project->hiringPartyRole()?->hiredBy())) {
            $projectData['hired_by'] = $hiredBy;
        }

        if ($this->step >= 3) {
            $projectData['first_furnish_date'] = $this->first_furnish_date ?: null;
            $projectData['last_furnish_date'] = $this->last_furnish_date ?: null;
            $projectData['completion_date'] = $this->completion_date ?: null;
            $projectData += $this->projectAmounts();

            $this->logDatesChangedAfterDeadlineBlock($projectData);
        }

        $this->project->update($projectData);

        if ($this->step >= 3) {
            app(DeadlineCalculator::class)->calculateForProject($this->project->fresh());
        }

        $filingData = [
            'amount_claimed_cents' => $this->amountClaimedCents(),
            'description_of_work' => $this->description_of_work,
            'service_level' => $this->service_level,
            'property_help_requested_at' => $this->propertyHelpRequestedAt(),
        ];

        if ($this->step >= 3 && $this->isLienRelease()) {
            $filingData['document_details_json'] = $this->documentDetailsWithOriginalLien();
        }

        if ($this->step >= 3 && $this->noiRequirement() !== null) {
            $filingData['document_details_json'] = $this->documentDetailsWithNoiAnswer();
        }

        if ($this->step >= 3 && $this->amountMode() === 'estimate') {
            $filingData['document_details_json'] = $this->documentDetailsWithEstimate();
        }

        $this->filing->update($filingData);
    }

    /**
     * The amounts the Work step saves on the project, in cents. Liens and
     * notices of intent save the whole breakdown (blank change orders or
     * work not done as 0) and the reason the claim differs from the balance
     * due, cleared once they agree. A preliminary notice asks only for an
     * estimate, so it leaves the project's amounts alone.
     *
     * @return array<string, int|string|null>
     */
    private function projectAmounts(): array
    {
        $mode = $this->amountMode();

        if ($mode === 'estimate') {
            return [];
        }

        $amounts = [];

        foreach (self::BREAKDOWN_FIELDS as $field => $column) {
            $amounts[$column] = $this->toCents($this->{$field});
        }

        if ($mode === 'claim') {
            $amounts['change_orders_cents'] ??= 0;
            $amounts['uncompleted_work_cents'] ??= 0;
            $amounts['claim_difference_reason'] = $this->claimDiffersFromBalance
                ? $this->nullIfBlank($this->claim_difference_reason)
                : null;
        }

        return $amounts;
    }

    /**
     * The filing's document details with the customer's estimated total
     * price as the estimate a preliminary notice states (the Document
     * details card edits the same value). Other details are kept.
     *
     * @return array<string, mixed>|null
     */
    private function documentDetailsWithEstimate(): ?array
    {
        $details = $this->filing->document_details_json ?? [];
        $estimate = $this->amountClaimedCents();

        if ($estimate === null) {
            unset($details['estimated_price_cents']);
        } else {
            $details['estimated_price_cents'] = $estimate;
        }

        return $details === [] ? null : $details;
    }

    /**
     * Once the wizard has told the customer the deadline passed, log any
     * change to the work dates: they go on a sworn document, so staff check
     * dates that moved after a block.
     *
     * @param  array<string, mixed>  $projectData
     */
    private function logDatesChangedAfterDeadlineBlock(array $projectData): void
    {
        if ($this->filing->deadline_block_shown_at === null) {
            return;
        }

        $before = [];
        $after = [];

        foreach (array_keys(self::WORK_DATE_LABELS) as $field) {
            $before[$field] = $this->project->{$field}?->format('Y-m-d');
            $after[$field] = $projectData[$field] ? CarbonImmutable::parse($projectData[$field])->format('Y-m-d') : null;
        }

        if ($before === $after) {
            return;
        }

        $this->filing->events()->create([
            'business_id' => $this->filing->business_id,
            'event_type' => LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK,
            'payload_json' => ['before' => $before, 'after' => $after],
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * The filing's document details with the customer's notice-of-intent
     * answer in place of any earlier one; other details are kept. The
     * confirmation time stays put while the date and recipient don't change.
     *
     * @return array<string, mixed>|null
     */
    private function documentDetailsWithNoiAnswer(): ?array
    {
        $previous = $this->filing->document_details_json ?? [];
        $details = array_diff_key($previous, array_flip(LienFiling::NOI_ANSWER_KEYS));

        if ($this->noi_served === 'yes' && $this->noi_served_on && $this->noi_confirmed) {
            $servedOn = CarbonImmutable::parse($this->noi_served_on)->toDateString();
            $unchanged = ($previous['noi_served_on'] ?? null) === $servedOn
                && ($previous['noi_served_to'] ?? null) === $this->noi_served_to;

            $details['noi_served_on'] = $servedOn;
            $details['noi_served_to'] = $this->noi_served_to;
            $details['noi_confirmed_at'] = $unchanged && ! empty($previous['noi_confirmed_at'])
                ? $previous['noi_confirmed_at']
                : now()->toIso8601String();
        } elseif ($this->noi_served === 'not_applicable') {
            $details['noi_not_applicable_reason'] = 'customer_says_not_applicable';
        }

        return $details === [] ? null : $details;
    }

    /**
     * The filing's document details with the lien being released as the
     * customer entered it. Other details, and the county and amount received
     * an admin may have set on the lien, are kept. Only set values are
     * stored, the same shape the admin Document details card writes.
     *
     * @return array<string, mixed>|null
     */
    private function documentDetailsWithOriginalLien(): ?array
    {
        $details = $this->filing->document_details_json ?? [];
        $existing = $this->storedOriginalLien();

        $original = array_filter([
            'recording_reference' => $this->nullIfBlank($this->original_lien_reference),
            'book' => $this->nullIfBlank($this->original_lien_book),
            'page' => $this->nullIfBlank($this->original_lien_page),
            'county' => $this->nullIfBlank($existing['county'] ?? null),
            'recorded_at' => $this->nullableDate($this->original_lien_recorded_at),
            'amount_received_cents' => $this->asCents($existing['amount_received_cents'] ?? null),
            'other_info' => $this->nullIfBlank($this->original_lien_other_info),
        ], fn ($value) => $value !== null);

        if ($original === []) {
            unset($details['original_lien']);
        } else {
            $details['original_lien'] = $original;
        }

        return $details === [] ? null : $details;
    }

    public function proceedToCheckout(): void
    {
        if ($this->requiresAttorneyReferral) {
            abort(403, $this->referralStateName.' mechanics liens must be filed directly with the court.');
        }

        // Validate service level selection
        $this->validate([
            'service_level' => ['required', 'in:self_serve,full_service'],
        ]);

        // Validate review step (checks property identification, owner, etc.)
        $this->validateReviewStep();

        // Check if there are any errors
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        $rules = ['disclaimerAccepted' => ['accepted']];
        $messages = ['disclaimerAccepted.accepted' => 'You must accept the disclaimer to continue.'];

        if ($this->amountMode() === 'claim') {
            $rules['claimAttested'] = ['accepted'];
            $messages['claimAttested.accepted'] = 'Confirm the amount claimed has no fees, interest or penalties added.';
        }

        $this->validate($rules, $messages);

        // An expedite fee goes through only with its terms ticked.
        if (($this->rushFee($this->deadlineGate())['acknowledgement'] ?? null) !== null && ! $this->rushAcknowledged) {
            $this->addError('rushAcknowledged', 'Tick the box to accept the expedite terms.');

            return;
        }

        $this->updateFiling();

        // Create recipient records from parties
        $this->createRecipients();

        // Snapshot data, and the submit-by date the board and rush fees read
        $gate = $this->deadlineGate();
        $rushFee = $this->rushFee($gate);
        $acknowledgement = $this->rushAcknowledged ? ($rushFee['acknowledgement'] ?? null) : null;
        $rushElected = $rushFee === null && $this->rushElected && $this->rushUpsell($gate) !== null;

        $this->filing->update([
            'payload_json' => $this->buildPayloadSnapshot(),
            'parties_snapshot_json' => $this->buildPartiesSnapshot(),
            'must_submit_by' => isset($gate['mustSubmitBy']) ? $gate['mustSubmitBy']->toDateString() : null,
            'deadline_business_days_left' => $gate['businessDaysLeft'] ?? null,
            'rush_tier' => $rushFee['tier'] ?? ($rushElected ? RushTier::RUSH : null),
            'rush_elected' => $rushElected,
            'rush_acknowledged_at' => $acknowledgement !== null ? now() : null,
            'claim_attested_at' => $this->amountMode() === 'claim' ? now() : null,
        ]);

        if ($acknowledgement !== null) {
            $this->filing->events()->create([
                'business_id' => $this->filing->business_id,
                'event_type' => LienFilingEvent::RUSH_ACKNOWLEDGED,
                'payload_json' => [
                    'tier' => $rushFee['tier'],
                    'text' => $acknowledgement,
                    'fee_cents' => $rushFee['price']->amount_cents,
                    'business_days_left' => $rushFee['businessDaysLeft'],
                    'must_submit_by' => $gate['mustSubmitBy']->toDateString(),
                ],
                'created_by' => auth()->id(),
            ]);
        }

        // Transition to awaiting payment
        if ($this->filing->status === FilingStatus::Draft) {
            $this->filing->transitionTo(FilingStatus::AwaitingPayment);
        }

        $this->redirect(route('lien.filings.checkout', $this->filing));
    }

    private function createRecipients(): void
    {
        // Clear existing recipients
        $this->filing->recipients()->delete();

        // Add owner as recipient
        $owner = $this->project->ownerParty();
        if ($owner) {
            $this->filing->recipients()->create(
                LienFilingRecipient::fromParty($owner)
            );
        }

        // Add customer if different from owner
        $customer = $this->project->parties()->where('role', 'customer')->first();
        if ($customer && $customer->id !== $owner?->id) {
            $this->filing->recipients()->create(
                LienFilingRecipient::fromParty($customer)
            );
        }
    }

    private function buildPayloadSnapshot(): array
    {
        return [
            'project' => [
                'name' => $this->project->name,
                'jobsite_address' => $this->project->jobsiteAddressLine(),
                'legal_description' => $this->legal_description,
                'apn' => $this->apn,
            ],
            'property_details' => [
                'is_public_project' => $this->project_type_category === 'government' ? 'yes' : 'no',
                'project_type_category' => $this->project_type_category,
                'has_legal_description' => $this->has_legal_description,
                'has_apn' => $this->has_apn,
                'multiple_parcels' => $this->multiple_parcels,
                'owner_is_tenant' => $this->owner_is_tenant,
            ],
            'filing' => [
                'document_type' => $this->deadline->documentType->name,
                'amount_claimed' => $this->amount_claimed,
                'description_of_work' => $this->description_of_work,
                'service_level' => $this->service_level,
            ],
            'amount_breakdown' => [
                'has_written_contract' => $this->has_written_contract,
                'base_contract_amount' => $this->base_contract_amount,
                'change_orders' => $this->change_orders,
                'credits_deductions' => $this->credits_deductions,
                'payments_received' => $this->payments_received,
                'uncompleted_work' => $this->uncompleted_work,
                'claim_difference_reason' => $this->project->claim_difference_reason,
            ],
            'dates' => [
                'first_furnish' => $this->project->first_furnish_date?->toDateString(),
                'last_furnish' => $this->project->last_furnish_date?->toDateString(),
                'completion' => $this->project->completion_date?->toDateString(),
            ],
        ];
    }

    private function buildPartiesSnapshot(): array
    {
        return $this->project->parties->map(fn ($p) => $p->toSnapshot())->toArray();
    }

    /**
     * Open the party modal for adding or editing a party.
     */
    public function openPartyModal(?int $partyId = null, ?string $defaultRole = null): void
    {
        $this->resetPartyForm();

        if ($partyId) {
            $party = $this->project->parties()->findOrFail($partyId);
            $this->editingPartyId = $party->id;
            $this->partyRole = $party->role->value;
            $this->partyName = $party->name;
            $this->partyCompanyName = $party->company_name;
            $this->partyAddress1 = $party->address1;
            $this->partyAddress2 = $party->address2;
            $this->partyCity = $party->city;
            $this->partyState = $party->state;
            $this->partyZip = $party->zip;
            $this->partyEmail = $party->email;
            $this->partyPhone = $party->phone;
            $this->partySameAsJobsiteConfirmed = (bool) $party->address_same_as_jobsite_confirmed;
            $this->partyAddressConfirmed = (bool) $party->address_confirmed_by_customer;
        } elseif ($defaultRole) {
            $this->partyRole = $defaultRole;
        }

        $this->showPartyModal = true;
    }

    /**
     * Close the party modal.
     */
    public function closePartyModal(): void
    {
        $this->showPartyModal = false;
        $this->resetPartyForm();
    }

    /**
     * Save a party (create or update). A ZIP code that seems to be in another
     * state needs the "This address is correct as typed" tick, and a general
     * contractor, subcontractor or customer at the jobsite address needs the
     * "This is really their mailing address" tick.
     */
    public function saveParty(): void
    {
        $this->validate([
            'partyRole' => ['required', Rule::enum(PartyRole::class)],
            'partyName' => ['required', 'string', 'max:255'],
            'partyCompanyName' => ['nullable', 'string', 'max:255'],
            'partyAddress1' => ['required', 'string', 'max:255'],
            'partyAddress2' => ['nullable', 'string', 'max:255'],
            'partyCity' => ['required', 'string', 'max:255'],
            'partyState' => ['required', 'string', 'max:2'],
            'partyZip' => ['required', 'string', 'max:10'],
            'partyEmail' => ['nullable', 'email', 'max:255'],
            'partyPhone' => ['nullable', 'string', 'max:50'],
        ], [], [
            'partyName' => 'name',
            'partyAddress1' => 'street address',
            'partyCity' => 'city',
            'partyState' => 'state',
            'partyZip' => 'ZIP code',
        ]);

        $zipMismatch = ZipStateRanges::mismatchMessage($this->partyZip, $this->partyState) !== null;
        $atJobsite = $this->partyAddressIsJobsite();
        $unconfirmed = [];

        if ($zipMismatch && ! $this->partyAddressConfirmed) {
            $unconfirmed['partyAddressConfirmed'] = 'Fix the ZIP code or the state. If the address is right, tick the box.';
        }

        if ($atJobsite && ! $this->partySameAsJobsiteConfirmed) {
            $unconfirmed['partySameAsJobsiteConfirmed'] = 'This is the jobsite address. Enter their mailing address. If this really is their mailing address, tick the box.';
        }

        if ($unconfirmed !== []) {
            throw ValidationException::withMessages($unconfirmed);
        }

        $data = [
            'role' => $this->partyRole,
            'name' => $this->partyName,
            'company_name' => $this->partyCompanyName,
            'address1' => $this->partyAddress1,
            'address2' => $this->partyAddress2,
            'city' => $this->partyCity,
            'state' => $this->partyState ? strtoupper($this->partyState) : null,
            'zip' => $this->partyZip,
            'address_same_as_jobsite_confirmed' => $atJobsite,
            'address_confirmed_by_customer' => $zipMismatch ? true : null,
            'email' => $this->partyEmail,
            'phone' => $this->partyPhone,
        ];

        if ($this->editingPartyId) {
            $party = $this->project->parties()->findOrFail($this->editingPartyId);
            $party->update($data);
            // The Parties step's gaps for this party are rechecked on Continue.
            $this->resetValidation('party_'.$party->id);
        } else {
            $data['business_id'] = $this->project->business_id;
            $data['project_id'] = $this->project->id;
            LienParty::create($data);
        }

        $this->project->refresh();
        $this->closePartyModal();
    }

    /**
     * Whether the modal holds a general contractor, subcontractor or customer
     * whose address is the jobsite address.
     */
    private function partyAddressIsJobsite(): bool
    {
        return $this->project->mailingAddressMustDifferFromJobsite(PartyRole::tryFrom($this->partyRole))
            && PartyAddress::sameAsJobsite($this->project, $this->partyAddress1, $this->partyCity, $this->partyZip);
    }

    /**
     * Google Places pick for the party modal's street-address input
     * (see livewire.lien._places-autocomplete).
     *
     * @param  array<string, mixed>  $addressData
     */
    public function updatePartyAddressFromAutocomplete(array $addressData): void
    {
        $this->partyAddress1 = $addressData['line1'] ?? null;
        $this->partyCity = $addressData['city'] ?? null;
        $this->partyState = $addressData['state'] ?? null;
        $this->partyZip = $addressData['zip'] ?? null;
    }

    /**
     * Delete a party.
     */
    public function deleteParty(int $partyId): void
    {
        $party = $this->project->parties()->findOrFail($partyId);
        $party->delete();
        $this->project->refresh();
    }

    /**
     * Reset the party form fields.
     */
    private function resetPartyForm(): void
    {
        $this->editingPartyId = null;
        $this->partyRole = 'owner';
        $this->partyName = '';
        $this->partyCompanyName = null;
        $this->partyAddress1 = null;
        $this->partyAddress2 = null;
        $this->partyCity = null;
        $this->partyState = null;
        $this->partyZip = null;
        $this->partyEmail = null;
        $this->partyPhone = null;
        $this->partySameAsJobsiteConfirmed = false;
        $this->partyAddressConfirmed = false;
        $this->ownerAddressSource = null;
        $this->resetValidation([
            'partyRole', 'partyName', 'partyCompanyName', 'partyAddress1',
            'partyAddress2', 'partyCity', 'partyState', 'partyZip',
            'partyEmail', 'partyPhone', 'partySameAsJobsiteConfirmed',
            'partyAddressConfirmed',
        ]);
    }

    /**
     * Populate party address fields with the jobsite address.
     */
    public function useJobsiteAddress(): void
    {
        $this->partyAddress1 = $this->project->jobsite_address1;
        $this->partyAddress2 = $this->project->jobsite_address2;
        $this->partyCity = $this->project->jobsite_city;
        $this->partyState = $this->project->jobsite_state;
        $this->partyZip = $this->project->jobsite_zip;
        $this->ownerAddressSource = 'copied_from_jobsite';
    }

    /**
     * Enable manual editing of the owner address.
     */
    public function enableManualAddressEdit(): void
    {
        $this->ownerAddressSource = 'manual';
    }

    /**
     * Get the claimant info from the business profile.
     */
    public function getClaimantInfoProperty(): array
    {
        $business = Auth::user()->currentBusiness();
        if (! $business) {
            return [];
        }

        $address = $business->business_address ?? [];

        return [
            'company_name' => $business->legal_name ?? $business->name ?? '',
            'address' => implode(', ', array_filter([
                $address['line1'] ?? null,
                $address['city'] ?? null,
                $address['state'] ?? null,
                $address['zip'] ?? null,
            ])),
            'phone' => $business->phone ?? '',
        ];
    }

    /**
     * The balance due for display ("$7,200.00", "-$300.00"), or null until
     * the contract amount is entered.
     */
    public function getCalculatedBalanceDueProperty(): ?string
    {
        $balance = $this->balanceDueCents();

        if ($balance === null) {
            return null;
        }

        return ($balance < 0 ? '-$' : '$').number_format(abs($balance) / 100, 2);
    }

    /**
     * Whether the amount claimed is more than a cent away from the balance
     * due, so the customer must say why. Liens and notices of intent only.
     */
    public function getClaimDiffersFromBalanceProperty(): bool
    {
        if ($this->amountMode() !== 'claim') {
            return false;
        }

        $balance = $this->balanceDueCents();
        $claim = $this->toCents($this->amount_claimed);

        return $balance !== null && $claim !== null && abs($claim - $balance) > 1;
    }

    /**
     * Check whether this filing requires an attorney referral (states where
     * mechanics liens must be filed directly with the court, not via eRegister).
     */
    public function getRequiresAttorneyReferralProperty(): bool
    {
        $referralStates = config('lien.attorney_referral_states', []);

        return in_array($this->project->jobsite_state, $referralStates, true)
            && $this->deadline->documentType->slug === 'mechanics_lien';
    }

    /**
     * Get the human-readable state name for the attorney referral state.
     */
    public function getReferralStateNameProperty(): string
    {
        return $this->stateName();
    }

    /**
     * The jobsite state's name ("Colorado"), or its code when unknown.
     */
    private function stateName(): string
    {
        $states = config('states', []);

        return $states[$this->project->jobsite_state] ?? (string) $this->project->jobsite_state;
    }

    /**
     * Send a referral request email to connect the user with an attorney
     * licensed in the project's jobsite state.
     */
    public function requestAttorneyReferral(): void
    {
        if ($this->referralSent) {
            return;
        }

        $user = Auth::user();
        $business = $user->currentBusiness();

        Mail::to('contact@eregister.com')->send(new AttorneyReferral(
            firstName: $user->first_name,
            lastName: $user->last_name,
            businessName: $business?->name ?? $business?->legal_name ?? 'N/A',
            userEmail: $user->email,
            stateName: $this->referralStateName,
            stateCode: $this->project->jobsite_state,
            phone: $business?->phone,
        ));

        $this->referralSent = true;
    }

    public function render(): View
    {
        $slug = $this->deadline->documentType->slug;
        $state = strtoupper((string) $this->project->jobsite_state);
        $pricing = array_replace(
            config('lien.pricing.'.$slug, [
                'self_serve' => 4900,
                'full_service' => 9900,
            ]),
            config("lien.state_pricing.{$state}.{$slug}", []),
        );

        $noiRequirement = $this->noiRequirement();
        $deadlineGate = $this->step === 4 && ! $this->requiresAttorneyReferral ? $this->deadlineGate() : null;
        $deadlineBlocked = $deadlineGate['passed'] ?? false;

        // The first time the customer is told the deadline has passed; date
        // changes after it are logged (logDatesChangedAfterDeadlineBlock).
        if ($deadlineBlocked && $this->filing->deadline_block_shown_at === null) {
            $this->filing->update(['deadline_block_shown_at' => now()]);
        }

        return view('livewire.lien.filing-wizard', [
            'parties' => $this->project->parties,
            'documentType' => $this->deadline->documentType,
            'pricing' => $pricing,
            'serviceLevels' => ServiceLevel::cases(),
            'claimantInfo' => $this->claimantInfo,
            'partyRoles' => collect(PartyRole::cases())->filter(fn ($role) => $role !== PartyRole::Claimant)->values(),
            'hiringPartyRole' => $this->step === 2 ? $this->project->hiringPartyRole() : null,
            'partyAtJobsite' => $this->showPartyModal && $this->partyAddressIsJobsite(),
            'partyZipWarning' => $this->showPartyModal ? ZipStateRanges::mismatchMessage($this->partyZip, $this->partyState) : null,
            'requiredDateFields' => $this->requiredDateFields,
            'isLienRelease' => $this->isLienRelease(),
            'amountMode' => $this->amountMode(),
            'recordedLien' => $this->step === 3 && $this->isLienRelease() ? $this->recordedLien() : null,
            'stateName' => $this->stateName(),
            'noiRequirement' => $noiRequirement,
            'noiServedTo' => self::NOI_SERVED_TO,
            'noiOrderUrl' => $noiRequirement !== null && in_array($this->step, [3, 4], true) ? $this->noiOrderUrl() : null,
            'noiBlock' => $this->step === 4 ? $this->noiBlock() : null,
            'deadlineGate' => $deadlineGate,
            'deadlineBlocked' => $deadlineBlocked,
            'rushFee' => $deadlineBlocked ? null : $this->rushFee($deadlineGate),
            'rushUpsell' => $this->step === 4 && ! $this->requiresAttorneyReferral && ! $deadlineBlocked ? $this->rushUpsell($deadlineGate) : null,
            'demandLetter' => $deadlineBlocked ? $this->demandLetterOffer() : null,
            'propertyRequired' => $this->requiresPropertyIdentification(),
            'propertyLookup' => $this->step === 1 ? PropertyLookup::forFiling($this->filing) : null,
        ])->layout('components.layouts.portal', ['title' => 'Create '.$this->deadline->documentType->name]);
    }
}

<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class FilingWizard extends Component
{
    use WithFileUploads;

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

    public ?string $multiple_parcels = null;

    public bool $owner_is_tenant = false;

    // Step 4: Amount & Contract
    public ?float $amount_claimed = null;

    public ?string $description_of_work = null;

    // Amount breakdown (stored in payload_json)
    public ?string $has_written_contract = null;

    public ?string $base_contract_amount = null;

    public ?string $change_orders = null;

    public ?string $credits_deductions = null;

    public ?string $payments_received = null;

    public ?string $uncompleted_work = null;

    // Step 5: Service choice
    public string $service_level = 'full_service';

    // Step 6: Review
    public bool $disclaimerAccepted = false;

    public array $warnings = [];

    // File uploads
    public array $attachments = [];

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

    // Track owner address source: copied_from_jobsite | manual | researched
    public ?string $ownerAddressSource = null;

    public function mount(LienProject $project, LienProjectDeadline $deadline): void
    {
        Gate::authorize('view', $project);

        $this->project = $project;
        $this->deadline = $deadline;

        // Find or create draft filing
        $this->filing = $project->filings()
            ->where('project_deadline_id', $deadline->id)
            ->whereIn('status', [FilingStatus::Draft, FilingStatus::AwaitingPayment])
            ->first();

        if (! $this->filing) {
            $this->filing = LienFiling::create([
                'public_id' => Str::ulid()->toBase32(),
                'business_id' => $project->business_id,
                'project_id' => $project->id,
                'document_type_id' => $deadline->document_type_id,
                'project_deadline_id' => $deadline->id,
                'jurisdiction_state' => $project->jobsite_state,
                'jurisdiction_county' => $project->jobsite_county,
                'status' => FilingStatus::Draft,
                'created_by_user_id' => auth()->id(),
            ]);
        }

        $this->populateFromProject();
        $this->populateFromFiling();
    }

    private function populateFromProject(): void
    {
        // Populate saved values from the project
        $this->project_type_category = $this->project->project_type;
        $this->legal_description = $this->project->legal_description;
        $this->apn = $this->project->apn;
        $this->owner_is_tenant = (bool) $this->project->owner_is_tenant;

        // Set yes/no toggles based on whether values exist
        if ($this->legal_description) {
            $this->has_legal_description = 'yes';
        }
        if ($this->apn) {
            $this->has_apn = 'yes';
        }
    }

    private function populateFromFiling(): void
    {
        // Populate from existing filing
        if ($this->filing->amount_claimed_cents) {
            $this->amount_claimed = $this->filing->amount_claimed_cents / 100;
        } elseif ($this->project->hasFinancialData()) {
            $balanceDueCents = $this->project->balanceDueCents();
            $this->amount_claimed = $balanceDueCents !== null ? $balanceDueCents / 100 : null;
        }

        $this->description_of_work = $this->filing->description_of_work;
        $this->service_level = $this->filing->service_level?->value ?? 'full_service';

        // Load any existing payload data
        $payload = $this->filing->payload_json ?? [];
        if (isset($payload['property_details'])) {
            $this->is_public_project = $payload['property_details']['is_public_project'] ?? 'unknown';
            $this->has_legal_description = $payload['property_details']['has_legal_description'] ?? null;
            $this->has_apn = $payload['property_details']['has_apn'] ?? null;
            $this->multiple_parcels = $payload['property_details']['multiple_parcels'] ?? null;
        }
        if (isset($payload['amount_breakdown'])) {
            $breakdown = $payload['amount_breakdown'];
            $this->has_written_contract = $breakdown['has_written_contract'] ?? null;
            $this->base_contract_amount = $breakdown['base_contract_amount'] ?? null;
            $this->change_orders = $breakdown['change_orders'] ?? null;
            $this->credits_deductions = $breakdown['credits_deductions'] ?? null;
            $this->payments_received = $breakdown['payments_received'] ?? null;
            $this->uncompleted_work = $breakdown['uncompleted_work'] ?? null;
        }
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->updateFiling();

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
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

    private function validateStep(): void
    {
        match ($this->step) {
            // Step 1: Property - validate property type
            1 => $this->validate([
                'project_type_category' => ['required', 'in:residential,commercial,government'],
            ], [
                'project_type_category.required' => 'Please select a property type.',
            ]),
            // Step 2: Parties - property owner is required
            2 => $this->validateOwnerRequired(),
            // Step 3: Amount & Contract
            3 => $this->validate([
                'has_written_contract' => ['required', 'in:0,1'],
                'amount_claimed' => ['required', 'numeric', 'min:0.01'],
                'description_of_work' => ['required', 'string', 'min:10'],
            ], [
                'has_written_contract.required' => 'Please indicate whether the work was done pursuant to a written contract.',
            ]),
            // Step 4: Checkout (service + review combined) - no nextStep validation needed
            // Validation happens in proceedToCheckout()
            default => null,
        };
    }

    /**
     * Validate that a property owner has been added.
     */
    private function validateOwnerRequired(): void
    {
        if (! $this->project->ownerParty()) {
            $this->addError('owner_required', 'Property owner is required to continue.');

            throw new \Illuminate\Validation\ValidationException(validator([], []));
        }
    }

    /**
     * Validate the review step before checkout.
     * Collects errors via addError() and warnings via $this->warnings.
     */
    private function validateReviewStep(): void
    {
        $this->warnings = [];

        $docType = $this->deadline->documentType->slug;
        $owner = $this->project->ownerParty();

        // Mechanics Lien + Self-Serve: require property identification and owner
        if ($docType === 'mechanics_lien' && $this->service_level === 'self_serve') {
            // Property identification check - require legal_description OR apn
            if (empty($this->legal_description) && empty($this->apn)) {
                $this->addError('property_identification',
                    'Legal description or APN required for Self-Serve mechanics lien. Go back and add one, or switch to Full Service.');
            }

            // Owner party check
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
    }

    private function updateFiling(): void
    {
        // Update property details on the project
        $this->project->update([
            'legal_description' => $this->has_legal_description === 'yes' ? $this->legal_description : null,
            'apn' => $this->has_apn === 'yes' ? $this->apn : null,
            'owner_is_tenant' => $this->owner_is_tenant,
            'project_type' => $this->project_type_category,
        ]);

        // Update filing
        $this->filing->update([
            'amount_claimed_cents' => $this->amount_claimed ? (int) ($this->amount_claimed * 100) : null,
            'description_of_work' => $this->description_of_work,
            'service_level' => $this->service_level,
        ]);
    }

    public function proceedToCheckout(): void
    {
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

        $this->validate([
            'disclaimerAccepted' => ['accepted'],
        ], [
            'disclaimerAccepted.accepted' => 'You must accept the disclaimer to continue.',
        ]);

        $this->updateFiling();

        // Create recipient records from parties
        $this->createRecipients();

        // Snapshot data
        $this->filing->update([
            'payload_json' => $this->buildPayloadSnapshot(),
            'parties_snapshot_json' => $this->buildPartiesSnapshot(),
        ]);

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
            ],
            'dates' => [
                'first_furnish' => $this->project->first_furnish_date?->toDateString(),
                'last_furnish' => $this->project->last_furnish_date?->toDateString(),
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
    public function openPartyModal(?int $partyId = null): void
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
     * Save a party (create or update).
     */
    public function saveParty(): void
    {
        $this->validate([
            'partyRole' => ['required', Rule::enum(PartyRole::class)],
            'partyName' => ['required', 'string', 'max:255'],
            'partyCompanyName' => ['nullable', 'string', 'max:255'],
            'partyAddress1' => ['nullable', 'string', 'max:255'],
            'partyAddress2' => ['nullable', 'string', 'max:255'],
            'partyCity' => ['nullable', 'string', 'max:255'],
            'partyState' => ['nullable', 'string', 'max:2'],
            'partyZip' => ['nullable', 'string', 'max:10'],
            'partyEmail' => ['nullable', 'email', 'max:255'],
            'partyPhone' => ['nullable', 'string', 'max:50'],
        ]);

        $data = [
            'role' => $this->partyRole,
            'name' => $this->partyName,
            'company_name' => $this->partyCompanyName,
            'address1' => $this->partyAddress1,
            'address2' => $this->partyAddress2,
            'city' => $this->partyCity,
            'state' => $this->partyState ? strtoupper($this->partyState) : null,
            'zip' => $this->partyZip,
            'email' => $this->partyEmail,
            'phone' => $this->partyPhone,
        ];

        if ($this->editingPartyId) {
            $party = $this->project->parties()->findOrFail($this->editingPartyId);
            $party->update($data);
        } else {
            $data['business_id'] = $this->project->business_id;
            $data['project_id'] = $this->project->id;
            LienParty::create($data);
        }

        $this->project->refresh();
        $this->closePartyModal();
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
        $this->ownerAddressSource = null;
        $this->resetValidation([
            'partyRole', 'partyName', 'partyCompanyName', 'partyAddress1',
            'partyAddress2', 'partyCity', 'partyState', 'partyZip',
            'partyEmail', 'partyPhone',
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
     * Calculate the balance due for display.
     */
    public function getCalculatedBalanceDueProperty(): ?string
    {
        if ($this->base_contract_amount === null || $this->base_contract_amount === '') {
            return null;
        }

        $base = (float) ($this->base_contract_amount ?? 0);
        $changes = (float) ($this->change_orders ?? 0);
        $credits = (float) ($this->credits_deductions ?? 0);
        $payments = (float) ($this->payments_received ?? 0);
        $uncompleted = (float) ($this->uncompleted_work ?? 0);

        $balance = $base + $changes - $credits - $payments - $uncompleted;

        return number_format($balance, 2);
    }

    public function render(): View
    {
        $pricing = config('lien.pricing.'.$this->deadline->documentType->slug, [
            'self_serve' => 4900,
            'full_service' => 9900,
        ]);

        return view('livewire.lien.filing-wizard', [
            'parties' => $this->project->parties,
            'documentType' => $this->deadline->documentType,
            'pricing' => $pricing,
            'serviceLevels' => ServiceLevel::cases(),
            'claimantInfo' => $this->claimantInfo,
            'partyRoles' => collect(PartyRole::cases())->filter(fn ($role) => $role !== PartyRole::Claimant)->values(),
        ])->layout('layouts.lien', ['title' => 'Create '.$this->deadline->documentType->name]);
    }
}

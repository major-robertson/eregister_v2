<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Services\GooglePlacesService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProjectForm extends Component
{
    public ?LienProject $project = null;

    public bool $isEditing = false;

    // Wizard state
    public int $step = 1;

    public int $totalSteps = 6;

    // Step 1: Project Info
    public string $name = '';

    public ?string $job_number = null;

    public string $claimant_type = 'subcontractor';

    // Step 2: Jobsite Address
    public ?string $jobsite_address1 = null;

    public ?string $jobsite_address2 = null;

    public ?string $jobsite_city = null;

    public string $jobsite_state = '';

    public ?string $jobsite_zip = null;

    public ?string $jobsite_county = null;

    // Geo fields from Google Places
    public ?string $jobsite_county_google = null;

    public ?string $jobsite_place_id = null;

    public ?string $jobsite_formatted_address = null;

    public ?float $jobsite_lat = null;

    public ?float $jobsite_lng = null;

    // Step 3: Property Details
    public ?string $legal_description = null;

    public ?string $apn = null;

    public ?string $project_type = null;

    public bool $owner_is_tenant = false;

    // Step 4: Parties (managed separately - we'll show a summary)
    // Claimant party data (pre-populated from business)
    public string $claimant_company_name = '';

    public string $claimant_name = '';

    public ?string $claimant_address1 = null;

    public ?string $claimant_city = null;

    public ?string $claimant_state = null;

    public ?string $claimant_zip = null;

    public ?string $claimant_phone = null;

    public ?string $claimant_email = null;

    // Step 5: Contract Details
    public ?string $has_written_contract = null;

    public ?string $base_contract_amount = null;

    public ?string $change_orders = null;

    public ?string $credits_deductions = null;

    public ?string $payments_received = null;

    public ?string $uncompleted_work = null;

    // Step 6: Important Dates
    public ?string $contract_date = null;

    public ?string $first_furnish_date = null;

    public ?string $last_furnish_date = null;

    public ?string $completion_date = null;

    public ?string $noc_recorded_date = null;

    public function mount(?LienProject $project = null): void
    {
        if ($project && $project->exists) {
            Gate::authorize('update', $project);
            $this->project = $project;
            $this->isEditing = true;
            $this->fillFromProject();
        } else {
            // Pre-populate claimant from business profile
            $this->prefillClaimantFromBusiness();
        }
    }

    private function prefillClaimantFromBusiness(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            return;
        }

        $this->claimant_company_name = $business->legal_name ?? $business->name ?? '';
        $this->claimant_phone = $business->phone ?? null;

        $address = $business->business_address ?? [];
        $this->claimant_address1 = $address['line1'] ?? null;
        $this->claimant_city = $address['city'] ?? null;
        $this->claimant_state = $address['state'] ?? null;
        $this->claimant_zip = $address['zip'] ?? null;

        // Get signer name from responsible_people
        $user = Auth::user();
        $responsiblePerson = $business->getResponsiblePersonForUser($user->id);

        if ($responsiblePerson) {
            $this->claimant_name = $responsiblePerson['name'] ?? $user->name ?? '';
        } else {
            $this->claimant_name = $user->name ?? '';
        }

        $this->claimant_email = $user->email ?? null;
    }

    private function fillFromProject(): void
    {
        // Step 1
        $this->name = $this->project->name;
        $this->job_number = $this->project->job_number;
        $this->claimant_type = $this->project->claimant_type?->value ?? 'subcontractor';

        // Step 2
        $this->jobsite_address1 = $this->project->jobsite_address1;
        $this->jobsite_address2 = $this->project->jobsite_address2;
        $this->jobsite_city = $this->project->jobsite_city;
        $this->jobsite_state = $this->project->jobsite_state ?? '';
        $this->jobsite_zip = $this->project->jobsite_zip;
        $this->jobsite_county = $this->project->jobsite_county;
        $this->jobsite_county_google = $this->project->jobsite_county_google;
        $this->jobsite_place_id = $this->project->jobsite_place_id;
        $this->jobsite_formatted_address = $this->project->jobsite_formatted_address;
        $this->jobsite_lat = $this->project->jobsite_lat;
        $this->jobsite_lng = $this->project->jobsite_lng;

        // Step 3
        $this->legal_description = $this->project->legal_description;
        $this->apn = $this->project->apn;
        $this->project_type = $this->project->project_type;
        $this->owner_is_tenant = (bool) $this->project->owner_is_tenant;

        // Step 4: Load claimant party
        $claimantParty = $this->project->claimantParty();
        if ($claimantParty) {
            $this->claimant_company_name = $claimantParty->company_name ?? '';
            $this->claimant_name = $claimantParty->name ?? '';
            $this->claimant_address1 = $claimantParty->address1;
            $this->claimant_city = $claimantParty->city;
            $this->claimant_state = $claimantParty->state;
            $this->claimant_zip = $claimantParty->zip;
            $this->claimant_phone = $claimantParty->phone;
            $this->claimant_email = $claimantParty->email;
        } else {
            $this->prefillClaimantFromBusiness();
        }

        // Step 5
        $this->has_written_contract = $this->project->has_written_contract !== null
            ? ($this->project->has_written_contract ? '1' : '0')
            : null;
        $this->base_contract_amount = $this->project->base_contract_amount_cents !== null
            ? number_format($this->project->base_contract_amount_cents / 100, 2, '.', '')
            : null;
        $this->change_orders = $this->project->change_orders_cents !== null
            ? number_format($this->project->change_orders_cents / 100, 2, '.', '')
            : null;
        $this->credits_deductions = $this->project->credits_deductions_cents !== null
            ? number_format($this->project->credits_deductions_cents / 100, 2, '.', '')
            : null;
        $this->payments_received = $this->project->payments_received_cents !== null
            ? number_format($this->project->payments_received_cents / 100, 2, '.', '')
            : null;
        $this->uncompleted_work = $this->project->uncompleted_work_cents !== null
            ? number_format($this->project->uncompleted_work_cents / 100, 2, '.', '')
            : null;

        // Step 6
        $this->contract_date = $this->project->contract_date?->format('Y-m-d');
        $this->first_furnish_date = $this->project->first_furnish_date?->format('Y-m-d');
        $this->last_furnish_date = $this->project->last_furnish_date?->format('Y-m-d');
        $this->completion_date = $this->project->completion_date?->format('Y-m-d');
        $this->noc_recorded_date = $this->project->noc_recorded_date?->format('Y-m-d');
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->saveProgress();

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
        // Only allow going back to completed steps
        if ($step < $this->step && $step >= 1) {
            $this->step = $step;
        }
    }

    protected function validateStep(): void
    {
        $rules = $this->getStepRules();

        if (! empty($rules)) {
            $this->validate($rules);
        }
    }

    protected function getStepRules(): array
    {
        return match ($this->step) {
            1 => [
                'name' => ['required', 'string', 'max:255'],
                'job_number' => ['nullable', 'string', 'max:100'],
                'claimant_type' => ['required', Rule::enum(ClaimantType::class)],
            ],
            2 => [
                'jobsite_address1' => ['nullable', 'string', 'max:255'],
                'jobsite_address2' => ['nullable', 'string', 'max:255'],
                'jobsite_city' => ['nullable', 'string', 'max:255'],
                'jobsite_state' => ['required', 'string', 'size:2'],
                'jobsite_zip' => ['nullable', 'string', 'max:10'],
                'jobsite_county' => ['nullable', 'string', 'max:255'],
            ],
            3 => [
                'legal_description' => ['nullable', 'string'],
                'apn' => ['nullable', 'string', 'max:100'],
                'project_type' => ['nullable', 'string', 'max:50'],
                'owner_is_tenant' => ['boolean'],
            ],
            4 => [
                'claimant_company_name' => ['required', 'string', 'max:255'],
                'claimant_name' => ['nullable', 'string', 'max:255'],
                'claimant_address1' => ['nullable', 'string', 'max:255'],
                'claimant_city' => ['nullable', 'string', 'max:255'],
                'claimant_state' => ['nullable', 'string', 'size:2'],
                'claimant_zip' => ['nullable', 'string', 'max:10'],
                'claimant_phone' => ['nullable', 'string', 'max:20'],
                'claimant_email' => ['nullable', 'email', 'max:255'],
            ],
            5 => [
                'has_written_contract' => ['nullable', 'in:0,1'],
                'base_contract_amount' => ['nullable', 'numeric', 'min:0'],
                'change_orders' => ['nullable', 'numeric'],
                'credits_deductions' => ['nullable', 'numeric', 'min:0'],
                'payments_received' => ['nullable', 'numeric', 'min:0'],
                'uncompleted_work' => ['nullable', 'numeric', 'min:0'],
            ],
            6 => [
                'contract_date' => ['nullable', 'date'],
                'first_furnish_date' => ['nullable', 'date'],
                'last_furnish_date' => ['nullable', 'date'],
                'completion_date' => ['nullable', 'date'],
                'noc_recorded_date' => ['nullable', 'date'],
            ],
            default => [],
        };
    }

    /**
     * Update address fields from Google Maps autocomplete.
     */
    public function updateAddressFromAutocomplete(array $addressData): void
    {
        $this->jobsite_address1 = $addressData['line1'] ?? null;
        $this->jobsite_city = $addressData['city'] ?? null;
        $this->jobsite_state = $addressData['state'] ?? '';
        $this->jobsite_zip = $addressData['zip'] ?? null;

        // Store Google's county in both fields - user can edit jobsite_county
        $googleCounty = $addressData['county'] ?? null;
        $this->jobsite_county = $googleCounty;
        $this->jobsite_county_google = $googleCounty;

        $this->jobsite_place_id = $addressData['place_id'] ?? null;
        $this->jobsite_formatted_address = $addressData['formatted_address'] ?? null;
        $this->jobsite_lat = $addressData['lat'] ?? null;
        $this->jobsite_lng = $addressData['lng'] ?? null;
    }

    public function save(): void
    {
        $this->validateStep();
        $this->saveProgress();

        // Mark wizard as complete
        $this->project->markWizardComplete();

        session()->flash('message', 'Project saved successfully.');

        $this->redirect(route('lien.projects.show', $this->project));
    }

    protected function saveClaimantParty(LienProject $project, int $businessId): void
    {
        $claimantData = [
            'business_id' => $businessId,
            'project_id' => $project->id,
            'role' => PartyRole::Claimant->value,
            'company_name' => $this->claimant_company_name,
            'name' => $this->claimant_name,
            'address1' => $this->claimant_address1,
            'city' => $this->claimant_city,
            'state' => $this->claimant_state,
            'zip' => $this->claimant_zip,
            'phone' => $this->claimant_phone,
            'email' => $this->claimant_email,
        ];

        $existingClaimant = $project->claimantParty();

        if ($existingClaimant) {
            $existingClaimant->update($claimantData);
        } else {
            LienParty::create($claimantData);
        }
    }

    protected function dollarsToCents(?string $dollars): ?int
    {
        if ($dollars === null || $dollars === '') {
            return null;
        }

        return (int) round((float) $dollars * 100);
    }

    /**
     * Geocode the address using Google Places API.
     * Used as fallback when user manually enters address without autocomplete.
     */
    protected function geocodeAddress(): void
    {
        $address = implode(', ', array_filter([
            $this->jobsite_address1,
            $this->jobsite_city,
            $this->jobsite_state,
            $this->jobsite_zip,
        ]));

        if (empty($address)) {
            return;
        }

        $googlePlaces = app(GooglePlacesService::class);
        $geoData = $googlePlaces->geocodeAddress($address);

        if ($geoData) {
            $this->jobsite_place_id = $geoData['place_id'];
            $this->jobsite_formatted_address = $geoData['formatted_address'];
            $this->jobsite_lat = $geoData['lat'];
            $this->jobsite_lng = $geoData['lng'];

            // Always store Google's county for reference
            if (! empty($geoData['county'])) {
                $this->jobsite_county_google = $geoData['county'];

                // Only update user-facing county if not already set
                if (empty($this->jobsite_county)) {
                    $this->jobsite_county = $geoData['county'];
                }
            }
        }
    }

    /**
     * Save the current progress (create or update project).
     * Called on each step and on final save.
     */
    protected function saveProgress(): void
    {
        // If no place_id (user didn't use autocomplete), try to geocode the address
        if (empty($this->jobsite_place_id) && ! empty($this->jobsite_address1)) {
            $this->geocodeAddress();
        }

        $business = Auth::user()->currentBusiness();

        $data = [
            'name' => $this->name,
            'job_number' => $this->job_number,
            'claimant_type' => $this->claimant_type,
            'jobsite_address1' => $this->jobsite_address1,
            'jobsite_address2' => $this->jobsite_address2,
            'jobsite_city' => $this->jobsite_city,
            'jobsite_state' => $this->jobsite_state ? strtoupper($this->jobsite_state) : null,
            'jobsite_zip' => $this->jobsite_zip,
            'jobsite_county' => $this->jobsite_county,
            'jobsite_county_google' => $this->jobsite_county_google,
            'jobsite_place_id' => $this->jobsite_place_id,
            'jobsite_formatted_address' => $this->jobsite_formatted_address,
            'jobsite_lat' => $this->jobsite_lat,
            'jobsite_lng' => $this->jobsite_lng,
            'legal_description' => $this->legal_description,
            'apn' => $this->apn,
            'project_type' => $this->project_type,
            'owner_is_tenant' => $this->owner_is_tenant,
            'has_written_contract' => $this->has_written_contract !== null ? (bool) $this->has_written_contract : null,
            'base_contract_amount_cents' => $this->dollarsToCents($this->base_contract_amount),
            'change_orders_cents' => $this->dollarsToCents($this->change_orders),
            'credits_deductions_cents' => $this->dollarsToCents($this->credits_deductions),
            'payments_received_cents' => $this->dollarsToCents($this->payments_received),
            'uncompleted_work_cents' => $this->dollarsToCents($this->uncompleted_work),
            'contract_date' => $this->contract_date ?: null,
            'first_furnish_date' => $this->first_furnish_date ?: null,
            'last_furnish_date' => $this->last_furnish_date ?: null,
            'completion_date' => $this->completion_date ?: null,
            'noc_recorded_date' => $this->noc_recorded_date ?: null,
        ];

        if ($this->project && $this->project->exists) {
            // Update existing project
            $this->project->update($data);
        } else {
            // Create new project
            $data['business_id'] = $business->id;
            $data['created_by_user_id'] = Auth::id();
            $this->project = LienProject::create($data);
            $this->isEditing = true;
        }

        // Save or update claimant party
        $this->saveClaimantParty($this->project, $business->id);

        // Recalculate deadlines
        app(DeadlineCalculator::class)->calculateForProject($this->project->fresh());
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
        return view('livewire.lien.project-form', [
            'claimantTypes' => ClaimantType::cases(),
            'states' => $this->getUsStates(),
            'projectTypes' => $this->getProjectTypes(),
            'stepTitles' => $this->getStepTitles(),
        ])->layout('layouts.lien', [
            'title' => $this->isEditing ? 'Edit Project' : 'Create Project',
        ]);
    }

    private function getStepTitles(): array
    {
        return [
            1 => 'Project Info',
            2 => 'Jobsite Address',
            3 => 'Property Details',
            4 => 'Claimant Info',
            5 => 'Contract Details',
            6 => 'Important Dates',
        ];
    }

    private function getProjectTypes(): array
    {
        return [
            'private' => 'Private',
            'public' => 'Public',
            'residential' => 'Residential',
            'commercial' => 'Commercial',
        ];
    }

    private function getUsStates(): array
    {
        return [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming',
        ];
    }
}

<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\NocStatus;
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

    public int $totalSteps = 3;

    // Step 1: Project Info
    public string $name = '';

    public ?string $job_number = null;

    public string $claimant_type = 'subcontractor';

    public string $property_context = 'unknown';

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

    // Step 3: Important Dates
    public ?string $first_furnish_date = null;

    public ?string $last_furnish_date = null;

    public string $noc_status = 'unknown';

    public ?string $noc_recorded_at = null;

    // States that show NOC question
    private const NOC_STATES = ['CA', 'NV', 'AZ', 'OR'];

    public function mount(?LienProject $project = null): void
    {
        if ($project && $project->exists) {
            Gate::authorize('update', $project);
            $this->project = $project;
            $this->isEditing = true;
            $this->fillFromProject();
        }
    }

    private function fillFromProject(): void
    {
        // Step 1
        $this->name = $this->project->name;
        $this->job_number = $this->project->job_number;
        $this->claimant_type = $this->project->claimant_type?->value ?? 'subcontractor';
        $this->property_context = $this->project->property_context ?? 'unknown';

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
        $this->first_furnish_date = $this->project->first_furnish_date?->format('Y-m-d');
        $this->last_furnish_date = $this->project->last_furnish_date?->format('Y-m-d');
        $this->noc_status = $this->project->noc_status?->value ?? 'unknown';
        $this->noc_recorded_at = $this->project->noc_recorded_at?->format('Y-m-d');
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

    /**
     * Skip step 3 (Important Dates) - saves without requiring dates.
     */
    public function skipStep(): void
    {
        if ($this->step === 3) {
            $this->saveProgress();
            $this->project->markWizardComplete();

            session()->flash('message', 'Project saved successfully.');
            $this->redirect(route('lien.projects.show', $this->project));
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
            3 => $this->getStep3Rules(),
            default => [],
        };
    }

    /**
     * Build step 3 validation rules properly (no empty strings in array).
     */
    private function getStep3Rules(): array
    {
        // Build last_furnish_date rules properly
        $lastFurnishRules = ['nullable', 'date', 'before_or_equal:today'];
        if ($this->first_furnish_date) {
            $lastFurnishRules[] = 'after_or_equal:first_furnish_date';
        }

        $rules = [
            'first_furnish_date' => ['nullable', 'date', 'before_or_equal:today'],
            'last_furnish_date' => $lastFurnishRules,
        ];

        // Only validate NOC fields for states that require it
        if ($this->showNocQuestion()) {
            $rules['noc_status'] = ['required', Rule::enum(NocStatus::class)];
            $rules['noc_recorded_at'] = [
                Rule::requiredIf($this->noc_status === 'yes'),
                'nullable',
                'date',
            ];
        }

        return $rules;
    }

    /**
     * Check if NOC question should be shown based on state.
     */
    public function showNocQuestion(): bool
    {
        return in_array(strtoupper($this->jobsite_state), self::NOC_STATES);
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
            'property_context' => $this->property_context,
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
            'first_furnish_date' => $this->first_furnish_date ?: null,
            'last_furnish_date' => $this->last_furnish_date ?: null,
            'noc_status' => $this->noc_status,
            'noc_recorded_at' => $this->noc_recorded_at ?: null,
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

        // Recalculate deadlines
        app(DeadlineCalculator::class)->calculateForProject($this->project->fresh());
    }

    public function render(): View
    {
        return view('livewire.lien.project-form', [
            'claimantTypes' => ClaimantType::cases(),
            'states' => $this->getUsStates(),
            'stepTitles' => $this->getStepTitles(),
            'nocStatuses' => NocStatus::cases(),
        ])->layout('layouts.lien', [
            'title' => $this->isEditing ? 'Edit Project' : 'Create Project',
        ]);
    }

    private function getStepTitles(): array
    {
        return [
            1 => 'Project Info',
            2 => 'Jobsite Address',
            3 => 'Important Dates',
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

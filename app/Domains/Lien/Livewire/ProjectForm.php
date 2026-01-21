<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
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

    // Form fields
    public string $name = '';

    public ?string $job_number = null;

    public string $claimant_type = 'subcontractor';

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

    public ?string $legal_description = null;

    public ?string $apn = null;

    public ?string $project_type = null;

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
        }
    }

    private function fillFromProject(): void
    {
        $this->name = $this->project->name;
        $this->job_number = $this->project->job_number;
        $this->claimant_type = $this->project->claimant_type?->value ?? 'subcontractor';
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
        $this->legal_description = $this->project->legal_description;
        $this->apn = $this->project->apn;
        $this->project_type = $this->project->project_type;
        $this->contract_date = $this->project->contract_date?->format('Y-m-d');
        $this->first_furnish_date = $this->project->first_furnish_date?->format('Y-m-d');
        $this->last_furnish_date = $this->project->last_furnish_date?->format('Y-m-d');
        $this->completion_date = $this->project->completion_date?->format('Y-m-d');
        $this->noc_recorded_date = $this->project->noc_recorded_date?->format('Y-m-d');
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

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'job_number' => ['nullable', 'string', 'max:100'],
            'claimant_type' => ['required', Rule::enum(ClaimantType::class)],
            'jobsite_address1' => ['nullable', 'string', 'max:255'],
            'jobsite_address2' => ['nullable', 'string', 'max:255'],
            'jobsite_city' => ['nullable', 'string', 'max:255'],
            'jobsite_state' => ['required', 'string', 'size:2'],
            'jobsite_zip' => ['nullable', 'string', 'max:10'],
            'jobsite_county' => ['nullable', 'string', 'max:255'],
            'legal_description' => ['nullable', 'string'],
            'apn' => ['nullable', 'string', 'max:100'],
            'project_type' => ['nullable', 'string', 'max:50'],
            'contract_date' => ['nullable', 'date'],
            'first_furnish_date' => ['nullable', 'date'],
            'last_furnish_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date'],
            'noc_recorded_date' => ['nullable', 'date'],
        ];
    }

    public function save(): void
    {
        $this->validate();

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
            'jobsite_state' => strtoupper($this->jobsite_state),
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
            'contract_date' => $this->contract_date ?: null,
            'first_furnish_date' => $this->first_furnish_date ?: null,
            'last_furnish_date' => $this->last_furnish_date ?: null,
            'completion_date' => $this->completion_date ?: null,
            'noc_recorded_date' => $this->noc_recorded_date ?: null,
        ];

        if ($this->isEditing) {
            $this->project->update($data);
            $project = $this->project;
        } else {
            $data['business_id'] = $business->id;
            $data['created_by_user_id'] = Auth::id();
            $project = LienProject::create($data);
        }

        // Recalculate deadlines
        app(DeadlineCalculator::class)->calculateForProject($project->fresh());

        session()->flash('message', $this->isEditing
            ? 'Project updated successfully.'
            : 'Project created successfully.');

        $this->redirect(route('lien.projects.show', $project));
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

    public function render(): View
    {
        return view('livewire.lien.project-form', [
            'claimantTypes' => ClaimantType::cases(),
            'states' => $this->getUsStates(),
        ])->layout('layouts.lien', [
            'title' => $this->isEditing ? 'Edit Project' : 'Create Project',
        ]);
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

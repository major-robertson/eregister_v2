<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Models\LienProject;
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
        $this->legal_description = $this->project->legal_description;
        $this->apn = $this->project->apn;
        $this->project_type = $this->project->project_type;
        $this->contract_date = $this->project->contract_date?->format('Y-m-d');
        $this->first_furnish_date = $this->project->first_furnish_date?->format('Y-m-d');
        $this->last_furnish_date = $this->project->last_furnish_date?->format('Y-m-d');
        $this->completion_date = $this->project->completion_date?->format('Y-m-d');
        $this->noc_recorded_date = $this->project->noc_recorded_date?->format('Y-m-d');
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

<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Lien\Models\LienProject;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class LienStats extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * The wizard fields organized by step for progress tracking.
     */
    protected array $wizardFields = [
        'Step 1: Project Info' => [
            'name' => 'Project Name',
            'job_number' => 'Job Number',
            'claimant_type' => 'Claimant Type',
        ],
        'Step 2: Jobsite' => [
            'jobsite_address1' => 'Address',
            'jobsite_city' => 'City',
            'jobsite_state' => 'State',
            'jobsite_zip' => 'ZIP',
            'jobsite_county' => 'County',
        ],
        'Step 3: Dates' => [
            'first_furnish_date' => 'First Furnish',
            'last_furnish_date' => 'Last Furnish',
            'completion_date' => 'Completion Date',
        ],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Calculate wizard progress for a project.
     *
     * @return array{filled: int, total: int, steps: array<string, array{filled: int, total: int, fields: array<string, bool>}>}
     */
    public function getWizardProgress(LienProject $project): array
    {
        $totalFilled = 0;
        $totalFields = 0;
        $steps = [];

        foreach ($this->wizardFields as $stepName => $fields) {
            $stepFilled = 0;
            $stepTotal = count($fields);
            $fieldStatus = [];

            foreach ($fields as $fieldKey => $fieldLabel) {
                $value = $project->getAttribute($fieldKey);
                $isFilled = $value !== null && $value !== '';
                $fieldStatus[$fieldLabel] = $isFilled;

                if ($isFilled) {
                    $stepFilled++;
                    $totalFilled++;
                }
            }

            $totalFields += $stepTotal;
            $steps[$stepName] = [
                'filled' => $stepFilled,
                'total' => $stepTotal,
                'fields' => $fieldStatus,
            ];
        }

        return [
            'filled' => $totalFilled,
            'total' => $totalFields,
            'steps' => $steps,
        ];
    }

    /**
     * Get filing summary for a project.
     *
     * @return array{total: int, paid: int, complete: int, draft: int}
     */
    public function getFilingSummary(LienProject $project): array
    {
        $filings = $project->filings;

        return [
            'total' => $filings->count(),
            'paid' => $filings->filter(fn ($f) => $f->paid_at !== null)->count(),
            'complete' => $filings->filter(fn ($f) => $f->status->value === 'complete')->count(),
            'draft' => $filings->filter(fn ($f) => $f->status->value === 'draft')->count(),
        ];
    }

    public function render(): View
    {
        $projects = LienProject::query()
            ->withoutGlobalScopes()
            ->with(['business', 'createdBy', 'filings'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('business', function ($bq) {
                            $bq->where('name', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('createdBy', function ($uq) {
                            $uq->where('email', 'like', "%{$this->search}%")
                                ->orWhere('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->latest()
            ->paginate(25);

        return view('admin.lien-stats', [
            'projects' => $projects,
        ])->layout('layouts.admin', ['title' => 'Lien Stats']);
    }
}

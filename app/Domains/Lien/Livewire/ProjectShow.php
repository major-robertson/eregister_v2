<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class ProjectShow extends Component
{
    public LienProject $project;

    public function mount(LienProject $project): void
    {
        Gate::authorize('view', $project);
        $this->project = $project;

        // Ensure deadlines are calculated if they don't exist
        if ($this->project->jobsite_state && $this->project->deadlines()->count() === 0) {
            app(DeadlineCalculator::class)->calculateForProject($this->project);
        }
    }

    public function startFiling(int $deadlineId): void
    {
        $deadline = LienProjectDeadline::findOrFail($deadlineId);

        // Check if there's an existing draft filing for this deadline
        $existingFiling = $this->project->filings()
            ->where('project_deadline_id', $deadline->id)
            ->where('status', FilingStatus::Draft)
            ->first();

        if ($existingFiling) {
            $this->redirect(route('lien.filings.start', [
                'project' => $this->project,
                'deadline' => $deadline,
            ]));

            return;
        }

        // Create a new draft filing
        $filing = LienFiling::create([
            'public_id' => Str::ulid()->toBase32(),
            'business_id' => $this->project->business_id,
            'project_id' => $this->project->id,
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'jurisdiction_state' => $this->project->jobsite_state,
            'jurisdiction_county' => $this->project->jobsite_county,
            'status' => FilingStatus::Draft,
            'created_by_user_id' => auth()->id(),
        ]);

        $this->redirect(route('lien.filings.start', [
            'project' => $this->project,
            'deadline' => $deadline,
        ]));
    }

    public function deleteProject(): void
    {
        Gate::authorize('delete', $this->project);

        $this->project->delete();

        session()->flash('message', 'Project deleted successfully.');
        $this->redirect(route('lien.projects.index'));
    }

    public function render(): View
    {
        $this->project->load([
            'parties',
            'deadlines' => function ($query) {
                $query->with(['documentType', 'rule', 'completedFiling', 'draftFiling'])
                    ->orderBy('document_type_id');
            },
            'filings' => function ($query) {
                $query->with('documentType')
                    ->latest()
                    ->limit(10);
            },
        ]);

        // Use StepStatusCalculator for computed step statuses
        $stepCalculator = app(StepStatusCalculator::class);
        $steps = $stepCalculator->forProject($this->project);

        // Find the next actionable deadline for the top banner
        // Only show steps with known deadlines — "deadline unknown" is shown in the steps list instead
        $nextDeadline = collect($steps)
            ->filter(fn ($step) => in_array($step->status, [
                DeadlineStatus::NotStarted,
                DeadlineStatus::DueSoon,
                DeadlineStatus::Missed,
                DeadlineStatus::InDraft,
                DeadlineStatus::AwaitingPayment,
            ], true))
            ->filter(fn ($step) => $step->isRequired() && $step->canStart && $step->deadlineDate !== null)
            ->sortBy(fn ($step) => $step->deadlineDate)
            ->first();

        return view('livewire.lien.project-show', [
            'parties' => $this->project->parties,
            'steps' => $steps,
            'filings' => $this->project->filings,
            'nextDeadline' => $nextDeadline,
        ])->layout('layouts.lien', ['title' => $this->project->name]);
    }
}

<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\EmailSequence;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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

        EmailSequence::startFor(
            'abandon_checkout',
            $filing,
            auth()->user(),
            $this->project->business,
            route('lien.filings.start', ['project' => $this->project, 'deadline' => $deadline])
        );

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
            'deadlines' => function ($query) {
                $query->with(['documentType', 'rule', 'completedFiling', 'draftFiling'])
                    ->orderBy('document_type_id');
            },
            'filings' => function ($query) {
                $query->with('documentType')->latest();
            },
        ]);

        // Use StepStatusCalculator for computed step statuses
        $stepCalculator = app(StepStatusCalculator::class);
        $steps = $stepCalculator->forProject($this->project);

        // Detect if ANY required step has been missed — missing one compromises the lien chain.
        // Excludes steps already Completed or NotApplicable.
        $requiredSteps = collect($steps)->filter(fn ($step) => $step->isRequired());
        $actionableRequired = $requiredSteps->filter(
            fn ($step) => ! in_array($step->status, [DeadlineStatus::Completed, DeadlineStatus::NotApplicable], true)
        );
        $hasAnyMissedDeadline = $actionableRequired->contains(
            fn ($step) => $step->status === DeadlineStatus::Missed
        );

        // Upcoming deadlines for the quiet right-rail summary: required steps
        // with a known due date that aren't finished, soonest first.
        $upcomingDeadlines = collect($steps)
            ->filter(fn ($step) => $step->isRequired()
                && $step->deadlineDate !== null
                && ! in_array($step->status, [DeadlineStatus::Completed, DeadlineStatus::NotApplicable], true))
            ->sortBy(fn ($step) => $step->deadlineDate)
            ->values();

        return view('livewire.lien.project-show', [
            'steps' => $steps,
            'hasAnyMissedDeadline' => $hasAnyMissedDeadline,
            'upcomingDeadlines' => $upcomingDeadlines,
            'waiverTypeCards' => $this->waiverTypeCards(),
            'documents' => $this->documents(),
            'subtitle' => collect([$this->project->jobsiteAddressLine(), $this->project->jobsite_county])
                ->filter()
                ->implode(' · '),
        ])->layout('components.layouts.portal', ['title' => $this->project->name]);
    }

    /**
     * The four canonical waiver types shown in the first-class Waivers card.
     * Each deep-links into the wizard with this project and type preselected.
     *
     * @return array<int, array{title: string, description: string, url: string}>
     */
    private function waiverTypeCards(): array
    {
        $descriptions = [
            WaiverKind::ConditionalProgress->value => 'Before a progress payment clears',
            WaiverKind::UnconditionalProgress->value => 'After a progress payment clears',
            WaiverKind::ConditionalFinal->value => 'Before the final payment clears',
            WaiverKind::UnconditionalFinal->value => 'After the final payment clears',
        ];

        return collect(WaiverKind::cases())
            ->map(fn (WaiverKind $kind) => [
                'title' => $kind->shortLabel(),
                'description' => $descriptions[$kind->value],
                'url' => route('lien.waivers.create', [
                    'project' => $this->project->public_id,
                    'kind' => $kind->value,
                ]),
            ])
            ->all();
    }

    /**
     * This project's waivers and filings merged into a single newest-first
     * list for the Documents card.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function documents(): Collection
    {
        $waivers = LienWaiver::query()
            ->where('project_id', $this->project->id)
            ->latest()
            ->get()
            ->map(fn (LienWaiver $waiver) => [
                'sort' => $waiver->created_at,
                'icon' => 'document-text',
                'title' => $waiver->kind->shortLabel(),
                'subtitle' => 'Waiver · '.$waiver->counterpartyDisplayName(),
                'status_label' => $waiver->status->label(),
                'status_color' => $waiver->status->color(),
                'url' => route('lien.waivers.show', $waiver),
            ]);

        $filings = $this->project->filings->map(fn (LienFiling $filing) => [
            'sort' => $filing->created_at,
            'icon' => 'document',
            'title' => $filing->documentType->name,
            'subtitle' => 'Filing · '.$filing->created_at->eastern()->format('M j, Y'),
            'status_label' => $filing->status->label(),
            'status_color' => $filing->status->color(),
            'url' => route('lien.filings.show', $filing),
        ]);

        return $waivers->concat($filings)
            ->sortByDesc('sort')
            ->values();
    }
}

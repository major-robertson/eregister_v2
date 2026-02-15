<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienStateRule;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LienFilingDetail extends Component
{
    use AuthorizesRequests;

    public LienFiling $lienFiling;

    public string $newStatus = '';

    public string $note = '';

    public string $comment = '';

    public function mount(LienFiling $lienFiling): void
    {
        $this->lienFiling = $lienFiling->load([
            'createdBy',
            'project.business',
            'project.parties',
            'project.deadlines.rule',
            'project.deadlines.documentType',
            'documentType',
            'events' => fn ($q) => $q->whereIn('event_type', ['status_changed', 'note_added'])->latest()->limit(50),
            'events.creator',
        ]);
    }

    public function render(): View
    {
        $project = $this->lienFiling->project;
        $stateRule = $project?->jobsite_state
            ? LienStateRule::find($project->jobsite_state)
            : null;

        $deadlines = $project?->deadlines
            ?->sortBy(fn ($d) => $d->due_date ?? now()->addYears(100))
            ->values() ?? collect();

        $requiredDeadlines = $deadlines->filter(fn ($d) => $d->rule?->is_required);

        $filingDocStatus = $this->computeFilingDocStatus($project, $stateRule, $deadlines);

        return view('lien.admin.filing-detail', [
            'filing' => $this->lienFiling,
            'kanbanColumn' => KanbanColumn::forFiling($this->lienFiling),
            'allowedTransitions' => $this->lienFiling->allowedTransitions(),
            'activityLog' => $this->getActivityLog(),
            'canChangeStatus' => auth()->user()->can('changeStatus', $this->lienFiling),
            'canUpdate' => auth()->user()->can('update', $this->lienFiling),
            'canAddComment' => auth()->user()->can('addComment', $this->lienFiling),
            'requiredDeadlines' => $requiredDeadlines,
            'filingDocStatus' => $filingDocStatus,
        ])->layout('layouts.admin', ['title' => 'Filing Detail']);
    }

    /**
     * Compute the traffic-light status for the filing's document type.
     *
     * @return array{color: string, label: string, docName: string, dueDate: ?string, daysRemaining: ?int, isRequired: bool}
     */
    protected function computeFilingDocStatus($project, ?LienStateRule $stateRule, Collection $deadlines): array
    {
        $docType = $this->lienFiling->documentType;
        $docName = $docType?->name ?? 'Unknown';

        $default = ['color' => 'zinc', 'label' => 'No project data', 'docName' => $docName, 'dueDate' => null, 'daysRemaining' => null, 'isRequired' => false];

        if (! $project || ! $docType) {
            return $default;
        }

        // Find the matching project deadline for this filing's document type
        $deadline = $deadlines->first(
            fn ($d) => $d->document_type_id === $docType->id
        );

        $isRequired = $deadline?->rule?->is_required ?? false;

        if (! $deadline) {
            return [
                'color' => 'zinc',
                'label' => 'No deadline rule found',
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        // Optional document — always green, no urgency
        if (! $isRequired) {
            return [
                'color' => 'green',
                'label' => 'Optional — file anytime',
                'docName' => $docName,
                'dueDate' => $deadline->due_date?->format('M j, Y'),
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        // Not applicable for this claimant/state
        if ($deadline->status === DeadlineStatus::NotApplicable) {
            return [
                'color' => 'green',
                'label' => 'Not required'.($stateRule ? ' in '.$project->jobsite_state : ''),
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => false,
            ];
        }

        $dueDate = $deadline->due_date?->format('M j, Y');
        $daysRemaining = $deadline->daysRemaining();

        // Completed
        if ($deadline->status === DeadlineStatus::Completed) {
            return [
                'color' => 'green',
                'label' => 'Filed',
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => null,
                'isRequired' => $isRequired,
            ];
        }

        // Overdue / Missed
        if ($deadline->status === DeadlineStatus::Missed || $deadline->isOverdue()) {
            $pastDays = $daysRemaining !== null ? abs($daysRemaining) : null;

            return [
                'color' => 'red',
                'label' => 'OVERDUE'.($pastDays !== null ? " — {$pastDays} days past deadline" : ''),
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => $daysRemaining,
                'isRequired' => $isRequired,
            ];
        }

        // Deadline unknown
        if ($deadline->status === DeadlineStatus::DeadlineUnknown) {
            return [
                'color' => 'yellow',
                'label' => ($isRequired ? 'Required' : 'Optional').' — deadline unknown (missing dates)',
                'docName' => $docName,
                'dueDate' => null,
                'daysRemaining' => null,
                'isRequired' => $isRequired,
            ];
        }

        // Has a due date in the future
        if ($daysRemaining !== null && $daysRemaining >= 0) {
            $urgency = $daysRemaining <= 7 ? 'yellow' : 'green';

            return [
                'color' => $urgency,
                'label' => ($isRequired ? 'Required' : 'Optional')." — {$daysRemaining} days left to file",
                'docName' => $docName,
                'dueDate' => $dueDate,
                'daysRemaining' => $daysRemaining,
                'isRequired' => $isRequired,
            ];
        }

        return [
            'color' => 'yellow',
            'label' => 'Status unknown — verify timeline',
            'docName' => $docName,
            'dueDate' => $dueDate,
            'daysRemaining' => $daysRemaining,
            'isRequired' => $isRequired,
        ];
    }

    /**
     * Get activity log including status changes and comments.
     */
    public function getActivityLog(): Collection
    {
        return $this->lienFiling->events()
            ->whereIn('event_type', ['status_changed', 'note_added'])
            ->with('creator')
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * Update the filing status.
     */
    public function updateStatus(): void
    {
        $this->authorize('changeStatus', $this->lienFiling);

        $this->validate([
            'newStatus' => ['required', Rule::enum(FilingStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $action = app(ChangeFilingStatus::class);
        $action->execute(
            filing: $this->lienFiling,
            newStatus: FilingStatus::from($this->newStatus),
            note: $this->note ?: null,
        );

        $this->lienFiling->refresh();
        $this->reset(['newStatus', 'note']);

        session()->flash('success', 'Status updated successfully.');
    }

    /**
     * Add a comment to the filing without changing status.
     */
    public function addComment(): void
    {
        $this->authorize('addComment', $this->lienFiling);

        $this->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $action = app(AddFilingComment::class);
        $action->execute(
            filing: $this->lienFiling,
            comment: $this->comment,
        );

        $this->reset('comment');

        session()->flash('success', 'Comment added successfully.');
    }

    /**
     * Toggle the needs_review flag.
     */
    public function toggleNeedsReview(): void
    {
        $this->authorize('update', $this->lienFiling);

        $this->lienFiling->update([
            'needs_review' => ! $this->lienFiling->needs_review,
        ]);

        // Log the change
        $this->lienFiling->events()->create([
            'business_id' => $this->lienFiling->business_id,
            'event_type' => 'needs_review_changed',
            'payload_json' => [
                'needs_review' => $this->lienFiling->needs_review,
            ],
            'created_by' => auth()->id(),
        ]);

        $this->lienFiling->refresh();

        session()->flash('success', $this->lienFiling->needs_review
            ? 'Filing flagged for manager review.'
            : 'Review flag removed.');
    }
}

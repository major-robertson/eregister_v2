<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
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
            'documentType',
            'events' => fn ($q) => $q->whereIn('event_type', ['status_changed', 'note_added'])->latest()->limit(50),
            'events.creator',
        ]);
    }

    public function render(): View
    {
        return view('lien.admin.filing-detail', [
            'filing' => $this->lienFiling,
            'kanbanColumn' => KanbanColumn::forFiling($this->lienFiling),
            'allowedTransitions' => $this->lienFiling->allowedTransitions(),
            'activityLog' => $this->getActivityLog(),
            'canChangeStatus' => auth()->user()->can('changeStatus', $this->lienFiling),
            'canUpdate' => auth()->user()->can('update', $this->lienFiling),
            'canAddComment' => auth()->user()->can('addComment', $this->lienFiling),
        ])->layout('layouts.admin', ['title' => 'Filing Detail']);
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

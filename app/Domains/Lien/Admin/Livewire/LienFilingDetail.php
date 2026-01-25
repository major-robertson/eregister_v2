<?php

namespace App\Domains\Lien\Admin\Livewire;

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

    public function mount(LienFiling $lienFiling): void
    {
        $this->lienFiling = $lienFiling->load([
            'project.business',
            'documentType',
            'events' => fn ($q) => $q->where('event_type', 'status_changed')->latest()->limit(20),
            'events.creator',
        ]);
    }

    public function render(): View
    {
        return view('lien.admin.filing-detail', [
            'filing' => $this->lienFiling,
            'kanbanColumn' => KanbanColumn::forFiling($this->lienFiling),
            'allowedTransitions' => $this->lienFiling->allowedTransitions(),
            'statusHistory' => $this->getStatusHistory(),
            'canChangeStatus' => auth()->user()->can('changeStatus', $this->lienFiling),
            'canUpdate' => auth()->user()->can('update', $this->lienFiling),
        ])->layout('layouts.admin', ['title' => 'Filing Detail']);
    }

    /**
     * Get status change history.
     */
    public function getStatusHistory(): Collection
    {
        return $this->lienFiling->events()
            ->where('event_type', 'status_changed')
            ->with('creator')
            ->latest()
            ->limit(20)
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

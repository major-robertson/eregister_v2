<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\AddFilingComment;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Admin\Actions\MarkStillWaiting;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Enums\SearchMode;
use App\Domains\Lien\Admin\Support\BoardCard;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class LienBoard extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public ?SearchMode $searchMode = null;

    public function updatedSearch(): void
    {
        $this->resetPage('businessesPage');
        $this->resetPage('liensPage');

        if (blank($this->search)) {
            $this->searchMode = null;
        }
    }

    public function searchBusinesses(): void
    {
        $this->searchMode = SearchMode::Businesses;
        $this->resetPage('businessesPage');
    }

    public function searchLiens(): void
    {
        $this->searchMode = SearchMode::Liens;
        $this->resetPage('liensPage');
    }

    public function clearSearchMode(): void
    {
        $this->searchMode = null;
    }

    /**
     * The "Start" button on a New card: we have picked the order up. A Paid
     * filing moves to In Fulfillment; a full-service order (In Fulfillment
     * since payment) gets a note instead. Either way the card moves to In
     * Progress. Does nothing if the card has left New in the meantime.
     */
    public function start(string $publicId): void
    {
        $filing = LienFiling::withoutGlobalScope('business')->where('public_id', $publicId)->firstOrFail();

        $this->authorize('changeStatus', $filing);

        if (BoardCard::forFiling($filing)?->column !== KanbanColumn::New) {
            return;
        }

        if ($filing->status === FilingStatus::Paid) {
            app(ChangeFilingStatus::class)->execute($filing, FilingStatus::InFulfillment, 'Started from the board');
        } else {
            app(AddFilingComment::class)->execute($filing, 'Started from the board');
        }

        unset($this->boardCards);
    }

    /**
     * "Still waiting" on a card the customer emailed us about: clears the
     * flag and, while the filing waits on the customer, restarts the
     * reminders (MarkStillWaiting). No note from the board.
     */
    public function stillWaiting(string $publicId): void
    {
        $filing = LienFiling::withoutGlobalScope('business')->where('public_id', $publicId)->firstOrFail();

        $this->authorize('changeStatus', $filing);

        app(MarkStillWaiting::class)->execute($filing);

        unset($this->boardCards);
    }

    /**
     * The cards on the board (no search), keyed by filing id, in board order.
     *
     * @return Collection<int, BoardCard>
     */
    #[Computed]
    public function boardCards(): Collection
    {
        return BoardCard::all();
    }

    public function render(): View
    {
        $data = ['searchMode' => $this->searchMode];

        if ($this->searchMode === SearchMode::Businesses) {
            $data['businessResults'] = $this->getBusinessResults();
            $data['resultCount'] = $data['businessResults']->total();
        } elseif ($this->searchMode === SearchMode::Liens) {
            $data['lienResults'] = $this->getLienResults();
            $data['resultCount'] = $data['lienResults']->total();
        } else {
            $data['columns'] = $this->getColumns();
            $data['filings'] = $this->getFilings();
            $data['cards'] = $this->search ? collect() : $this->boardCards;
            $data['resultCount'] = $data['filings']->flatten()->count();
        }

        return view('lien.admin.board', $data)
            ->layout('layouts.admin', ['title' => 'Lien Filings Board']);
    }

    /**
     * Get columns -- all FilingStatus cases when searching, otherwise the Kanban columns.
     */
    public function getColumns(): array
    {
        if ($this->search) {
            return FilingStatus::cases();
        }

        return KanbanColumn::cases();
    }

    /**
     * Get filings grouped by column. When searching, returns all statuses
     * grouped by FilingStatus value (like board-all). Without search, the
     * paid, active filings grouped by KanbanColumn (see BoardCard), nearest
     * deadline first within each column.
     */
    public function getFilings(): Collection
    {
        if (! $this->search) {
            return $this->boardCards
                ->groupBy(fn (BoardCard $card) => $card->column->value)
                ->map(fn (Collection $cards) => $cards->map(fn (BoardCard $card) => $card->filing)->values());
        }

        return LienFiling::query()
            ->withoutGlobalScope('business')
            ->withTrashed()
            ->adminSearch($this->search)
            ->with([
                'project',
                'project.business',
                'documentType',
                'createdBy',
                'events' => fn ($q) => $q->where('event_type', 'note_added')->latest()->limit(1),
            ])
            ->withExists(['events as dates_changed_after_deadline_block_exists' => fn ($q) => $q->where('event_type', LienFilingEvent::DATES_CHANGED_AFTER_DEADLINE_BLOCK)])
            // Rush orders first in each column: expedite, then rush, soonest
            // submit-by date first; then everything else by when it was paid.
            // (The board itself orders its cards in BoardCard::sorted().)
            ->orderByRaw("CASE rush_tier WHEN 'expedite' THEN 0 WHEN 'rush' THEN 1 ELSE 2 END")
            ->orderByRaw('CASE WHEN rush_tier IS NULL THEN NULL ELSE must_submit_by END')
            ->orderBy('paid_at', 'asc')
            ->get()
            ->groupBy(fn (LienFiling $filing) => $filing->status->value);
    }

    /**
     * Get count for a specific column.
     */
    public function getColumnCount(Collection $filings, KanbanColumn $column): int
    {
        return $filings->get($column->value)?->count() ?? 0;
    }

    /**
     * Search businesses with associated users.
     */
    public function getBusinessResults(): LengthAwarePaginator
    {
        return Business::query()
            ->when($this->search, fn ($query) => $query->adminSearch($this->search))
            ->withCount(['lienProjects', 'formApplications'])
            ->with(['users' => fn ($q) => $q->limit(4)])
            ->orderBy('name')
            ->paginate(15, pageName: 'businessesPage');
    }

    /**
     * Search all lien filings regardless of status.
     */
    public function getLienResults(): LengthAwarePaginator
    {
        return LienFiling::query()
            ->withoutGlobalScope('business')
            ->withTrashed()
            ->when($this->search, fn ($query) => $query->adminSearch($this->search))
            ->with([
                'project',
                'project.business',
                'documentType',
                'createdBy',
            ])
            ->latest()
            ->paginate(15, pageName: 'liensPage');
    }
}

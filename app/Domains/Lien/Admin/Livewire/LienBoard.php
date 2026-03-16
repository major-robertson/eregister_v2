<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Enums\SearchMode;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class LienBoard extends Component
{
    use WithPagination;

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
            $data['resultCount'] = $data['filings']->flatten()->count();
        }

        return view('lien.admin.board', $data)
            ->layout('layouts.admin', ['title' => 'Lien Filings Board']);
    }

    /**
     * Get columns -- all FilingStatus cases when searching, otherwise the 4 Kanban columns.
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
     * grouped by FilingStatus value (like board-all). Without search, uses the
     * standard paid/active filter grouped by KanbanColumn.
     */
    public function getFilings(): Collection
    {
        $query = LienFiling::query()
            ->withoutGlobalScope('business')
            ->when($this->search, fn ($query) => $query->adminSearch($this->search))
            ->with([
                'project',
                'project.business',
                'documentType',
                'createdBy',
                'events' => fn ($q) => $q->where('event_type', 'note_added')->latest()->limit(1),
            ])
            ->orderBy('created_at', 'asc');

        if (! $this->search) {
            $query->whereNotNull('paid_at')
                ->whereNotIn('status', [
                    FilingStatus::Draft,
                    FilingStatus::AwaitingPayment,
                    FilingStatus::Complete,
                    FilingStatus::Canceled,
                    FilingStatus::Refunded,
                ]);
        }

        return $query->get()->groupBy(
            $this->search
                ? fn (LienFiling $filing) => $filing->status->value
                : fn (LienFiling $filing) => KanbanColumn::forFiling($filing)->value
        );
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

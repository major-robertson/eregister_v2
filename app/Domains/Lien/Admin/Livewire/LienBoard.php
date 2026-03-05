<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class LienBoard extends Component
{
    public string $search = '';

    public function render(): View
    {
        return view('lien.admin.board', [
            'columns' => $this->getColumns(),
            'filings' => $this->getFilings(),
        ])->layout('layouts.admin', ['title' => 'Lien Filings Board']);
    }

    /**
     * Get all Kanban columns.
     */
    public function getColumns(): array
    {
        return KanbanColumn::cases();
    }

    /**
     * Get filings grouped by Kanban column.
     */
    public function getFilings(): Collection
    {
        return LienFiling::query()
            ->withoutGlobalScope('business')
            ->whereNotNull('paid_at')
            ->whereNotIn('status', [
                FilingStatus::Draft,
                FilingStatus::AwaitingPayment,
                FilingStatus::Complete,
                FilingStatus::Canceled,
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('project', function ($pq) {
                        $pq->where('name', 'like', "%{$this->search}%")
                            ->orWhere('jobsite_address1', 'like', "%{$this->search}%")
                            ->orWhere('jobsite_city', 'like', "%{$this->search}%")
                            ->orWhere('jobsite_state', 'like', "%{$this->search}%")
                            ->orWhere('jobsite_zip', 'like', "%{$this->search}%")
                            ->orWhere('jobsite_county', 'like', "%{$this->search}%");
                    })
                        ->orWhereHas('project.business', function ($bq) {
                            $bq->where('name', 'like', "%{$this->search}%");
                        })
                        ->orWhereHas('createdBy', function ($uq) {
                            $uq->where('email', 'like', "%{$this->search}%")
                                ->orWhere('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%");
                        });
                });
            })
            ->with([
                'project',
                'project.business',
                'documentType',
                'createdBy',
                'events' => fn ($q) => $q->where('event_type', 'note_added')->latest()->limit(1),
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(fn (LienFiling $filing) => KanbanColumn::forFiling($filing)->value);
    }

    /**
     * Get count for a specific column.
     */
    public function getColumnCount(Collection $filings, KanbanColumn $column): int
    {
        return $filings->get($column->value)?->count() ?? 0;
    }
}

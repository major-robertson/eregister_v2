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
            ->whereNotNull('paid_at')
            ->whereNotIn('status', [
                FilingStatus::Draft,
                FilingStatus::AwaitingPayment,
                FilingStatus::Canceled,
            ])
            ->with(['project', 'project.business', 'documentType'])
            ->orderBy('created_at', 'asc') // Oldest first in queue
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

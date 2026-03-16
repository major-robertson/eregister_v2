<?php

namespace App\Domains\Lien\Admin\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class LienBoardAll extends Component
{
    public string $search = '';

    public function render(): View
    {
        return view('lien.admin.board-all', [
            'columns' => FilingStatus::cases(),
            'filings' => $this->getFilings(),
        ])->layout('layouts.admin', ['title' => 'All Filings Board']);
    }

    /**
     * Get all filings grouped by status.
     */
    public function getFilings(): Collection
    {
        return LienFiling::query()
            ->withoutGlobalScope('business')
            ->when($this->search, fn ($query) => $query->adminSearch($this->search))
            ->with([
                'project',
                'project.business',
                'documentType',
                'createdBy',
                'events' => fn ($q) => $q->where('event_type', 'note_added')->latest()->limit(1),
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(fn (LienFiling $filing) => $filing->status->value);
    }
}

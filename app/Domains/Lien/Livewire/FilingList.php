<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class FilingList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $business = Auth::user()->currentBusiness();

        $filings = LienFiling::query()
            ->with(['project:id,public_id,name', 'documentType:id,name'])
            ->whereHas('project', fn ($q) => $q->where('business_id', $business->id))
            ->when($this->search, fn ($q) => $q->where(fn ($query) => $query
                ->whereHas('project', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('documentType', fn ($dq) => $dq->where('name', 'like', "%{$this->search}%"))
                ->orWhere('jurisdiction_county', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.lien.filing-list', [
            'filings' => $filings,
            'statuses' => FilingStatus::cases(),
        ])->layout('layouts.lien', ['title' => 'Filings']);
    }
}

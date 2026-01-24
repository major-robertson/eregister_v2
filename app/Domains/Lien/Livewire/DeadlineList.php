<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DeadlineList extends Component
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

        $deadlines = LienProjectDeadline::query()
            ->with(['project:id,public_id,name', 'documentType:id,name'])
            ->where('business_id', $business->id)
            ->when($this->search, fn ($q) => $q->where(fn ($query) => $query
                ->whereHas('project', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('documentType', fn ($dq) => $dq->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->orderBy('due_date')
            ->paginate(15);

        return view('livewire.lien.deadline-list', [
            'deadlines' => $deadlines,
            'statuses' => DeadlineStatus::cases(),
        ])->layout('layouts.lien', ['title' => 'Deadlines']);
    }
}

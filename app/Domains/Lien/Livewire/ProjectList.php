<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Lien\Models\LienProject;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render(): View
    {
        $business = Auth::user()->currentBusiness();

        $projects = LienProject::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('job_number', 'like', '%'.$this->search.'%')
                        ->orWhere('jobsite_city', 'like', '%'.$this->search.'%')
                        ->orWhere('jobsite_state', 'like', '%'.$this->search.'%');
                });
            })
            ->with(['deadlines' => function ($query) {
                $query->where('status', 'pending')
                    ->whereNotNull('due_date')
                    ->orderBy('due_date');
            }])
            ->withCount(['filings', 'parties'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.lien.project-list', [
            'projects' => $projects,
        ])->layout('layouts.lien', ['title' => 'Lien Projects']);
    }
}

<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The full waiver table: filter by status/direction, search by counterparty
 * or project name. Row click opens the waiver. Queries are pinned to the
 * current business by LienWaiver's BelongsToBusiness global scope.
 */
class WaiverList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $directionFilter = '';

    /** Optional project public_id filter (from a project page's waivers link). */
    #[Url]
    public string $project = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDirectionFilter(): void
    {
        $this->resetPage();
    }

    public function openWaiver(string $publicId): void
    {
        $this->redirect(route('lien.waivers.show', ['waiver' => $publicId]), navigate: true);
    }

    public function render(): View
    {
        $waivers = LienWaiver::query()
            ->with(['project:id,public_id,name'])
            ->when($this->search, fn ($q) => $q->where(fn ($query) => $query
                ->where('counterparty_company', 'like', "%{$this->search}%")
                ->orWhere('counterparty_name', 'like', "%{$this->search}%")
                ->orWhereHas('project', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->directionFilter, fn ($q) => $q->where('direction', $this->directionFilter))
            ->when($this->project, fn ($q) => $q->whereHas('project', fn ($pq) => $pq->where('public_id', $this->project)))
            ->latest()
            ->paginate(15);

        // The project scope is business-guarded by BelongsToBusiness on the
        // relation, so a foreign public_id simply matches nothing.
        $projectName = $this->project
            ? LienProject::where('public_id', $this->project)->value('name')
            : null;

        return view('livewire.lien.waivers.waiver-list', [
            'waivers' => $waivers,
            'statuses' => WaiverStatus::cases(),
            'directions' => WaiverDirection::cases(),
            'projectFilterName' => $projectName,
        ])->layout('components.layouts.portal', ['title' => 'All Waivers']);
    }
}

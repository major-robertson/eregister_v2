<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Business\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessesList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = Business::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    // Search in JSON business_address fields
                    $q->where('business_address->city', 'like', "%{$this->search}%")
                        ->orWhere('business_address->state', 'like', "%{$this->search}%")
                        ->orWhere('business_address->street', 'like', "%{$this->search}%")
                        ->orWhere('business_address->zip', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%")
                        ->orWhere('legal_name', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['users', 'formApplications', 'lienProjects'])
            ->addSelect([
                'has_active_subscription' => DB::table('subscriptions')
                    ->whereColumn('subscriptions.business_id', 'businesses.id')
                    ->where('subscriptions.stripe_status', 'active')
                    ->selectRaw('1')
                    ->limit(1),
            ])
            ->latest()
            ->paginate(25);

        return view('admin.businesses-list', [
            'businesses' => $businesses,
        ])->layout('layouts.admin', ['title' => 'Businesses']);
    }
}

<?php

namespace App\Domains\Admin\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class UsersList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('email', 'like', "%{$this->search}%")
                        ->orWhere('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->withCount('businesses')
            ->addSelect([
                'has_subscription' => DB::table('subscriptions')
                    ->join('business_user', 'subscriptions.business_id', '=', 'business_user.business_id')
                    ->whereColumn('business_user.user_id', 'users.id')
                    ->where('subscriptions.stripe_status', 'active')
                    ->selectRaw('1')
                    ->limit(1),
            ])
            ->latest()
            ->paginate(25);

        return view('admin.users-list', [
            'users' => $users,
        ])->layout('layouts.admin', ['title' => 'Users']);
    }
}

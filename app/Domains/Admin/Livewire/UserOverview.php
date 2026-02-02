<?php

namespace App\Domains\Admin\Livewire;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class UserOverview extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function render(): View
    {
        // Eager load businesses with pivot data and subscriptions
        $this->user->load([
            'businesses' => fn ($q) => $q
                ->with(['subscriptions'])
                ->withPivot(['role', 'created_at']),
        ]);

        // Subquery for business IDs (avoids materializing in PHP)
        $businessIds = $this->user->businesses()->select('businesses.id');

        // Summary stats
        $totalPaymentsSum = Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereIn('business_id', $businessIds)
            ->sum('amount_cents');

        $activeSubscriptionsCount = DB::table('subscriptions')
            ->whereIn('business_id', $businessIds)
            ->where('stripe_status', 'active')
            ->count();

        // Recent payments (paginated)
        $recentPayments = Payment::query()
            ->whereIn('business_id', $businessIds)
            ->with('business')
            ->latest('paid_at')
            ->paginate(15, pageName: 'payments');

        return view('admin.user-overview', [
            'totalPaymentsSum' => $totalPaymentsSum,
            'activeSubscriptionsCount' => $activeSubscriptionsCount,
            'recentPayments' => $recentPayments,
        ])->layout('layouts.admin', ['title' => $this->user->name]);
    }

    /**
     * Format subscription status for display.
     */
    public function formatSubscriptionStatus(string $status): string
    {
        return match ($status) {
            'active' => 'Active',
            'canceled' => 'Canceled',
            'incomplete' => 'Incomplete',
            'incomplete_expired' => 'Expired',
            'past_due' => 'Past Due',
            'trialing' => 'Trialing',
            'unpaid' => 'Unpaid',
            default => ucfirst($status),
        };
    }

    /**
     * Get badge color for subscription status.
     */
    public function getSubscriptionStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'trialing' => 'blue',
            'canceled' => 'zinc',
            'incomplete', 'incomplete_expired' => 'amber',
            'past_due', 'unpaid' => 'red',
            default => 'zinc',
        };
    }
}

<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StatsBoard extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    public function render(): View
    {
        return view('admin.stats-board', [
            'signupStats' => $this->getSignupStats(),
            'paymentStats' => $this->getPaymentStats(),
            'subscriptionStats' => $this->getSubscriptionStats(),
            'lienFilingStats' => $this->getLienFilingStats(),
            'recentSignups' => $this->getRecentSignups(),
            'recentPayments' => $this->getRecentPayments(),
            'recentSubscriptions' => $this->getRecentSubscriptions(),
        ])->layout('layouts.admin', ['title' => 'Stats Dashboard']);
    }

    /**
     * Get UTC date range for a given EST period.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function getEstDateRange(string $period): array
    {
        $now = now(self::STATS_TIMEZONE);

        return match ($period) {
            'today' => [
                $now->copy()->startOfDay()->utc(),
                $now->copy()->endOfDay()->utc(),
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay()->utc(),
                $now->copy()->subDay()->endOfDay()->utc(),
            ],
            'this_week' => [
                $now->copy()->startOfWeek()->utc(),
                $now->copy()->endOfWeek()->utc(),
            ],
            'this_month' => [
                $now->copy()->startOfMonth()->utc(),
                $now->copy()->endOfMonth()->endOfDay()->utc(),
            ],
        };
    }

    /**
     * Get signup counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getSignupStats(): array
    {
        return [
            'today' => User::whereBetween('created_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => User::whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => User::whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => User::whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
        ];
    }

    /**
     * Get payment counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getPaymentStats(): array
    {
        return [
            'today' => Payment::where('status', PaymentStatus::Succeeded)
                ->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => Payment::where('status', PaymentStatus::Succeeded)
                ->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => Payment::where('status', PaymentStatus::Succeeded)
                ->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => Payment::where('status', PaymentStatus::Succeeded)
                ->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
        ];
    }

    /**
     * Get subscription counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getSubscriptionStats(): array
    {
        return [
            'today' => DB::table('subscriptions')
                ->whereBetween('created_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => DB::table('subscriptions')
                ->whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => DB::table('subscriptions')
                ->whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => DB::table('subscriptions')
                ->whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
        ];
    }

    /**
     * Get lien filing paid counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getLienFilingStats(): array
    {
        $paidStatuses = [
            FilingStatus::Paid,
            FilingStatus::InFulfillment,
            FilingStatus::Mailed,
            FilingStatus::Recorded,
            FilingStatus::Complete,
        ];

        return [
            'today' => LienFiling::whereIn('status', $paidStatuses)
                ->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => LienFiling::whereIn('status', $paidStatuses)
                ->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => LienFiling::whereIn('status', $paidStatuses)
                ->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => LienFiling::whereIn('status', $paidStatuses)
                ->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
        ];
    }

    /**
     * Get the last 20 signups with business info.
     */
    protected function getRecentSignups(): Collection
    {
        return User::query()
            ->with(['businesses' => function ($query) {
                $query->latest('business_user.created_at')->limit(1);
            }])
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (User $user) {
                $business = $user->businesses->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'state' => $business?->business_address['state'] ?? null,
                    'has_business' => $business !== null,
                    'lien_ready' => $business?->lien_onboarding_completed_at !== null,
                    'subscribed' => $business ? $this->hasActiveSubscription($business->id) : false,
                    'created_at' => $user->created_at,
                ];
            });
    }

    /**
     * Get the last 20 payments with user info.
     */
    protected function getRecentPayments(): Collection
    {
        return Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->with(['business.users'])
            ->latest('paid_at')
            ->limit(20)
            ->get()
            ->map(function (Payment $payment) {
                $user = $payment->business?->users->first();

                return [
                    'id' => $payment->id,
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'amount' => $payment->formattedAmount(),
                    'type' => $payment->stripe_subscription_id ? 'Subscription' : 'One-time',
                    'status' => $payment->status->label(),
                    'paid_at' => $payment->paid_at,
                ];
            });
    }

    /**
     * Get the last 20 subscriptions with user info.
     */
    protected function getRecentSubscriptions(): Collection
    {
        return DB::table('subscriptions')
            ->join('businesses', 'subscriptions.business_id', '=', 'businesses.id')
            ->join('business_user', 'businesses.id', '=', 'business_user.business_id')
            ->join('users', 'business_user.user_id', '=', 'users.id')
            ->select([
                'subscriptions.id',
                'subscriptions.stripe_status',
                'subscriptions.created_at',
                'users.first_name',
                'users.last_name',
                'users.email',
                'businesses.business_address',
            ])
            ->orderBy('subscriptions.created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($sub) {
                $address = json_decode($sub->business_address, true);

                return [
                    'id' => $sub->id,
                    'name' => trim($sub->first_name.' '.$sub->last_name),
                    'email' => $sub->email,
                    'state' => $address['state'] ?? null,
                    'status' => $this->formatSubscriptionStatus($sub->stripe_status),
                    'status_color' => $this->getSubscriptionStatusColor($sub->stripe_status),
                    'created_at' => Carbon::parse($sub->created_at),
                ];
            });
    }

    /**
     * Check if a business has an active subscription.
     */
    protected function hasActiveSubscription(int $businessId): bool
    {
        return DB::table('subscriptions')
            ->where('business_id', $businessId)
            ->where('stripe_status', 'active')
            ->exists();
    }

    /**
     * Format subscription status for display.
     */
    protected function formatSubscriptionStatus(string $status): string
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
    protected function getSubscriptionStatusColor(string $status): string
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

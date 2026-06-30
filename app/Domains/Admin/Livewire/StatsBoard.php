<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\LlcFormation;
use App\Domains\Forms\Models\SalesTaxRegistration;
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
            'salesTaxStats' => $this->getSalesTaxStats(),
            'formationStats' => $this->getFormationStats(),
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
            FilingStatus::AwaitingClient,
            FilingStatus::AwaitingEsign,
            FilingStatus::AwaitingNotary,
            FilingStatus::InFulfillment,
            FilingStatus::SubmittedForRecording,
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
     * Get paid sales tax registration counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getSalesTaxStats(): array
    {
        return [
            'today' => SalesTaxRegistration::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => SalesTaxRegistration::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => SalesTaxRegistration::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => SalesTaxRegistration::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
        ];
    }

    /**
     * Get paid LLC formation counts for different time periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getFormationStats(): array
    {
        return [
            'today' => LlcFormation::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
            'yesterday' => LlcFormation::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
            'this_week' => LlcFormation::whereNotNull('paid_at')
                ->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
            'this_month' => LlcFormation::whereNotNull('paid_at')
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
                    'email' => $user->email,
                    'landing_path' => $user->signup_landing_path,
                    'referrer' => $this->stripScheme($user->signup_referrer),
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
            ->with(['business.users', 'price'])
            ->latest('paid_at')
            ->limit(20)
            ->get()
            ->map(function (Payment $payment) {
                $user = $payment->business?->users->first();
                $kind = $this->paymentKind($payment);

                return [
                    'id' => $payment->id,
                    'email' => $user?->email ?? 'Unknown',
                    'amount' => $payment->formattedAmount(),
                    'kind' => $kind['label'],
                    'kind_color' => $kind['color'],
                    'type' => $payment->stripe_subscription_id ? 'Subscription' : 'One-time',
                    'paid_at' => $payment->paid_at,
                ];
            });
    }

    /**
     * Resolve the human-readable "kind" of a payment (the product purchased)
     * and a badge color, derived from the catalog price snapshot. Falls back to
     * subscription/one-time when no price is attached (e.g. legacy SaaS rows).
     *
     * @return array{label: string, color: string}
     */
    protected function paymentKind(Payment $payment): array
    {
        $price = $payment->price;

        if ($price) {
            $label = match ($price->product_key) {
                'prelim_notice' => 'Preliminary Notice',
                'noi' => 'Notice of Intent',
                'mechanics_lien' => 'Mechanics Lien',
                'lien_release' => 'Lien Release',
                'demand_letter' => 'Demand Letter',
                'sales_tax_permit' => 'Sales Tax Reg',
                'llc' => 'LLC Formation',
                default => ucwords(str_replace('_', ' ', (string) $price->product_key)),
            };

            $color = match ($price->product_family) {
                'lien' => 'amber',
                'tax' => 'teal',
                'llc', 'formation' => 'indigo',
                'saas' => 'purple',
                default => 'zinc',
            };

            return ['label' => $label, 'color' => $color];
        }

        if ($payment->stripe_subscription_id) {
            return ['label' => 'Subscription', 'color' => 'purple'];
        }

        return ['label' => 'Other', 'color' => 'zinc'];
    }

    /**
     * Strip the http(s):// scheme from a URL for compact display.
     */
    protected function stripScheme(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        return preg_replace('#^https?://#i', '', $url);
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

<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class SalesTaxStats extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    public function render(): View
    {
        return view('admin.sales-tax-stats', [
            'revenueStats' => $this->getRevenueStats(),
            'registrationStats' => $this->getRegistrationStats(),
            'recentRegistrations' => $this->getRecentRegistrations(),
        ])->layout('layouts.admin', ['title' => 'Sales Tax Stats']);
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
     * Get sales-tax-product revenue (in cents) for different periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getRevenueStats(): array
    {
        return [
            'today' => $this->sumTaxRevenue('today'),
            'yesterday' => $this->sumTaxRevenue('yesterday'),
            'this_week' => $this->sumTaxRevenue('this_week'),
            'this_month' => $this->sumTaxRevenue('this_month'),
        ];
    }

    /**
     * Sum succeeded sales-tax-product payment amounts for an EST period.
     */
    protected function sumTaxRevenue(string $period): int
    {
        return (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('price', fn ($query) => $query->where('product_family', 'tax'))
            ->whereBetween('paid_at', $this->getEstDateRange($period))
            ->sum('amount_cents');
    }

    /**
     * Get sales tax registration counts (started vs paid) for periods (EST).
     *
     * @return array{
     *     started: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     paid: array{today: int, yesterday: int, this_week: int, this_month: int}
     * }
     */
    protected function getRegistrationStats(): array
    {
        return [
            'started' => [
                'today' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => SalesTaxRegistration::whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
            ],
            'paid' => [
                'today' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => SalesTaxRegistration::whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
            ],
        ];
    }

    /**
     * Get the last 20 sales tax registrations with payer and amount info.
     */
    protected function getRecentRegistrations(): Collection
    {
        return SalesTaxRegistration::query()
            // Select only the columns we render. Avoids pulling large JSON
            // columns (core_data, definition_snapshot) into the sort buffer,
            // which can exhaust MySQL's sort memory on big tables.
            ->select(['id', 'business_id', 'selected_states', 'status', 'paid_at', 'created_at'])
            ->with(['business.users', 'payment'])
            // Order by the indexed primary key instead of created_at to avoid
            // a filesort; id order matches insertion (recency) order.
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (SalesTaxRegistration $registration) {
                $user = $registration->business?->users->first();

                return [
                    'id' => $registration->id,
                    'business' => $registration->business?->name ?? 'Unknown',
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'states' => $registration->stateCount(),
                    'amount' => $registration->payment?->formattedAmount(),
                    'status' => $registration->display_status,
                    'paid_at' => $registration->paid_at,
                    'created_at' => $registration->created_at,
                ];
            });
    }

    /**
     * Format an amount in cents as a USD string.
     */
    public function formatCents(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}

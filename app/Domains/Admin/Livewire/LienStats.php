<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class LienStats extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    public function render(): View
    {
        return view('admin.lien-stats', [
            'revenueStats' => $this->getRevenueStats(),
            'filingStats' => $this->getFilingStats(),
            'recentFilings' => $this->getRecentFilings(),
        ])->layout('layouts.admin', ['title' => 'Lien Stats']);
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
     * Get lien-product revenue (in cents) for different periods (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getRevenueStats(): array
    {
        return [
            'today' => $this->sumLienRevenue('today'),
            'yesterday' => $this->sumLienRevenue('yesterday'),
            'this_week' => $this->sumLienRevenue('this_week'),
            'this_month' => $this->sumLienRevenue('this_month'),
        ];
    }

    /**
     * Sum succeeded lien-product payment amounts for an EST period.
     */
    protected function sumLienRevenue(string $period): int
    {
        return (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('price', fn ($query) => $query->where('product_family', 'lien'))
            ->whereBetween('paid_at', $this->getEstDateRange($period))
            ->sum('amount_cents');
    }

    /**
     * Get filing counts (started vs paid) for periods (EST).
     *
     * @return array{
     *     started: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     paid: array{today: int, yesterday: int, this_week: int, this_month: int}
     * }
     */
    protected function getFilingStats(): array
    {
        return [
            'started' => [
                'today' => $this->filingsQuery()->whereBetween('created_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => $this->filingsQuery()->whereBetween('created_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => $this->filingsQuery()->whereBetween('created_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => $this->filingsQuery()->whereBetween('created_at', $this->getEstDateRange('this_month'))->count(),
            ],
            'paid' => [
                'today' => $this->filingsQuery()->whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('today'))->count(),
                'yesterday' => $this->filingsQuery()->whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('yesterday'))->count(),
                'this_week' => $this->filingsQuery()->whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_week'))->count(),
                'this_month' => $this->filingsQuery()->whereNotNull('paid_at')->whereBetween('paid_at', $this->getEstDateRange('this_month'))->count(),
            ],
        ];
    }

    /**
     * Get the last 20 lien filings with payer, document type, and amount info.
     */
    protected function getRecentFilings(): Collection
    {
        return $this->filingsQuery()
            ->with(['business.users', 'documentType', 'payment'])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (LienFiling $filing) {
                $user = $filing->business?->users->first();

                return [
                    'id' => $filing->id,
                    'business' => $filing->business?->name ?? 'Unknown',
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'document' => $filing->documentType?->name ?? '—',
                    'state' => $filing->jurisdiction_state ?? '—',
                    'amount' => $filing->payment?->formattedAmount(),
                    'status_label' => $filing->status->label(),
                    'status_color' => $filing->status->color(),
                    'paid_at' => $filing->paid_at,
                    'created_at' => $filing->created_at,
                ];
            });
    }

    /**
     * Base query for filings across all businesses (admin view bypasses the
     * per-business global scope).
     */
    protected function filingsQuery()
    {
        return LienFiling::query()->withoutGlobalScope('business');
    }

    /**
     * Format an amount in cents as a USD string.
     */
    public function formatCents(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}

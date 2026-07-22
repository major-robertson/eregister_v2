<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienWaiver;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Cashier\Subscription;
use Livewire\Component;

class LienStats extends Component
{
    private const STATS_TIMEZONE = 'America/New_York';

    /** The four periods every count/revenue card breaks down by. */
    private const PERIODS = ['today', 'yesterday', 'this_week', 'this_month'];

    public function render(): View
    {
        $waiverSubscriptions = $this->getWaiverSubscriptionData();

        return view('admin.lien-stats', [
            'revenueStats' => $this->getRevenueStats(),
            'filingStats' => $this->getFilingStats(),
            'recentFilings' => $this->getRecentFilings(),
            'waiverRevenueStats' => $this->getWaiverRevenueStats(),
            'waiverStats' => $this->getWaiverStats(),
            'waiverPipeline' => $this->getWaiverPipeline(),
            'waiverMix' => $this->getWaiverMix(),
            'waiverSubscriptionStats' => $waiverSubscriptions['stats'],
            'waiverSubscriptionRows' => $waiverSubscriptions['rows'],
            'recentWaivers' => $this->getRecentWaivers(),
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
     * Lien-waiver subscription revenue (in cents) for the four periods (EST).
     * This is a subset of getRevenueStats(), which sums the whole lien family.
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function getWaiverRevenueStats(): array
    {
        return $this->byPeriod(fn (string $period) => (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereHas('price', fn ($query) => $query
                ->where('product_family', 'lien')
                ->where('product_key', 'lien_waiver')
            )
            ->whereBetween('paid_at', $this->getEstDateRange($period))
            ->sum('amount_cents'));
    }

    /**
     * Waiver volume: saved (created) vs signed, per period (EST).
     *
     * @return array{
     *     created: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     signed: array{today: int, yesterday: int, this_week: int, this_month: int},
     *     sent: array{today: int, yesterday: int, this_week: int, this_month: int}
     * }
     */
    protected function getWaiverStats(): array
    {
        return [
            'created' => $this->countWaiversByPeriod('created_at'),
            'sent' => $this->countWaiversByPeriod('sent_at'),
            'signed' => $this->countWaiversByPeriod('signed_at'),
        ];
    }

    /**
     * Count waivers whose $column timestamp falls in each period (EST).
     *
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function countWaiversByPeriod(string $column): array
    {
        return $this->byPeriod(fn (string $period) => $this->waiverCountsQuery()
            ->whereNotNull($column)
            ->whereBetween($column, $this->getEstDateRange($period))
            ->count());
    }

    /**
     * All-time waiver counts per status, in enum order, for the pipeline card.
     *
     * @return list<array{label: string, color: string, count: int}>
     */
    protected function getWaiverPipeline(): array
    {
        $counts = $this->waiverCountsQuery()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return collect(WaiverStatus::cases())
            ->map(fn (WaiverStatus $status) => [
                'label' => $status->label(),
                'color' => $status->color(),
                'count' => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();
    }

    /**
     * All-time product mix: which side of the exchange waivers are for, how
     * they got here (our engine vs an uploaded outside copy), and the states
     * seeing the most volume.
     *
     * @return array{
     *     directions: list<array{label: string, count: int}>,
     *     sources: array{generated: int, uploaded: int},
     *     top_states: Collection<string, int>
     * }
     */
    protected function getWaiverMix(): array
    {
        $byDirection = $this->waiverCountsQuery()
            ->selectRaw('direction, COUNT(*) as aggregate')
            ->groupBy('direction')
            ->pluck('aggregate', 'direction');

        $bySource = $this->waiverCountsQuery()
            ->selectRaw('source, COUNT(*) as aggregate')
            ->groupBy('source')
            ->pluck('aggregate', 'source');

        $topStates = $this->waiverCountsQuery()
            ->selectRaw('state, COUNT(*) as aggregate')
            ->groupBy('state')
            ->orderByDesc('aggregate')
            ->limit(5)
            ->pluck('aggregate', 'state')
            ->map(fn ($count) => (int) $count);

        return [
            'directions' => collect(WaiverDirection::cases())
                ->map(fn (WaiverDirection $direction) => [
                    'label' => $direction->label(),
                    'count' => (int) ($byDirection[$direction->value] ?? 0),
                ])
                ->all(),
            'sources' => [
                'generated' => (int) ($bySource['generated'] ?? 0),
                'uploaded' => (int) ($bySource['uploaded'] ?? 0),
            ],
            'top_states' => $topStates,
        ];
    }

    /**
     * Waiver subscription rollup and the newest subscriptions, computed from
     * one query so the card and the table can't disagree.
     *
     * Seats are the billed quantity (WaiverSeats keeps quantity == assigned
     * seats), and MRR normalizes yearly plans to a monthly figure.
     *
     * @return array{
     *     stats: array{active: int, seats: int, mrr_cents: int, new_this_month: int, cancelling: int},
     *     rows: Collection<int, array<string, mixed>>
     * }
     */
    protected function getWaiverSubscriptionData(): array
    {
        $subscriptions = Subscription::query()
            ->where('type', config('lien_waivers.subscription_type'))
            ->orderByDesc('id')
            ->get();

        $priceMap = $this->waiverPriceMap();
        $fallbackMonthly = (int) config('lien_waivers.prices.monthly.amount_cents');

        $seatsOf = fn (Subscription $subscription): int => max(1, (int) $subscription->quantity);
        $mrrOf = fn (Subscription $subscription): int => (int) (
            $priceMap[$subscription->stripe_price]['monthly_cents'] ?? $fallbackMonthly
        ) * $seatsOf($subscription);

        $active = $subscriptions->filter(fn (Subscription $subscription) => $subscription->active());

        [$monthStart, $monthEnd] = $this->getEstDateRange('this_month');

        $businessNames = Business::query()
            ->whereIn('id', $subscriptions->pluck('business_id')->unique()->all())
            ->pluck('name', 'id');

        return [
            'stats' => [
                'active' => $active->count(),
                'seats' => (int) $active->sum($seatsOf),
                'mrr_cents' => (int) $active->sum($mrrOf),
                'new_this_month' => $subscriptions
                    ->filter(fn (Subscription $subscription) => $subscription->created_at?->between($monthStart, $monthEnd))
                    ->count(),
                'cancelling' => $subscriptions
                    ->filter(fn (Subscription $subscription) => $subscription->onGracePeriod())
                    ->count(),
            ],
            'rows' => $subscriptions->take(20)->map(function (Subscription $subscription) use ($businessNames, $priceMap, $seatsOf, $mrrOf) {
                $status = $this->subscriptionStatus($subscription);

                return [
                    'id' => $subscription->id,
                    'business' => $businessNames[$subscription->business_id] ?? 'Unknown',
                    'seats' => $seatsOf($subscription),
                    'plan' => ($priceMap[$subscription->stripe_price]['interval'] ?? 'month') === 'year'
                        ? 'Yearly'
                        : 'Monthly',
                    'mrr' => $this->formatCents($mrrOf($subscription)),
                    'status_label' => $status['label'],
                    'status_color' => $status['color'],
                    'ends_at' => $subscription->ends_at,
                    'created_at' => $subscription->created_at,
                ];
            })->values(),
        ];
    }

    /**
     * Map every waiver Stripe price ID (test and live, so the page reads right
     * in either environment) to its monthly-normalized amount and interval.
     *
     * @return array<string, array{monthly_cents: int, interval: string}>
     */
    protected function waiverPriceMap(): array
    {
        $map = [];

        $prices = Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->get();

        foreach ($prices as $price) {
            $months = max(1, (int) ($price->interval_count ?: 1)) * ($price->interval === 'year' ? 12 : 1);

            $entry = [
                'monthly_cents' => (int) round(((int) $price->amount_cents) / $months),
                'interval' => (string) ($price->interval ?: 'month'),
            ];

            foreach ([$price->stripe_price_id_test, $price->stripe_price_id_live] as $stripePriceId) {
                if ($stripePriceId) {
                    $map[$stripePriceId] = $entry;
                }
            }
        }

        return $map;
    }

    /**
     * Display status for a subscription row. Grace-period subscriptions are
     * still active but on their way out, so they get their own label.
     *
     * @return array{label: string, color: string}
     */
    protected function subscriptionStatus(Subscription $subscription): array
    {
        if ($subscription->onGracePeriod()) {
            return ['label' => 'Cancelling', 'color' => 'amber'];
        }

        if ($subscription->ended()) {
            return ['label' => 'Ended', 'color' => 'zinc'];
        }

        if ($subscription->pastDue()) {
            return ['label' => 'Past Due', 'color' => 'red'];
        }

        if ($subscription->active()) {
            return ['label' => 'Active', 'color' => 'green'];
        }

        return ['label' => ucfirst(str_replace('_', ' ', (string) $subscription->stripe_status)), 'color' => 'zinc'];
    }

    /**
     * Get the last 20 waivers with the account, author, and document info.
     */
    protected function getRecentWaivers(): Collection
    {
        return $this->waiversQuery()
            ->with(['business.users', 'createdBy'])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (LienWaiver $waiver) {
                // The author is the truthful "who did this"; fall back to the
                // account's first member for waivers created before the column
                // was populated (or whose author has since been deleted).
                $user = $waiver->createdBy ?? $waiver->business?->users->first();

                return [
                    'id' => $waiver->id,
                    'business' => $waiver->business?->name ?? 'Unknown',
                    'name' => $user?->name ?? 'Unknown',
                    'email' => $user?->email ?? 'Unknown',
                    'kind' => $waiver->kind->shortLabel(),
                    'direction' => $waiver->direction->label(),
                    'state' => $waiver->state ?? '—',
                    'amount' => $waiver->formattedAmount(),
                    'status_label' => $waiver->status->label(),
                    'status_color' => $waiver->status->color(),
                    'signed_at' => $waiver->signed_at,
                    'created_at' => $waiver->created_at,
                ];
            });
    }

    /**
     * Base query for waivers across all businesses (admin view bypasses the
     * per-business global scope, which would otherwise pin results to whatever
     * business the signed-in admin happens to be on).
     */
    protected function waiversQuery(): Builder
    {
        return LienWaiver::query()->withoutGlobalScope('business');
    }

    /**
     * Aggregate query for waiver counts. Soft-deleted waivers are included:
     * saving one consumes a free-tier slot (see LienWaiver::savedThisMonthFor),
     * so deleting it afterwards must not rewrite the volume history.
     */
    protected function waiverCountsQuery(): Builder
    {
        return $this->waiversQuery()->withTrashed();
    }

    /**
     * Run a per-period callback over the four EST periods.
     *
     * @param  callable(string): int  $callback
     * @return array{today: int, yesterday: int, this_week: int, this_month: int}
     */
    protected function byPeriod(callable $callback): array
    {
        return collect(self::PERIODS)
            ->mapWithKeys(fn (string $period) => [$period => $callback($period)])
            ->all();
    }

    /**
     * Format an amount in cents as a USD string.
     */
    public function formatCents(int $cents): string
    {
        return '$'.number_format($cents / 100, 2);
    }
}

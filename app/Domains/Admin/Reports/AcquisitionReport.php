<?php

namespace App\Domains\Admin\Reports;

use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Sign-ups, paying customers and revenue by first-touch channel and landing
 * page, per Eastern month, so SEO work can be measured against a baseline.
 *
 * Channel comes from the attribution stored on the user at sign-up:
 *  - ads:      a Google Ads click id, or utm_medium cpc / ppc / paid
 *  - campaign: a utm_campaign without those (email, partner, QR links)
 *  - direct:   no external referrer and no landing path
 *  - organic:  everything else, split by landing section (the first path
 *              segment: /liens/texas counts under /liens, / is "home")
 *
 * A business counts for the month its owner signed up. Revenue is the
 * business's lifetime succeeded payments, not only that month's. In
 * production only live-mode payments count.
 */
class AcquisitionReport
{
    public const CHANNELS = ['organic', 'ads', 'campaign', 'direct'];

    private const ADS_MEDIUMS = ['cpc', 'ppc', 'paid'];

    private bool $liveOnly;

    public function __construct(?bool $liveOnly = null)
    {
        $this->liveOnly = $liveOnly ?? app()->isProduction();
    }

    /**
     * One row per month, channel and (for organic) landing section, newest
     * month first. Months with no sign-ups have no rows.
     *
     * @return list<array{month: string, channel: string, section: ?string, signups: int, converted: int, paying_businesses: int, revenue_cents: int, conversion_rate: float}>
     */
    public function matrix(int $months = 12, ?string $channel = null): array
    {
        [$monthSql, $monthBindings] = $this->monthExpression($months);
        $channelSql = $this->channelExpression();

        $rows = $this->baseQuery($months, $channel)
            ->selectRaw("{$monthSql} as month", $monthBindings)
            ->selectRaw("{$channelSql} as channel")
            ->selectRaw("case when {$channelSql} = 'organic' then {$this->sectionExpression()} end as section")
            ->tap(fn (Builder $query) => $this->selectMetrics($query))
            ->groupBy('month', 'channel', 'section')
            ->get();

        return $rows
            ->map(fn (object $row) => $this->formatRow($row, [
                'month' => (string) $row->month,
                'channel' => (string) $row->channel,
                'section' => $row->section,
            ]))
            ->sort(fn (array $a, array $b) => [$b['month'], $this->channelOrder($a['channel']), $b['signups'], (string) $a['section']]
                <=> [$a['month'], $this->channelOrder($b['channel']), $a['signups'], (string) $b['section']])
            ->values()
            ->all();
    }

    /**
     * Landing paths with the most sign-ups in the window, with their paying
     * businesses and lifetime revenue.
     *
     * @return list<array{landing_path: string, signups: int, converted: int, paying_businesses: int, revenue_cents: int, conversion_rate: float}>
     */
    public function topLandingPaths(int $months = 12, ?string $channel = null, int $limit = 20): array
    {
        return $this->baseQuery($months, $channel)
            ->selectRaw("coalesce(nullif(users.signup_landing_path, ''), '(none)') as landing_path")
            ->tap(fn (Builder $query) => $this->selectMetrics($query))
            ->groupBy('landing_path')
            ->orderByDesc('signups')
            ->orderByDesc('revenue_cents')
            ->orderBy('landing_path')
            ->limit($limit)
            ->get()
            ->map(fn (object $row) => $this->formatRow($row, ['landing_path' => (string) $row->landing_path]))
            ->all();
    }

    /**
     * Start of the window: the first day of the Eastern month $months - 1
     * months before this one.
     */
    public function windowStart(int $months): CarbonImmutable
    {
        return $this->monthStarts($months)[0];
    }

    /**
     * Users who signed up in the window, each joined to the businesses they
     * are the (first) owner of and those businesses' lifetime revenue.
     */
    private function baseQuery(int $months, ?string $channel): Builder
    {
        $owners = DB::table('business_user')
            ->where('role', 'owner')
            ->groupBy('business_id')
            ->select('business_id', DB::raw('min(user_id) as user_id'));

        $revenue = DB::table('payments')
            ->where('status', PaymentStatus::Succeeded->value)
            ->when($this->liveOnly, fn (Builder $query) => $query->where('livemode', true))
            ->groupBy('business_id')
            ->select('business_id', DB::raw('sum(amount_cents) as revenue_cents'));

        return DB::table('users')
            ->leftJoinSub($owners, 'owned', 'owned.user_id', '=', 'users.id')
            ->leftJoinSub($revenue, 'paid', 'paid.business_id', '=', 'owned.business_id')
            ->where('users.created_at', '>=', $this->windowStart($months)->utc())
            ->when($channel !== null, fn (Builder $query) => $query->whereRaw("({$this->channelExpression()}) = ?", [$channel]));
    }

    private function selectMetrics(Builder $query): void
    {
        $query
            ->selectRaw('count(distinct users.id) as signups')
            ->selectRaw('count(distinct case when paid.business_id is not null then users.id end) as converted')
            ->selectRaw('count(distinct paid.business_id) as paying_businesses')
            ->selectRaw('coalesce(sum(paid.revenue_cents), 0) as revenue_cents');
    }

    /**
     * @param  array<string, mixed>  $keys
     * @return array<string, mixed>
     */
    private function formatRow(object $row, array $keys): array
    {
        $signups = (int) $row->signups;
        $converted = (int) $row->converted;

        return $keys + [
            'signups' => $signups,
            'converted' => $converted,
            'paying_businesses' => (int) $row->paying_businesses,
            'revenue_cents' => (int) $row->revenue_cents,
            'conversion_rate' => $signups > 0 ? round($converted / $signups * 100, 1) : 0.0,
        ];
    }

    private function channelOrder(string $channel): int
    {
        $index = array_search($channel, self::CHANNELS, true);

        return $index === false ? count(self::CHANNELS) : $index;
    }

    /**
     * SQL for the channel of a user, from the first-touch columns.
     */
    private function channelExpression(): string
    {
        $mediums = "'".implode("', '", self::ADS_MEDIUMS)."'";

        return "case
            when coalesce(users.signup_gclid, '') <> '' or lower(coalesce(users.signup_utm_medium, '')) in ({$mediums}) then 'ads'
            when coalesce(users.signup_utm_campaign, '') <> '' then 'campaign'
            when coalesce(users.signup_referrer, '') = '' and coalesce(users.signup_landing_path, '') = '' then 'direct'
            else 'organic'
        end";
    }

    /**
     * SQL for the landing section: the first path segment, "home" for "/".
     */
    private function sectionExpression(): string
    {
        $segment = "lower(substring_index(trim(leading '/' from users.signup_landing_path), '/', 1))";

        return "case
            when coalesce(users.signup_landing_path, '') = '' then '(none)'
            when {$segment} = '' then 'home'
            else concat('/', {$segment})
        end";
    }

    /**
     * SQL that buckets users.created_at (UTC) into Eastern months ("Y-m"),
     * using boundaries computed here so MySQL needs no time zone tables.
     *
     * @return array{0: string, 1: list<string>}
     */
    private function monthExpression(int $months): array
    {
        $cases = [];
        $bindings = [];

        foreach (array_reverse($this->monthStarts($months)) as $start) {
            $cases[] = 'when users.created_at >= ? then ?';
            $bindings[] = $start->utc()->toDateTimeString();
            $bindings[] = $start->format('Y-m');
        }

        return ['case '.implode(' ', $cases).' end', $bindings];
    }

    /**
     * Starts of the Eastern months in the window, oldest first.
     *
     * @return list<CarbonImmutable>
     */
    private function monthStarts(int $months): array
    {
        $current = CarbonImmutable::now(config('app.display_timezone', 'America/New_York'))->startOfMonth();

        return collect(range(max(1, $months) - 1, 0))
            ->map(fn (int $back) => $current->subMonthsNoOverflow($back))
            ->all();
    }
}

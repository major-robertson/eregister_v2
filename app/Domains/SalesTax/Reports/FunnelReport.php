<?php

namespace App\Domains\SalesTax\Reports;

use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * The sales tax registration funnel in numbers, for one period and the
 * period before it: sign-ups from the sales tax and resale pages, drafts,
 * paid registrations (states, rush, revenue), submissions, generator
 * subscriptions, and paid registrations by the Google Ads campaign that
 * brought the customer in. Ads spend is not available here, so the report
 * links to the campaign table for the same dates; cost per paid = spend ÷ paid.
 */
class FunnelReport
{
    /** Landing paths that count as the sales tax / resale funnel. */
    private const PATHS = ['/sales-tax-registration', '/lp/sales-tax', '/resale-certificates', '/lp/resale-certificate'];

    /**
     * @return array{
     *     days: int,
     *     current: array{start: CarbonImmutable, end: CarbonImmutable, metrics: array<string, int>},
     *     previous: array{start: CarbonImmutable, end: CarbonImmutable, metrics: array<string, int>},
     *     by_campaign: array<string, int>,
     * }
     */
    public function build(int $days = 7): array
    {
        // Whole Eastern days, today excluded.
        $end = CarbonImmutable::now('America/New_York')->startOfDay();
        $start = $end->subDays($days);
        $previousStart = $start->subDays($days);

        return [
            'days' => $days,
            'current' => ['start' => $start, 'end' => $end, 'metrics' => $this->metrics($start, $end)],
            'previous' => ['start' => $previousStart, 'end' => $start, 'metrics' => $this->metrics($previousStart, $start)],
            'by_campaign' => $this->paidByCampaign($start, $end),
        ];
    }

    /**
     * Labels in display order.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'signups' => 'Sign-ups from the sales tax and resale pages',
            'signups_ads' => '  of which from Google Ads (utm_medium=cpc)',
            'drafts' => 'Registrations started (states picked)',
            'paid' => 'Paid registrations',
            'paid_ads' => '  of which from Google Ads',
            'paid_states' => 'States paid for',
            'rush' => 'Rush orders',
            'revenue' => 'Registration revenue ($)',
            'submitted' => 'Questions submitted',
            'resale_subscriptions' => 'Resale certificate subscriptions started',
            'registration_subscribers' => '  of which by businesses with a paid registration',
            'certificates_generated' => 'Resale certificates generated',
        ];
    }

    /**
     * @return array<string, int>
     */
    private function metrics(CarbonImmutable $start, CarbonImmutable $end): array
    {
        [$from, $to] = [$start->utc(), $end->utc()];

        $funnelUsers = fn () => User::query()
            ->whereBetween('created_at', [$from, $to])
            ->where(function ($query) {
                foreach (self::PATHS as $path) {
                    $query->orWhere('signup_landing_path', 'like', $path.'%');
                }
            });

        $paid = FormApplication::forList()
            ->where('form_type', 'sales_tax_permit')
            ->whereBetween('paid_at', [$from, $to])
            ->get();

        $paidFromAds = FormApplication::query()
            ->where('form_type', 'sales_tax_permit')
            ->whereBetween('paid_at', [$from, $to])
            ->whereHas('createdBy', fn ($query) => $query->where('signup_utm_medium', 'cpc'))
            ->count();

        $revenueCents = (int) Payment::query()
            ->where('status', PaymentStatus::Succeeded)
            ->whereBetween('paid_at', [$from, $to])
            ->whereHas('price', fn ($query) => $query->where('product_family', 'tax'))
            ->sum('amount_cents');

        return [
            'signups' => $funnelUsers()->count(),
            'signups_ads' => $funnelUsers()->where('signup_utm_medium', 'cpc')->count(),
            'drafts' => FormApplication::query()
                ->where('form_type', 'sales_tax_permit')
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'paid' => $paid->count(),
            'paid_ads' => $paidFromAds,
            'paid_states' => $paid->sum(fn (FormApplication $application) => $application->stateCount()),
            'rush' => $paid->whereNotNull('rush_requested_at')->count(),
            'revenue' => intdiv($revenueCents, 100),
            'submitted' => FormApplication::query()
                ->where('form_type', 'sales_tax_permit')
                ->whereBetween('submitted_at', [$from, $to])
                ->count(),
            'resale_subscriptions' => DB::table('subscriptions')
                ->where('type', config('resale_cert.subscription_type'))
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            // Registration customers who went on to the generator: the
            // registration -> subscriber path the offer is meant to grow.
            'registration_subscribers' => DB::table('subscriptions')
                ->where('type', config('resale_cert.subscription_type'))
                ->whereBetween('created_at', [$from, $to])
                ->whereIn('business_id', FormApplication::query()
                    ->where('form_type', 'sales_tax_permit')
                    ->whereNotNull('paid_at')
                    ->select('business_id'))
                ->count(),
            'certificates_generated' => DB::table('resale_certificates')
                ->whereBetween('created_at', [$from, $to])
                ->count(),
        ];
    }

    /**
     * Paid registrations in the period, by the Google Ads campaign id the
     * customer signed up under (from the final URL suffix's utm_campaign).
     *
     * @return array<string, int>
     */
    private function paidByCampaign(CarbonImmutable $start, CarbonImmutable $end): array
    {
        return FormApplication::query()
            ->where('form_type', 'sales_tax_permit')
            ->whereBetween('form_applications.paid_at', [$start->utc(), $end->utc()])
            ->join('users', 'users.id', '=', 'form_applications.created_by_user_id')
            ->where('users.signup_utm_medium', 'cpc')
            ->groupBy('users.signup_utm_campaign')
            ->orderByDesc('paid')
            ->selectRaw("coalesce(users.signup_utm_campaign, '(no campaign)') as campaign, count(*) as paid")
            ->pluck('paid', 'campaign')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}

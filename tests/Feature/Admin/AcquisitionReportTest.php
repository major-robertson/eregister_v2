<?php

use App\Domains\Admin\Livewire\AcquisitionStats;
use App\Domains\Admin\Reports\AcquisitionReport;
use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;

/**
 * A user who signed up at the given UTC time with the given first-touch
 * attribution. created_at is stamped after the insert.
 *
 * @param  array<string, mixed>  $attribution
 */
function acquisitionSignup(string $createdAt, array $attribution = []): User
{
    $user = User::factory()->create();
    $user->forceFill(array_merge([
        'signup_landing_path' => null,
        'signup_referrer' => null,
        'signup_utm_source' => null,
        'signup_utm_medium' => null,
        'signup_utm_campaign' => null,
        'signup_gclid' => null,
    ], $attribution, ['created_at' => CarbonImmutable::parse($createdAt, 'UTC')]))->saveQuietly();

    return $user;
}

/**
 * A business owned by the user, with payments of the given amounts and status.
 *
 * @param  list<int>  $amounts
 */
function acquisitionBusiness(User $owner, array $amounts = [], PaymentStatus $status = PaymentStatus::Succeeded, bool $livemode = false): Business
{
    $business = Business::factory()->create();
    $business->users()->attach($owner->id, ['role' => 'owner']);

    foreach ($amounts as $amount) {
        Payment::factory()->create([
            'business_id' => $business->id,
            'amount_cents' => $amount,
            'status' => $status,
            'livemode' => $livemode,
            'paid_at' => $status === PaymentStatus::Succeeded ? now() : null,
        ]);
    }

    return $business;
}

/**
 * The matrix row for a month / channel / section, or null.
 *
 * @param  list<array<string, mixed>>  $matrix
 * @return array<string, mixed>|null
 */
function acquisitionRow(array $matrix, string $month, string $channel, ?string $section = null): ?array
{
    return collect($matrix)->first(fn (array $row) => $row['month'] === $month
        && $row['channel'] === $channel
        && $row['section'] === $section);
}

beforeEach(function () {
    // Mid-June 2026 Eastern: the 12-month window starts July 1, 2025.
    $this->travelTo(CarbonImmutable::parse('2026-06-15 16:00:00', 'UTC'));
});

describe('channel derivation', function () {
    it('derives ads, campaign, direct and organic sections from the first-touch fields', function () {
        $at = '2026-06-10 15:00:00';

        acquisitionSignup($at, ['signup_gclid' => 'Cj0KCQ', 'signup_landing_path' => '/liens']);
        acquisitionSignup($at, ['signup_utm_medium' => 'CPC', 'signup_utm_campaign' => '123']);
        acquisitionSignup($at, ['signup_utm_medium' => 'paid', 'signup_referrer' => 'https://www.reddit.com/']);
        acquisitionSignup($at, ['signup_utm_medium' => 'ppc']);
        acquisitionSignup($at, ['signup_utm_campaign' => 'spring-mailer', 'signup_utm_medium' => 'email', 'signup_landing_path' => '/liens']);
        acquisitionSignup($at);
        acquisitionSignup($at, ['signup_landing_path' => '/liens/texas', 'signup_referrer' => 'https://www.google.com/']);
        acquisitionSignup($at, ['signup_landing_path' => '/liens']);
        acquisitionSignup($at, ['signup_landing_path' => '/Liens/florida/']);
        acquisitionSignup($at, ['signup_landing_path' => '/']);
        acquisitionSignup($at, ['signup_landing_path' => '/resale-certificates/california']);
        acquisitionSignup($at, ['signup_landing_path' => '/llc']);
        acquisitionSignup($at, ['signup_referrer' => 'https://chatgpt.com/']);

        $matrix = (new AcquisitionReport)->matrix();

        expect(acquisitionRow($matrix, '2026-06', 'ads')['signups'])->toBe(4)
            ->and(acquisitionRow($matrix, '2026-06', 'campaign')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-06', 'direct')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-06', 'organic', '/liens')['signups'])->toBe(3)
            ->and(acquisitionRow($matrix, '2026-06', 'organic', 'home')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-06', 'organic', '/resale-certificates')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-06', 'organic', '/llc')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-06', 'organic', '(none)')['signups'])->toBe(1)
            ->and(collect($matrix)->sum('signups'))->toBe(13);

        // Organic rows come first, the biggest section first.
        expect($matrix[0]['channel'])->toBe('organic')
            ->and($matrix[0]['section'])->toBe('/liens');
    });
});

describe('monthly counts', function () {
    it('buckets sign-ups by Eastern month and leaves out sign-ups before the window', function () {
        acquisitionSignup('2026-06-02 12:00:00', ['signup_landing_path' => '/liens']);
        // 23:00 EDT on May 31: May, not June.
        acquisitionSignup('2026-06-01 03:00:00', ['signup_landing_path' => '/liens']);
        acquisitionSignup('2026-05-10 12:00:00', ['signup_landing_path' => '/liens']);
        acquisitionSignup('2025-07-01 12:00:00', ['signup_landing_path' => '/liens']);
        // Before the 12-month window.
        acquisitionSignup('2025-06-20 12:00:00', ['signup_landing_path' => '/liens']);

        $matrix = (new AcquisitionReport)->matrix();

        expect(acquisitionRow($matrix, '2026-06', 'organic', '/liens')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2026-05', 'organic', '/liens')['signups'])->toBe(2)
            ->and(acquisitionRow($matrix, '2025-07', 'organic', '/liens')['signups'])->toBe(1)
            ->and(acquisitionRow($matrix, '2025-06', 'organic', '/liens'))->toBeNull()
            ->and(collect($matrix)->pluck('month')->all())->toBe(['2026-06', '2026-05', '2025-07']);

        expect(collect((new AcquisitionReport)->matrix(2))->sum('signups'))->toBe(3);
    });
});

describe('paying businesses and revenue', function () {
    it('counts businesses with a succeeded payment and sums their lifetime revenue', function () {
        $paid = acquisitionSignup('2026-05-10 12:00:00', ['signup_landing_path' => '/liens/texas']);
        $business = acquisitionBusiness($paid, [10000, 5000]);
        // Failed and refunded payments are not revenue.
        Payment::factory()->create(['business_id' => $business->id, 'amount_cents' => 9999, 'status' => PaymentStatus::Failed]);
        Payment::factory()->create(['business_id' => $business->id, 'amount_cents' => 7777, 'status' => PaymentStatus::Refunded]);

        // A second paying business for the same owner.
        acquisitionBusiness($paid, [2500]);

        // A sign-up whose business never paid.
        $unpaid = acquisitionSignup('2026-05-11 12:00:00', ['signup_landing_path' => '/liens']);
        acquisitionBusiness($unpaid, [4000], PaymentStatus::Initiated);

        // A sign-up with no business at all.
        acquisitionSignup('2026-05-12 12:00:00', ['signup_landing_path' => '/liens']);

        // A member (not owner) who joined a paying business is not credited.
        $member = acquisitionSignup('2026-05-13 12:00:00', ['signup_landing_path' => '/liens']);
        $business->users()->attach($member->id, ['role' => 'member']);

        // Revenue paid months later still counts for the sign-up month.
        $adsUser = acquisitionSignup('2025-12-01 17:00:00', ['signup_gclid' => 'abc']);
        acquisitionBusiness($adsUser, [30000]);

        $matrix = (new AcquisitionReport)->matrix();
        $liens = acquisitionRow($matrix, '2026-05', 'organic', '/liens');

        expect($liens['signups'])->toBe(4)
            ->and($liens['paying_businesses'])->toBe(2)
            ->and($liens['converted'])->toBe(1)
            ->and($liens['revenue_cents'])->toBe(17500)
            ->and($liens['conversion_rate'])->toBe(25.0);

        $ads = acquisitionRow($matrix, '2025-12', 'ads');

        expect($ads['signups'])->toBe(1)
            ->and($ads['paying_businesses'])->toBe(1)
            ->and($ads['revenue_cents'])->toBe(30000)
            ->and($ads['conversion_rate'])->toBe(100.0);
    });

    it('counts only live-mode payments when asked to', function () {
        $user = acquisitionSignup('2026-06-03 12:00:00', ['signup_landing_path' => '/llc']);
        acquisitionBusiness($user, [10000], livemode: false);
        acquisitionBusiness($user, [20000], livemode: true);

        $all = acquisitionRow((new AcquisitionReport)->matrix(), '2026-06', 'organic', '/llc');
        $live = acquisitionRow((new AcquisitionReport(liveOnly: true))->matrix(), '2026-06', 'organic', '/llc');

        expect($all['revenue_cents'])->toBe(30000)
            ->and($all['paying_businesses'])->toBe(2)
            ->and($live['revenue_cents'])->toBe(20000)
            ->and($live['paying_businesses'])->toBe(1);
    });
});

describe('top landing paths', function () {
    it('orders landing paths by sign-ups, then revenue', function () {
        foreach (range(1, 3) as $i) {
            acquisitionSignup('2026-06-0'.$i.' 12:00:00', ['signup_landing_path' => '/liens/texas']);
        }

        $resale = acquisitionSignup('2026-04-01 12:00:00', ['signup_landing_path' => '/resale-certificates']);
        acquisitionBusiness($resale, [9900]);
        acquisitionSignup('2026-04-02 12:00:00', ['signup_landing_path' => '/resale-certificates']);

        $llc = acquisitionSignup('2026-04-03 12:00:00', ['signup_landing_path' => '/llc']);
        acquisitionBusiness($llc, [50000]);
        acquisitionSignup('2026-04-04 12:00:00', ['signup_landing_path' => '/llc']);

        acquisitionSignup('2026-04-05 12:00:00');
        acquisitionSignup('2026-04-06 12:00:00', ['signup_gclid' => 'xyz', 'signup_landing_path' => '/lp/sales-tax']);

        $paths = (new AcquisitionReport)->topLandingPaths();

        expect(collect($paths)->pluck('landing_path')->all())
            ->toBe(['/liens/texas', '/llc', '/resale-certificates', '(none)', '/lp/sales-tax'])
            ->and($paths[0]['signups'])->toBe(3)
            ->and($paths[1]['revenue_cents'])->toBe(50000)
            ->and($paths[1]['paying_businesses'])->toBe(1);

        expect(collect((new AcquisitionReport)->topLandingPaths(channel: 'ads'))->pluck('landing_path')->all())
            ->toBe(['/lp/sales-tax'])
            ->and((new AcquisitionReport)->topLandingPaths(limit: 2))->toHaveCount(2);
    });

    it('filters the matrix by channel', function () {
        acquisitionSignup('2026-06-02 12:00:00', ['signup_landing_path' => '/liens']);
        acquisitionSignup('2026-06-02 12:00:00', ['signup_gclid' => 'abc']);

        $matrix = (new AcquisitionReport)->matrix(channel: 'ads');

        expect($matrix)->toHaveCount(1)
            ->and($matrix[0]['channel'])->toBe('ads')
            ->and($matrix[0]['section'])->toBeNull();
    });
});

describe('dashboard section', function () {
    it('renders on the stats dashboard for an admin', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $buyer = acquisitionSignup('2026-06-02 12:00:00', ['signup_landing_path' => '/resale-certificates/texas']);
        acquisitionBusiness($buyer, [12345]);

        $this->actingAs($admin)
            ->get(route('admin.stats'))
            ->assertSuccessful()
            ->assertSee('Acquisition by First Touch')
            ->assertSee('Top 20 Landing Paths')
            ->assertSee('/resale-certificates/texas')
            ->assertSee('$123.45');
    });

    it('is forbidden for a non-admin', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.stats'))
            ->assertForbidden();

        Livewire::actingAs($user)
            ->test(AcquisitionStats::class)
            ->assertForbidden();
    });

    it('filters by channel', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        acquisitionSignup('2026-06-02 12:00:00', ['signup_landing_path' => '/liens/ohio']);
        acquisitionSignup('2026-06-02 12:00:00', ['signup_gclid' => 'abc', 'signup_landing_path' => '/lp/liens']);

        Livewire::actingAs($admin)
            ->test(AcquisitionStats::class)
            ->assertSee('/liens/ohio')
            ->assertSee('/lp/liens')
            ->set('channel', 'ads')
            ->assertDontSee('/liens/ohio')
            ->assertSee('/lp/liens')
            ->set('channel', 'bogus')
            ->assertSet('channel', '')
            ->assertSee('/liens/ohio');
    });
});

describe('report:acquisition command', function () {
    it('prints the matrix and top paths as tables', function () {
        $buyer = acquisitionSignup('2026-05-10 12:00:00', ['signup_landing_path' => '/liens/texas']);
        acquisitionBusiness($buyer, [10000]);
        acquisitionSignup('2026-05-11 12:00:00', ['signup_landing_path' => '/liens/georgia']);

        $this->artisan('report:acquisition', ['--months' => 3])
            ->expectsTable(
                ['Month', 'Channel', 'Section', 'Signups', 'Paid businesses', 'Revenue', 'Conversion'],
                [['2026-05', 'organic', '/liens', 2, 1, '$100.00', '50.0%']],
            )
            ->expectsTable(
                ['Landing path', 'Signups', 'Paid businesses', 'Revenue', 'Conversion'],
                [
                    ['/liens/texas', 1, 1, '$100.00', '100.0%'],
                    ['/liens/georgia', 1, 0, '$0.00', '0.0%'],
                ],
            )
            ->assertSuccessful();
    });

    it('prints JSON with --json', function () {
        acquisitionSignup('2026-06-02 12:00:00', ['signup_utm_campaign' => 'fall-mailer']);

        expect(Artisan::call('report:acquisition', ['--json' => true]))->toBe(0);

        $report = json_decode(Artisan::output(), true);

        expect($report['since'])->toBe('2025-07-01')
            ->and($report['months'])->toBe(12)
            ->and($report['matrix'])->toHaveCount(1)
            ->and($report['matrix'][0])->toMatchArray([
                'month' => '2026-06',
                'channel' => 'campaign',
                'section' => null,
                'signups' => 1,
                'paying_businesses' => 0,
                'revenue_cents' => 0,
            ])
            ->and($report['top_landing_paths'][0]['landing_path'])->toBe('(none)');
    });
});

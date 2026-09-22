<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\Reports\FunnelReport;
use App\Enums\PaymentStatus;
use App\Mail\SalesTaxFunnelReportMail;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Support\Analytics\AdsTableState;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

/**
 * A paid sales tax registration for the report, created by the given user.
 *
 * @param  array<int, string>  $states
 * @param  array<string, mixed>  $overrides
 */
function reportedRegistration(Business $business, User $user, array $states, array $overrides = []): FormApplication
{
    // created_at is guarded, so it is stamped after the insert.
    $createdAt = $overrides['created_at'] ?? null;
    unset($overrides['created_at']);

    $application = FormApplication::create(array_merge([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $user->id,
    ], $overrides));

    if ($createdAt) {
        $application->forceFill(['created_at' => $createdAt])->saveQuietly();
    }

    return $application;
}

/**
 * A user who signed up on the given day from the given page.
 *
 * @param  array<string, mixed>  $attribution
 */
function reportedSignup(\DateTimeInterface $createdAt, array $attribution): User
{
    $user = User::factory()->create();
    $user->forceFill(array_merge($attribution, ['created_at' => $createdAt]))->saveQuietly();

    return $user;
}

beforeEach(function () {
    $this->business = Business::factory()->create();

    $this->price = Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
});

it('counts the funnel for the period and the one before it, with paid registrations by campaign', function () {
    $adsUser = reportedSignup(now()->subDays(2), [
        'signup_landing_path' => '/lp/sales-tax/tx?intent=sales-tax-permit',
        'signup_utm_medium' => 'cpc',
        'signup_utm_campaign' => '23955122777',
    ]);
    $organicUser = reportedSignup(now()->subDays(10), [
        'signup_landing_path' => '/sales-tax-registration',
    ]);
    // Signed up from a lien page: not part of this funnel.
    reportedSignup(now()->subDays(2), [
        'signup_landing_path' => '/liens',
        'signup_utm_medium' => 'cpc',
    ]);

    // This period: paid rush order for two states from the ads customer, submitted.
    $paid = reportedRegistration($this->business, $adsUser, ['CA', 'TX'], [
        'created_at' => now()->subDays(2),
        'paid_at' => now()->subDay(),
        'rush_requested_at' => now()->subDay(),
        'status' => 'submitted',
        'submitted_at' => now()->subDay(),
        'locked_at' => now()->subDay(),
    ]);
    Payment::create([
        'purchasable_type' => $paid->getMorphClass(),
        'purchasable_id' => $paid->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'amount_cents' => 49700,
        'currency' => 'usd',
        'status' => PaymentStatus::Succeeded,
        'provider' => 'stripe',
        'livemode' => false,
        'paid_at' => now()->subDay(),
    ]);
    // This period: a draft that has not paid.
    reportedRegistration($this->business, $adsUser, ['NY'], ['created_at' => now()->subDays(3)]);

    // Previous period: one organic paid registration.
    $earlier = reportedRegistration($this->business, $organicUser, ['CA'], [
        'created_at' => now()->subDays(9),
        'paid_at' => now()->subDays(9),
    ]);
    Payment::create([
        'purchasable_type' => $earlier->getMorphClass(),
        'purchasable_id' => $earlier->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'amount_cents' => 19900,
        'currency' => 'usd',
        'status' => PaymentStatus::Succeeded,
        'provider' => 'stripe',
        'livemode' => false,
        'paid_at' => now()->subDays(9),
    ]);

    $report = app(FunnelReport::class)->build(7);

    expect($report['current']['metrics'])->toMatchArray([
        'signups' => 1,
        'signups_ads' => 1,
        'drafts' => 2,
        'paid' => 1,
        'paid_ads' => 1,
        'paid_states' => 2,
        'rush' => 1,
        'revenue' => 497,
        'submitted' => 1,
        'resale_subscriptions' => 0,
    ])
        ->and($report['previous']['metrics'])->toMatchArray([
            'signups' => 1,
            'signups_ads' => 0,
            'drafts' => 1,
            'paid' => 1,
            'paid_ads' => 0,
            'paid_states' => 1,
            'rush' => 0,
            'revenue' => 199,
            'submitted' => 0,
        ])
        ->and($report['by_campaign'])->toBe(['23955122777' => 1]);
});

it('prints the report and emails it to the admins on request', function () {
    Mail::fake();

    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->artisan('report:sales-tax-funnel --days=7')
        ->expectsOutputToContain('Paid registrations')
        ->assertSuccessful();

    Mail::assertNothingQueued();

    $this->artisan('report:sales-tax-funnel --days=7 --email')->assertSuccessful();

    Mail::assertQueued(SalesTaxFunnelReportMail::class, fn ($mail) => $mail->hasTo($admin->email));

    $this->artisan('report:sales-tax-funnel --email --to=ops@example.test')->assertSuccessful();

    Mail::assertQueued(SalesTaxFunnelReportMail::class, fn ($mail) => $mail->hasTo('ops@example.test'));
});

it('renders the table and the Ads links in the email', function () {
    $report = app(FunnelReport::class)->build(7);

    $rendered = (new SalesTaxFunnelReportMail($report))->render();

    expect($rendered)->toContain('Paid registrations')
        ->and($rendered)->toContain('ads.google.com/aw/campaigns?ocid='.AdsTableState::OCID.'&amp;tableState=')
        ->and($rendered)->toContain('Cost per paid registration');
});

it('encodes a Google Ads date range the way the UI does', function () {
    $state = AdsTableState::forRange(CarbonImmutable::create(2026, 9, 20), CarbonImmutable::create(2026, 9, 22));

    expect($state)->toBe('CggIABIEaW1wcjoUChIKBwjqDxAJGBQSBwjqDxAJGBZIAVABgAFkiAEB0AEB2AEDkAIAmgIuEhVzZWdtZW50YXRpb25faW5mby5kYXkaEXN0YXRzLmNvbnZlcnNpb25zGgA4AQ%3D%3D');
});

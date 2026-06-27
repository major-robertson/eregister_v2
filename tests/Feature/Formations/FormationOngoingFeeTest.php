<?php

use App\Domains\Business\Models\Business;
use App\Domains\Formations\Livewire\FormationCheckout;
use App\Domains\Formations\Models\FormationRenewalFeeItem;
use App\Domains\Formations\Services\FormationFeeSchedule;
use App\Domains\Forms\Livewire\StateSelector;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;
use Livewire\Livewire;

// Self-contained Stripe signature helper (uniquely named to avoid colliding
// with FormationCheckoutTest's llcStripeSignature when both files load).
function llcInvoiceSignature(string $payload): string
{
    $secret = config('cashier.webhook.secret', 'whsec_test_secret');
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},v1={$signature}";
}

function llcFormation(Business $business, User $user, string $state, array $overrides = []): FormApplication
{
    $application = FormApplication::create(array_merge([
        'business_id' => $business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => [$state],
        'status' => 'draft',
        'current_phase' => 'review',
        'core_data' => ['llc_name' => 'Acme Ventures LLC'],
        'created_by_user_id' => $user->id,
    ], $overrides));

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => $state,
        'status' => 'complete',
        'completed_at' => now(),
        'data' => [],
    ]);

    return $application;
}

function llcLocalSubscription(Business $business, string $stripeId, Carbon $createdAt): Subscription
{
    $sub = $business->subscriptions()->create([
        'type' => 'llc',
        'stripe_id' => $stripeId,
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
    ]);

    $sub->forceFill(['created_at' => $createdAt])->save();

    return $sub;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function llcRenewalInvoiceObject(array $overrides = []): array
{
    return array_merge([
        'id' => 'in_'.uniqid(),
        'object' => 'invoice',
        'billing_reason' => 'subscription_cycle',
        'subscription' => 'sub_test',
        'customer' => 'cus_test',
        'currency' => 'usd',
        'amount_paid' => 79900,
        'status' => 'draft',
        'livemode' => false,
        'period_start' => Carbon::parse('2025-01-01')->timestamp, // cycle 1 vs 2024-01-01 anchor
        'subscription_details' => ['metadata' => ['app_domain' => 'llc', 'state' => 'MA']],
    ], $overrides);
}

function postLlcInvoiceWebhook($test, string $type, array $object): \Illuminate\Testing\TestResponse
{
    $payload = ['id' => 'evt_'.uniqid(), 'type' => $type, 'data' => ['object' => $object]];

    return $test->postJson(
        route('webhooks.stripe'),
        $payload,
        [
            'Stripe-Signature' => llcInvoiceSignature(json_encode($payload)),
            'Content-Type' => 'application/json',
        ]
    );
}

beforeEach(function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);
    config(['cashier.secret' => '']); // keyless: ensureFeeItem records ledger rows, skips Stripe

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    test()->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

/*
|--------------------------------------------------------------------------
| Schedule (pure)
|--------------------------------------------------------------------------
*/

it('charges an annual state every renewal cycle', function () {
    $schedule = app(FormationFeeSchedule::class);

    foreach ([1, 2, 3, 4] as $cycle) {
        $due = $schedule->dueCharges('MA', $cycle);
        expect($due)->toHaveCount(1)
            ->and($due[0]['component_key'])->toBe('annual_report')
            ->and($due[0]['amount_cents'])->toBe(50000);
    }
});

it('charges a biennial state only on even cycles', function () {
    $schedule = app(FormationFeeSchedule::class);

    expect($schedule->dueCharges('NY', 1))->toBeEmpty()
        ->and($schedule->dueCharges('NY', 3))->toBeEmpty()
        ->and($schedule->dueCharges('NY', 2))->toHaveCount(1)
        ->and($schedule->dueCharges('NY', 4))->toHaveCount(1);

    expect($schedule->dueCharges('NY', 2)[0]['amount_cents'])->toBe(900);
});

it('charges nothing for no-fee or manual states', function () {
    $schedule = app(FormationFeeSchedule::class);

    expect($schedule->dueCharges('OH', 1))->toBeEmpty()  // none
        ->and($schedule->dueCharges('OH', 2))->toBeEmpty()
        ->and($schedule->dueCharges('TX', 1))->toBeEmpty()  // manual (franchise tax)
        ->and($schedule->dueCharges('AL', 2))->toBeEmpty(); // manual
});

it('charges CA franchise tax every cycle and the statement of information on even cycles', function () {
    $schedule = app(FormationFeeSchedule::class);

    expect($schedule->dueCharges('CA', 1))->toHaveCount(1) // franchise only
        ->and($schedule->dueCharges('CA', 1)[0]['component_key'])->toBe('franchise_tax_min');

    $cycle2 = collect($schedule->dueCharges('CA', 2))->pluck('amount_cents', 'component_key');
    expect($cycle2)->toHaveCount(2)
        ->and($cycle2['franchise_tax_min'])->toBe(80000)
        ->and($cycle2['statement_of_information'])->toBe(2000);

    expect($schedule->dueCharges('CA', 3))->toHaveCount(1); // franchise only again
});

it('computes the renewal cycle number from the subscription anchor', function () {
    $schedule = app(FormationFeeSchedule::class);
    $anchor = Carbon::parse('2024-01-01');

    expect($schedule->cycleNumberFor($anchor, Carbon::parse('2025-01-01')))->toBe(1)
        ->and($schedule->cycleNumberFor($anchor, Carbon::parse('2026-01-01')))->toBe(2)
        ->and($schedule->cycleNumberFor($anchor, Carbon::parse('2027-06-01')))->toBe(3);
});

/*
|--------------------------------------------------------------------------
| Renewal webhooks
|--------------------------------------------------------------------------
*/

it('records the ongoing fee in the ledger once across invoice.upcoming and invoice.created', function () {
    $app = llcFormation($this->business, $this->user, 'MA');
    llcLocalSubscription($this->business, 'sub_ma', Carbon::parse('2024-01-01'));

    $object = llcRenewalInvoiceObject([
        'subscription' => 'sub_ma',
        'subscription_details' => ['metadata' => ['app_domain' => 'llc', 'state' => 'MA', 'llc_application_id' => $app->id]],
    ]);

    postLlcInvoiceWebhook($this, 'invoice.upcoming', $object)->assertOk();
    postLlcInvoiceWebhook($this, 'invoice.created', array_merge($object, ['status' => 'draft']))->assertOk();

    $rows = FormationRenewalFeeItem::where('stripe_subscription_id', 'sub_ma')->get();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]->cycle_number)->toBe(1)
        ->and($rows[0]->component_key)->toBe('annual_report')
        ->and($rows[0]->amount_cents)->toBe(50000);
});

it('adds no ledger row for a biennial state on an odd cycle', function () {
    $app = llcFormation($this->business, $this->user, 'NY');
    llcLocalSubscription($this->business, 'sub_ny', Carbon::parse('2024-01-01'));

    // period_start 2025-01-01 → cycle 1 → NY biennial not yet due
    postLlcInvoiceWebhook($this, 'invoice.created', llcRenewalInvoiceObject([
        'subscription' => 'sub_ny',
        'subscription_details' => ['metadata' => ['app_domain' => 'llc', 'state' => 'NY', 'llc_application_id' => $app->id]],
    ]))->assertOk();

    expect(FormationRenewalFeeItem::count())->toBe(0);
});

it('skips the first invoice (subscription_create), only charging real renewals', function () {
    $app = llcFormation($this->business, $this->user, 'MA');
    llcLocalSubscription($this->business, 'sub_ma', Carbon::parse('2024-01-01'));

    postLlcInvoiceWebhook($this, 'invoice.created', llcRenewalInvoiceObject([
        'subscription' => 'sub_ma',
        'billing_reason' => 'subscription_create',
        'subscription_details' => ['metadata' => ['app_domain' => 'llc', 'state' => 'MA', 'llc_application_id' => $app->id]],
    ]))->assertOk();

    expect(FormationRenewalFeeItem::count())->toBe(0);
});

it('records a renewal payment on invoice.paid and is idempotent', function () {
    $app = llcFormation($this->business, $this->user, 'MA');
    llcLocalSubscription($this->business, 'sub_ma', Carbon::parse('2024-01-01'));

    $object = llcRenewalInvoiceObject([
        'id' => 'in_ma_renew_1',
        'subscription' => 'sub_ma',
        'status' => 'paid',
        'amount_paid' => 79900,
        'subscription_details' => ['metadata' => ['app_domain' => 'llc', 'state' => 'MA', 'llc_application_id' => $app->id]],
    ]);

    postLlcInvoiceWebhook($this, 'invoice.paid', $object)->assertOk();
    postLlcInvoiceWebhook($this, 'invoice.paid', $object)->assertOk(); // replay (new event id)

    $payments = Payment::where('stripe_invoice_id', 'in_ma_renew_1')->get();

    expect($payments)->toHaveCount(1)
        ->and($payments[0]->billing_type)->toBe('subscription')
        ->and($payments[0]->status)->toBe(PaymentStatus::Succeeded)
        ->and($payments[0]->amount_cents)->toBe(79900)
        ->and($payments[0]->purchasable_id)->toBe($app->id);
});

it('routes invoice events to the LLC domain via the local subscription fallback', function () {
    // No app_domain in metadata at all — resolver must map sub → local llc sub.
    $app = llcFormation($this->business, $this->user, 'MA');
    llcLocalSubscription($this->business, 'sub_ma_fb', Carbon::parse('2024-01-01'));

    postLlcInvoiceWebhook($this, 'invoice.created', [
        'id' => 'in_fb',
        'object' => 'invoice',
        'billing_reason' => 'subscription_cycle',
        'subscription' => 'sub_ma_fb',
        'customer' => 'cus_test',
        'currency' => 'usd',
        'status' => 'draft',
        'period_start' => Carbon::parse('2025-01-01')->timestamp,
        // selected_states[0] supplies the state since metadata is absent
    ])->assertOk();

    expect(FormationRenewalFeeItem::where('stripe_subscription_id', 'sub_ma_fb')->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| One LLC per company
|--------------------------------------------------------------------------
*/

it('blocks starting a second LLC for a company that already formed one', function () {
    llcFormation($this->business, $this->user, 'WY', [
        'status' => 'submitted',
        'paid_at' => now(),
        'locked_at' => now(),
    ]);

    Livewire::test(StateSelector::class, ['formType' => 'llc'])
        ->assertRedirect(route('formations.dashboard'));
});

it('allows the formations selector when the company has no LLC yet', function () {
    Livewire::test(StateSelector::class, ['formType' => 'llc'])
        ->assertNoRedirect();
});

it('blocks checkout of a second LLC when one is already formed', function () {
    llcFormation($this->business, $this->user, 'WY', [
        'status' => 'submitted',
        'paid_at' => now(),
        'locked_at' => now(),
    ]);

    $second = llcFormation($this->business, $this->user, 'CA');

    Livewire::test(FormationCheckout::class, ['application' => $second])
        ->assertRedirect(route('formations.dashboard'));
});

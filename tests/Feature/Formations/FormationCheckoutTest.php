<?php

use App\Domains\Business\Models\Business;
use App\Domains\Formations\Livewire\FormationCheckout;
use App\Domains\Formations\Services\FormationPaymentService;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Support\Workspaces\WorkspaceRegistry;
use Database\Seeders\FormationFeeSeeder;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Stripe\PaymentIntent;

/**
 * Build a complete-but-unpaid LLC formation for the current business, with its
 * single selected state marked complete (so the checkout gate passes).
 */
function completeLlcApplication(Business $business, User $user, string $state): FormApplication
{
    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => [$state],
        'status' => 'draft',
        'current_phase' => 'review',
        'core_data' => ['llc_name' => 'Acme Ventures LLC'],
        'created_by_user_id' => $user->id,
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => $state,
        'status' => 'complete',
        'completed_at' => now(),
        'data' => [],
    ]);

    return $application;
}

/**
 * Create a checkout-stage Payment row for the embedded subscription flow, with
 * the subscription id already stored (as FormationCheckout does at checkout).
 */
function llcCheckoutPayment(Business $business, FormApplication $application, string $piId, string $subId): Payment
{
    return Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $business->id,
        'price_id' => Price::resolve('formation', 'llc', 'membership', 'subscription')->id,
        'stripe_payment_intent_id' => $piId,
        'stripe_subscription_id' => $subId,
        'amount_cents' => 39900,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'billing_type' => 'subscription',
        'livemode' => false,
    ]);
}

function llcStripeSignature(string $payload): string
{
    $secret = config('cashier.webhook.secret', 'whsec_test_secret');
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},v1={$signature}";
}

function postLlcWebhook($test, array $payload): \Illuminate\Testing\TestResponse
{
    return $test->postJson(
        route('webhooks.stripe'),
        $payload,
        [
            'Stripe-Signature' => llcStripeSignature(json_encode($payload)),
            'Content-Type' => 'application/json',
        ]
    );
}

beforeEach(function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    (new FormationFeeSeeder)->run();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    test()->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

it('seeds the membership price and per-state filing fees', function () {
    $membership = Price::resolve('formation', 'llc', 'membership', 'subscription');
    $wyoming = Price::resolve('formation', 'llc', 'WY', 'one_time');
    $massachusetts = Price::resolve('formation', 'llc', 'MA', 'one_time');

    expect($membership->amount_cents)->toBe(29900)
        ->and($wyoming->amount_cents)->toBe(10000)
        ->and($massachusetts->amount_cents)->toBe(50000);
});

it('wires the formations workspace checkout + confirmation routes', function () {
    $workspace = app(WorkspaceRegistry::class)->findByFormType('llc');

    expect($workspace->checkoutRouteName)->toBe('formations.checkout')
        ->and($workspace->confirmationRouteName)->toBe('formations.payment-confirmation');
});

it('stub-checks out without Stripe keys, charging membership + state fee', function () {
    config(['cashier.secret' => '']);

    // WY one-time fee $100 + $299 membership = $399. Stub runs on mount.
    $application = completeLlcApplication($this->business, $this->user, 'WY');

    Livewire::test(FormationCheckout::class, ['application' => $application])
        ->assertRedirect(route('formations.payment-confirmation', $application));

    $application->refresh();

    expect($application->paid_at)->not->toBeNull()
        ->and($application->status)->toBe('submitted')
        ->and($application->locked_at)->not->toBeNull();

    $payment = $application->payment;

    expect($payment)->not->toBeNull()
        ->and($payment->amount_cents)->toBe(39900)
        ->and($payment->status)->toBe(PaymentStatus::Succeeded);

    // A stub membership subscription is recorded so subscribed('llc') is true.
    expect($this->business->fresh()->subscribed('llc'))->toBeTrue();
});

it('marks the formation paid, submitted, locked and records the subscription on success', function () {
    Mail::fake();

    $application = completeLlcApplication($this->business, $this->user, 'WY');
    $payment = llcCheckoutPayment($this->business, $application, 'pi_llc_123', 'sub_llc_123');

    $pi = PaymentIntent::constructFrom([
        'id' => 'pi_llc_123',
        'amount_received' => 39900,
        'currency' => 'usd',
        'latest_charge' => 'ch_llc_123',
    ]);

    app(FormationPaymentService::class)->markSucceeded($payment, $pi);

    $payment->refresh();
    $application->refresh();

    expect($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->stripe_charge_id)->toBe('ch_llc_123')
        ->and($payment->stripe_subscription_id)->toBe('sub_llc_123')
        ->and($application->paid_at)->not->toBeNull()
        ->and($application->status)->toBe('submitted')
        ->and($application->locked_at)->not->toBeNull()
        ->and($this->business->fresh()->subscribed('llc'))->toBeTrue();
});

it('is idempotent when markSucceeded runs twice', function () {
    Mail::fake();

    $application = completeLlcApplication($this->business, $this->user, 'WY');
    $payment = llcCheckoutPayment($this->business, $application, 'pi_llc_idem', 'sub_llc_idem');

    $pi = PaymentIntent::constructFrom([
        'id' => 'pi_llc_idem',
        'amount_received' => 39900,
        'currency' => 'usd',
        'latest_charge' => 'ch_first',
    ]);

    $service = app(FormationPaymentService::class);
    $service->markSucceeded($payment, $pi);

    $second = PaymentIntent::constructFrom([
        'id' => 'pi_llc_idem',
        'amount_received' => 39900,
        'currency' => 'usd',
        'latest_charge' => 'ch_second',
    ]);
    $service->markSucceeded($payment->fresh(), $second);

    // Charge id from the first call is preserved (second call no-ops), and only
    // one local subscription row exists.
    expect($payment->fresh()->stripe_charge_id)->toBe('ch_first')
        ->and($this->business->subscriptions()->where('stripe_id', 'sub_llc_idem')->count())->toBe(1);
});

it('flags an amount mismatch for review and does not submit', function () {
    $application = completeLlcApplication($this->business, $this->user, 'WY');
    $payment = llcCheckoutPayment($this->business, $application, 'pi_llc_mismatch', 'sub_llc_mismatch');

    $pi = PaymentIntent::constructFrom([
        'id' => 'pi_llc_mismatch',
        'amount_received' => 19900, // wrong amount
        'currency' => 'usd',
        'latest_charge' => 'ch_mismatch',
    ]);

    app(FormationPaymentService::class)->markSucceeded($payment, $pi);

    $payment->refresh();
    $application->refresh();

    expect($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->requires_manual_review)->toBeTrue()
        ->and($payment->error_message)->toContain('Amount mismatch')
        ->and($application->paid_at)->toBeNull()
        ->and($application->status)->toBe('draft');
});

it('processes a payment_intent.succeeded webhook', function () {
    Mail::fake();

    $application = completeLlcApplication($this->business, $this->user, 'WY');
    $payment = llcCheckoutPayment($this->business, $application, 'pi_llc_webhook', 'sub_llc_webhook');

    postLlcWebhook($this, [
        'id' => 'evt_llc_pi_succeeded',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_llc_webhook',
                'object' => 'payment_intent',
                'amount_received' => 39900,
                'currency' => 'usd',
                'latest_charge' => 'ch_llc_webhook',
                'metadata' => [
                    'app_payment_id' => $payment->id,
                    'app_domain' => 'llc',
                    'payment_kind' => 'llc_formation',
                    'llc_application_id' => $application->id,
                    'state' => 'WY',
                ],
            ],
        ],
    ])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($application->fresh()->paid_at)->not->toBeNull()
        ->and($application->fresh()->status)->toBe('submitted')
        ->and($this->business->fresh()->subscribed('llc'))->toBeTrue();
});

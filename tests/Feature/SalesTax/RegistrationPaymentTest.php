<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Livewire\StateSelector;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\RegistrationCheckout;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Support\Workspaces\WorkspaceRegistry;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Stripe\PaymentIntent;

/**
 * Build a complete-but-unpaid sales-tax registration for the current
 * business with every selected state marked complete.
 *
 * @param  array<int, string>  $states
 */
function completeSalesTaxApplication(Business $business, User $user, array $states): FormApplication
{
    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'review',
        'core_data' => ['entity_type' => 'corporation'],
        'created_by_user_id' => $user->id,
    ]);

    foreach ($states as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'complete',
            'completed_at' => now(),
            'data' => [],
        ]);
    }

    return $application;
}

function taxStripeSignature(string $payload): string
{
    $secret = config('cashier.webhook.secret', 'whsec_test_secret');
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return "t={$timestamp},v1={$signature}";
}

function postTaxWebhook($test, array $payload): \Illuminate\Testing\TestResponse
{
    return $test->postJson(
        route('webhooks.stripe'),
        $payload,
        [
            'Stripe-Signature' => taxStripeSignature(json_encode($payload)),
            'Content-Type' => 'application/json',
        ]
    );
}

beforeEach(function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->price = Price::updateOrCreate(
        [
            'product_family' => 'tax',
            'product_key' => 'sales_tax_permit',
            'variant_key' => 'per_state',
            'billing_type' => 'one_time',
        ],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );

    test()->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

it('seeds the $199 per-state tax price', function () {
    (new \Database\Seeders\PriceSeeder)->run();

    $price = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time');

    expect($price->amount_cents)->toBe(19900)
        ->and($price->currency)->toBe('usd');
});

it('wires the sales tax workspace checkout route', function () {
    $workspace = app(WorkspaceRegistry::class)->findByFormType('sales_tax_permit');

    expect($workspace->checkoutRouteName)->toBe('sales-tax.registrations.checkout');
});

it('marks the registration paid, submitted and locked on success', function () {
    Mail::fake();

    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX']);

    $payment = Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'stripe_payment_intent_id' => 'pi_tax_123',
        'amount_cents' => 39800,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);

    $pi = PaymentIntent::constructFrom([
        'amount_received' => 39800,
        'currency' => 'usd',
        'latest_charge' => 'ch_tax_123',
    ]);

    app(RegistrationPaymentService::class)->markSucceeded($payment, $pi);

    $payment->refresh();
    $application->refresh();

    expect($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->stripe_charge_id)->toBe('ch_tax_123')
        ->and($application->paid_at)->not->toBeNull()
        ->and($application->status)->toBe('submitted')
        ->and($application->locked_at)->not->toBeNull();
});

it('is idempotent when markSucceeded runs twice', function () {
    Mail::fake();

    $application = completeSalesTaxApplication($this->business, $this->user, ['CA']);

    $payment = Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'stripe_payment_intent_id' => 'pi_tax_idem',
        'amount_cents' => 19900,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);

    $pi = PaymentIntent::constructFrom([
        'amount_received' => 19900,
        'currency' => 'usd',
        'latest_charge' => 'ch_first',
    ]);

    $service = app(RegistrationPaymentService::class);
    $service->markSucceeded($payment, $pi);

    $secondPi = PaymentIntent::constructFrom([
        'amount_received' => 19900,
        'currency' => 'usd',
        'latest_charge' => 'ch_second',
    ]);
    $service->markSucceeded($payment->fresh(), $secondPi);

    $payment->refresh();

    // Charge id from the first call is preserved (second call no-ops).
    expect($payment->stripe_charge_id)->toBe('ch_first');
});

it('flags an amount mismatch for review and does not submit', function () {
    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX']);

    $payment = Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'stripe_payment_intent_id' => 'pi_tax_mismatch',
        'amount_cents' => 39800,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);

    $pi = PaymentIntent::constructFrom([
        'amount_received' => 19900, // wrong amount
        'currency' => 'usd',
        'latest_charge' => 'ch_mismatch',
    ]);

    app(RegistrationPaymentService::class)->markSucceeded($payment, $pi);

    $payment->refresh();
    $application->refresh();

    expect($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->requires_manual_review)->toBeTrue()
        ->and($payment->error_message)->toContain('Amount mismatch')
        ->and($application->paid_at)->toBeNull()
        ->and($application->status)->toBe('draft');
});

it('processes a tax payment_intent.succeeded webhook', function () {
    Mail::fake();

    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX']);

    $payment = Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'stripe_payment_intent_id' => 'pi_tax_webhook',
        'amount_cents' => 39800,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);

    postTaxWebhook($this, [
        'id' => 'evt_tax_succeeded',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_tax_webhook',
                'amount_received' => 39800,
                'currency' => 'usd',
                'latest_charge' => 'ch_tax_webhook',
                'metadata' => [
                    'app_payment_id' => $payment->id,
                    'app_domain' => 'tax',
                    'payment_kind' => 'sales_tax_registration',
                    'sales_tax_application_id' => $application->id,
                ],
            ],
        ],
    ])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($application->fresh()->paid_at)->not->toBeNull()
        ->and($application->fresh()->status)->toBe('submitted');
});

it('handles a tax payment_intent.payment_failed webhook as retryable', function () {
    $application = completeSalesTaxApplication($this->business, $this->user, ['CA']);

    $payment = Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'stripe_payment_intent_id' => 'pi_tax_failed',
        'amount_cents' => 19900,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);

    postTaxWebhook($this, [
        'id' => 'evt_tax_failed',
        'type' => 'payment_intent.payment_failed',
        'data' => [
            'object' => [
                'id' => 'pi_tax_failed',
                'last_payment_error' => ['message' => 'Your card was declined.'],
                'metadata' => [
                    'app_payment_id' => $payment->id,
                    'app_domain' => 'tax',
                ],
            ],
        ],
    ])->assertOk();

    $payment->refresh();

    expect($payment->status)->toBe(PaymentStatus::RequiresPaymentMethod)
        ->and($payment->error_message)->toBe('Your card was declined.')
        ->and($application->fresh()->paid_at)->toBeNull();
});

it('stub-checks out without Stripe keys and charges $199 per state', function () {
    config(['cashier.secret' => '']);

    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX', 'NY']);

    Livewire::test(RegistrationCheckout::class, ['application' => $application])
        ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));

    $application->refresh();

    expect($application->paid_at)->not->toBeNull()
        ->and($application->status)->toBe('submitted')
        ->and($application->locked_at)->not->toBeNull();

    $payment = $application->payment;

    expect($payment)->not->toBeNull()
        ->and($payment->amount_cents)->toBe(59700) // 3 states x $199
        ->and($payment->status)->toBe(PaymentStatus::Succeeded);
});

it('blocks already-paid states from new registrations', function () {
    config(['cashier.secret' => '']);

    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX']);

    Livewire::test(RegistrationCheckout::class, ['application' => $application]);

    expect($application->fresh()->isPaid())->toBeTrue();

    $component = Livewire::test(StateSelector::class, ['formType' => 'sales_tax_permit']);

    expect($component->get('blockedStates'))->toContain('CA')
        ->and($component->get('blockedStates'))->toContain('TX');
});

it('redirects an already-paid application away from checkout', function () {
    $application = completeSalesTaxApplication($this->business, $this->user, ['CA']);
    $application->update(['paid_at' => now(), 'status' => 'submitted', 'locked_at' => now()]);

    Livewire::test(RegistrationCheckout::class, ['application' => $application])
        ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));
});

it('views a paid application via the receipt page instead of 403ing', function () {
    $application = completeSalesTaxApplication($this->business, $this->user, ['CA', 'TX']);
    $application->update(['paid_at' => now(), 'status' => 'submitted', 'locked_at' => now()]);

    // The dashboard "View" action points at the confirmation/receipt page.
    expect($application->fresh()->dashboard_action_label)->toBe('View')
        ->and($application->fresh()->dashboard_action_url)
        ->toBe(route('sales-tax.registrations.payment-confirmation', $application));

    // Opening the form runner for a locked app redirects to the receipt
    // rather than throwing a 403 (the old Gate::authorize('update') bug).
    Livewire::test(MultiStateFormRunner::class, ['application' => $application])
        ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));
});

it('returns the receipt view for a paid application', function () {
    $application = completeSalesTaxApplication($this->business, $this->user, ['CA']);

    Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $this->business->id,
        'price_id' => $this->price->id,
        'amount_cents' => 19900,
        'currency' => 'usd',
        'status' => PaymentStatus::Succeeded,
        'provider' => 'stripe',
        'livemode' => false,
        'paid_at' => now(),
    ]);
    $application->update(['paid_at' => now(), 'status' => 'submitted', 'locked_at' => now()]);

    $view = app(\App\Domains\SalesTax\Http\Controllers\RegistrationPaymentController::class)
        ->confirmation($application->fresh(), request());

    expect($view->name())->toBe('sales-tax.registration.payment-success');
});

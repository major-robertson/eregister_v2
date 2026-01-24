<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\StripeWebhookEvent;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create();
    $this->docType = LienDocumentType::first();

    $this->filing = LienFiling::factory()->forProject($this->project)->create([
        'document_type_id' => $this->docType->id,
        'status' => FilingStatus::AwaitingPayment,
    ]);

    // Create a payment for this filing (using polymorphic Payment model)
    $this->payment = Payment::create([
        'purchasable_type' => $this->filing->getMorphClass(),
        'purchasable_id' => $this->filing->id,
        'business_id' => $this->business->id,
        'stripe_payment_intent_id' => 'pi_test_123',
        'amount_cents' => 4900,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
    ]);
});

/**
 * Helper to create a valid Stripe webhook signature.
 */
function createStripeSignature(string $payload, ?string $secret = null): string
{
    $secret = $secret ?? config('cashier.webhook.secret', 'whsec_test_secret');
    $timestamp = time();
    $signedPayload = $timestamp.'.'.$payload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);

    return "t={$timestamp},v1={$signature}";
}

/**
 * Helper to send a webhook with proper signature.
 */
function postWebhook($test, array $payload): \Illuminate\Testing\TestResponse
{
    $jsonPayload = json_encode($payload);
    $signature = createStripeSignature($jsonPayload);

    return $test->postJson(
        route('webhooks.stripe'),
        $payload,
        [
            'Stripe-Signature' => $signature,
            'Content-Type' => 'application/json',
        ]
    );
}

it('processes payment_intent.succeeded webhook', function () {
    // Set a test webhook secret
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_pi_succeeded',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'amount_received' => 4900,
                'currency' => 'usd',
                'latest_charge' => 'ch_test_123',
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                    'lien_filing_id' => $this->filing->id,
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk();

    // Check event was recorded and marked processed
    $this->assertDatabaseHas('stripe_webhook_events', [
        'stripe_event_id' => 'evt_test_pi_succeeded',
        'type' => 'payment_intent.succeeded',
    ]);
    expect(StripeWebhookEvent::where('stripe_event_id', 'evt_test_pi_succeeded')->first()->processed_at)->not->toBeNull();

    // Check payment status updated
    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    expect($this->payment->fresh()->stripe_charge_id)->toBe('ch_test_123');

    // Check filing status updated (InFulfillment for full-service, Paid for self-serve)
    $status = $this->filing->fresh()->status;
    expect($status)->toBeIn([FilingStatus::Paid, FilingStatus::InFulfillment]);
});

it('is idempotent - duplicate events are ignored after processing', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $eventId = 'evt_test_idempotent';

    $payload = [
        'id' => $eventId,
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'amount_received' => 4900,
                'currency' => 'usd',
                'latest_charge' => 'ch_test_456',
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                    'lien_filing_id' => $this->filing->id,
                ],
            ],
        ],
    ];

    // First request
    postWebhook($this, $payload)->assertOk();

    $eventCount = StripeWebhookEvent::count();

    // Second request with same event ID
    postWebhook($this, $payload)
        ->assertOk()
        ->assertSee('Already processed');

    // Should not create duplicate event records
    expect(StripeWebhookEvent::count())->toBe($eventCount);
});

it('handles payment_intent.payment_failed as retryable', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_pi_failed',
        'type' => 'payment_intent.payment_failed',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'last_payment_error' => [
                    'message' => 'Your card was declined.',
                ],
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk();

    // Payment should be marked as retryable, not terminal failed
    $payment = $this->payment->fresh();
    expect($payment->status)->toBe(PaymentStatus::RequiresPaymentMethod);
    expect($payment->error_message)->toBe('Your card was declined.');
});

it('handles payment_intent.canceled', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_pi_canceled',
        'type' => 'payment_intent.canceled',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk();

    expect($this->payment->fresh()->status)->toBe(PaymentStatus::Canceled);
});

it('flags payment for manual review on amount mismatch', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_amount_mismatch',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'amount_received' => 9900, // Different from expected 4900
                'currency' => 'usd',
                'latest_charge' => 'ch_test_789',
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                    'lien_filing_id' => $this->filing->id,
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk();

    $payment = $this->payment->fresh();
    expect($payment->status)->toBe(PaymentStatus::Succeeded);
    expect($payment->requires_manual_review)->toBeTrue();
    expect($payment->error_message)->toContain('Amount mismatch');

    // Filing should NOT be transitioned when flagged for review
    expect($this->filing->fresh()->status)->toBe(FilingStatus::AwaitingPayment);
});

it('returns 400 for invalid signature', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_bad_sig',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => []],
    ];

    // Send with wrong signature
    $response = $this->postJson(
        route('webhooks.stripe'),
        $payload,
        [
            'Stripe-Signature' => 't=12345,v1=invalid_signature',
            'Content-Type' => 'application/json',
        ]
    );

    $response->assertStatus(400);
});

it('handles missing app_payment_id gracefully', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $payload = [
        'id' => 'evt_test_no_payment_id',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_external_123',
                'amount_received' => 9900,
                'currency' => 'usd',
                'metadata' => [
                    'app_domain' => 'lien',
                    // No app_payment_id
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk()
        ->assertSee('No app_payment_id');
});

it('does not re-process already succeeded payments', function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    // Mark payment as already succeeded
    $this->payment->update([
        'status' => PaymentStatus::Succeeded,
        'paid_at' => now(),
    ]);

    $payload = [
        'id' => 'evt_test_already_succeeded',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'amount_received' => 4900,
                'currency' => 'usd',
                'latest_charge' => 'ch_new_charge',
                'metadata' => [
                    'app_payment_id' => $this->payment->id,
                    'app_domain' => 'lien',
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk();

    // Charge ID should NOT be updated (idempotent)
    expect($this->payment->fresh()->stripe_charge_id)->toBeNull();
});

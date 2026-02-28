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
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create();
    $this->docType = LienDocumentType::first();

    $this->filing = LienFiling::factory()->forProject($this->project)->create([
        'document_type_id' => $this->docType->id,
        'status' => FilingStatus::AwaitingPayment,
    ]);

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

function createStripeSignature(string $payload, ?string $secret = null): string
{
    $secret = $secret ?? config('cashier.webhook.secret', 'whsec_test_secret');
    $timestamp = time();
    $signedPayload = $timestamp.'.'.$payload;
    $signature = hash_hmac('sha256', $signedPayload, $secret);

    return "t={$timestamp},v1={$signature}";
}

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

    $this->assertDatabaseHas('stripe_webhook_events', [
        'stripe_event_id' => 'evt_test_pi_succeeded',
        'type' => 'payment_intent.succeeded',
    ]);
    expect(StripeWebhookEvent::where('stripe_event_id', 'evt_test_pi_succeeded')->first()->processed_at)->not->toBeNull();

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::Succeeded);
    expect($this->payment->stripe_charge_id)->toBe('ch_test_123');

    $this->filing->refresh();
    expect($this->filing->status)->toBeIn([FilingStatus::Paid, FilingStatus::InFulfillment]);
});

it('is idempotent - duplicate events are ignored after processing', function () {
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

    postWebhook($this, $payload)->assertOk();

    $eventCount = StripeWebhookEvent::count();

    postWebhook($this, $payload)
        ->assertOk()
        ->assertSee('Already processed');

    expect(StripeWebhookEvent::count())->toBe($eventCount);
});

it('handles payment_intent.payment_failed as retryable', function () {
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

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::RequiresPaymentMethod);
    expect($this->payment->error_message)->toBe('Your card was declined.');
});

it('handles payment_intent.canceled', function () {
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

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::Canceled);
});

it('flags payment for manual review on amount mismatch', function () {
    $payload = [
        'id' => 'evt_test_amount_mismatch',
        'type' => 'payment_intent.succeeded',
        'data' => [
            'object' => [
                'id' => 'pi_test_123',
                'amount_received' => 9900,
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

    $this->payment->refresh();
    expect($this->payment->status)->toBe(PaymentStatus::Succeeded);
    expect($this->payment->requires_manual_review)->toBeTrue();
    expect($this->payment->error_message)->toContain('Amount mismatch');

    $this->filing->refresh();
    expect($this->filing->status)->toBe(FilingStatus::AwaitingPayment);
});

it('returns 400 for invalid signature', function () {
    $payload = [
        'id' => 'evt_test_bad_sig',
        'type' => 'payment_intent.succeeded',
        'data' => ['object' => []],
    ];

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
                ],
            ],
        ],
    ];

    $response = postWebhook($this, $payload);

    $response->assertOk()
        ->assertSee('No app_payment_id');
});

it('does not re-process already succeeded payments', function () {
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

    $this->payment->refresh();
    expect($this->payment->stripe_charge_id)->toBeNull();
});

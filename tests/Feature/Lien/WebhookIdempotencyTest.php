<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienPayment;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStripeWebhookEvent;
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
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);
});

it('processes checkout.session.completed webhook', function () {
    $payload = [
        'id' => 'evt_test_123',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_123',
                'payment_intent' => 'pi_test_123',
                'amount_total' => 9900,
                'currency' => 'usd',
                'metadata' => [
                    'filing_id' => $this->filing->id,
                    'filing_public_id' => $this->filing->public_id,
                ],
            ],
        ],
    ];

    $response = $this->postJson(route('lien.webhooks.stripe'), $payload);

    $response->assertOk();

    // Check event was recorded
    $this->assertDatabaseHas('lien_stripe_webhook_events', [
        'stripe_event_id' => 'evt_test_123',
        'type' => 'checkout.session.completed',
    ]);

    // Check payment was created
    $this->assertDatabaseHas('lien_payments', [
        'filing_id' => $this->filing->id,
        'stripe_checkout_session_id' => 'cs_test_123',
    ]);

    // Check filing status updated
    expect($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
});

it('is idempotent - duplicate events are ignored', function () {
    $eventId = 'evt_test_456';

    $payload = [
        'id' => $eventId,
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_456',
                'payment_intent' => 'pi_test_456',
                'amount_total' => 9900,
                'currency' => 'usd',
                'metadata' => [
                    'filing_id' => $this->filing->id,
                ],
            ],
        ],
    ];

    // First request
    $this->postJson(route('lien.webhooks.stripe'), $payload)->assertOk();

    $paymentCount = LienPayment::count();
    $eventCount = LienStripeWebhookEvent::count();

    // Second request with same event ID
    $this->postJson(route('lien.webhooks.stripe'), $payload)
        ->assertOk()
        ->assertSee('Already processed');

    // Should not create duplicate records
    expect(LienPayment::count())->toBe($paymentCount);
    expect(LienStripeWebhookEvent::count())->toBe($eventCount);
});

it('handles missing filing_id gracefully', function () {
    $payload = [
        'id' => 'evt_test_789',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_789',
                'metadata' => [],
            ],
        ],
    ];

    $response = $this->postJson(route('lien.webhooks.stripe'), $payload);

    $response->assertOk()
        ->assertSee('No filing_id in metadata');
});

it('unique constraint on checkout_session_id prevents duplicate payments', function () {
    // Create first payment
    LienPayment::create([
        'business_id' => $this->business->id,
        'filing_id' => $this->filing->id,
        'stripe_checkout_session_id' => 'cs_unique_test',
        'amount_cents' => 9900,
        'currency' => 'usd',
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    // Attempting to create duplicate should use firstOrCreate pattern
    $payment = LienPayment::firstOrCreate(
        ['stripe_checkout_session_id' => 'cs_unique_test'],
        [
            'business_id' => $this->business->id,
            'filing_id' => $this->filing->id,
            'amount_cents' => 5000, // Different amount
            'currency' => 'usd',
            'status' => 'paid',
        ]
    );

    // Should return existing payment, not create new one
    expect($payment->amount_cents)->toBe(9900);
    expect(LienPayment::where('stripe_checkout_session_id', 'cs_unique_test')->count())->toBe(1);
});

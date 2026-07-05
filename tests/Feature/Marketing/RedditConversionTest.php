<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Enums\PaymentStatus;
use App\Jobs\SendRedditConversion;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Stripe\StripeObject;

// Server-side CAPI events must mirror the pixel's conversion ids so Reddit
// dedupes the two sources. These tests pin the ids, the hashing, and the
// exactly-once dispatch semantics.

function enableRedditCapi(): void
{
    config()->set('services.reddit.capi_enabled', true);
    config()->set('services.reddit.capi_token', 'test-token');
}

function makePendingCapiPayment(): Payment
{
    $user = User::factory()->create(['signup_rdt_cid' => 'click-abc']);
    $business = Business::create(['name' => 'Capi Business']);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    return Payment::create([
        'business_id' => $business->id,
        'purchasable_type' => $business->getMorphClass(),
        'purchasable_id' => $business->id,
        'provider' => 'stripe',
        'livemode' => false,
        'amount_cents' => 29700,
        'currency' => 'usd',
        'status' => PaymentStatus::Processing,
        'billing_type' => 'subscription',
    ]);
}

test('markSucceeded queues one hashed Purchase conversion, idempotently', function () {
    enableRedditCapi();
    Queue::fake();

    $payment = makePendingCapiPayment();
    $intent = StripeObject::constructFrom([
        'amount_received' => 29700,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]);

    $service = app(ResaleCertPaymentService::class);
    $service->markSucceeded($payment, $intent);
    $service->markSucceeded($payment->fresh(), $intent);

    Queue::assertPushed(SendRedditConversion::class, 1);
    Queue::assertPushed(SendRedditConversion::class, function (SendRedditConversion $job) use ($payment) {
        $user = $payment->business->users()->first();

        return $job->event['event_metadata']['conversion_id'] === 'purchase-'.$payment->id
            && $job->event['event_metadata']['value_decimal'] === 297.0
            && $job->event['event_type']['tracking_type'] === 'Purchase'
            && $job->event['click_id'] === 'click-abc'
            && $job->event['user']['email'] === hash('sha256', mb_strtolower(trim($user->email)))
            && $job->event['user']['external_id'] === hash('sha256', (string) $user->id);
    });
});

test('registration queues a SignUp conversion with the captured click id', function () {
    enableRedditCapi();
    Queue::fake();

    // An ad-click landing carries ?rdt_cid=; the attribution middleware
    // captures it first-touch and CreateNewUser persists it on the user.
    $this->get('/?rdt_cid=reddit-click-1');

    $this->post(route('register.store'), [
        'first_name' => 'Cari',
        'last_name' => 'Papi',
        'email' => 'capi-signup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'capi-signup@example.com')->firstOrFail();

    expect($user->signup_rdt_cid)->toBe('reddit-click-1');

    Queue::assertPushed(SendRedditConversion::class, function (SendRedditConversion $job) use ($user) {
        return $job->event['event_metadata']['conversion_id'] === 'signup-'.$user->id
            && $job->event['event_type']['tracking_type'] === 'SignUp'
            && $job->event['click_id'] === 'reddit-click-1';
    });
});

test('a flagged-for-review payment still queues the Purchase conversion', function () {
    enableRedditCapi();
    Queue::fake();

    $payment = makePendingCapiPayment();

    // Amount mismatch flags the payment for review, but the charge is real
    // and the browser pixel counts it - CAPI must mirror it.
    app(ResaleCertPaymentService::class)->markSucceeded($payment, StripeObject::constructFrom([
        'amount_received' => 11111,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]));

    expect($payment->fresh()->requires_manual_review)->toBeTrue();

    Queue::assertPushed(SendRedditConversion::class, 1);
});

test('malformed rdt_cid values are ignored instead of poisoning registration', function () {
    $this->get('/?rdt_cid[]=x');
    $this->get('/?rdt_cid='.str_repeat('a', 300));

    expect(session('signup_rdt_cid'))->toBeNull();

    $this->post(route('register.store'), [
        'first_name' => 'Clean',
        'last_name' => 'Session',
        'email' => 'clean-session@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasNoErrors();

    expect(User::where('email', 'clean-session@example.com')->firstOrFail()->signup_rdt_cid)->toBeNull();
});

test('no conversion jobs are queued while CAPI is disabled', function () {
    Queue::fake();

    $payment = makePendingCapiPayment();
    app(ResaleCertPaymentService::class)->markSucceeded($payment, StripeObject::constructFrom([
        'amount_received' => 29700,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]));

    $this->post(route('register.store'), [
        'first_name' => 'No',
        'last_name' => 'Capi',
        'email' => 'no-capi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Queue::assertNotPushed(SendRedditConversion::class);
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Enums\PaymentStatus;
use App\Jobs\SendOpenAiConversion;
use App\Models\Payment;
use App\Models\User;
use App\Services\OpenAiConversionsApi;
use Illuminate\Support\Facades\Queue;
use Stripe\StripeObject;

// Server-side CAPI events must mirror the pixel's event_id so OpenAI dedupes
// the two sources on pixelId + event name + id. These tests pin the ids, the
// order/subscription mapping, the hashing, and exactly-once dispatch.

function enableOpenAiCapi(): void
{
    config()->set('services.openai_ads.capi_enabled', true);
    config()->set('services.openai_ads.capi_token', 'test-token');
}

function makePendingOpenAiPayment(string $billingType = 'subscription'): Payment
{
    $user = User::factory()->create(['signup_oppref' => 'oppref-abc']);
    $business = Business::create(['name' => 'OpenAI Capi Business']);
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
        'billing_type' => $billingType,
    ]);
}

test('markSucceeded queues one subscription_created conversion, idempotently', function () {
    enableOpenAiCapi();
    Queue::fake();

    $payment = makePendingOpenAiPayment('subscription');
    $intent = StripeObject::constructFrom([
        'amount_received' => 29700,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]);

    $service = app(ResaleCertPaymentService::class);
    $service->markSucceeded($payment, $intent);
    $service->markSucceeded($payment->fresh(), $intent);

    Queue::assertPushed(SendOpenAiConversion::class, 1);
    Queue::assertPushed(SendOpenAiConversion::class, function (SendOpenAiConversion $job) use ($payment) {
        $user = $payment->business->users()->first();

        return $job->event['id'] === 'subscription-'.$payment->id
            && $job->event['type'] === 'subscription_created'
            && $job->event['data']['type'] === 'plan_enrollment'
            && $job->event['data']['amount'] === 297.0
            && $job->event['action_source'] === 'web'
            && $job->event['oppref'] === 'oppref-abc'
            && $job->event['user']['email_sha256'] === hash('sha256', mb_strtolower(trim($user->email)))
            && $job->event['user']['external_id_sha256'] === hash('sha256', (string) $user->id);
    });
});

test('purchaseEvent maps a one-time payment to order_created / contents', function () {
    enableOpenAiCapi();

    $payment = makePendingOpenAiPayment('one_time');
    $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);

    $event = app(OpenAiConversionsApi::class)->purchaseEvent($payment->fresh());

    expect($event['id'])->toBe('order-'.$payment->id)
        ->and($event['type'])->toBe('order_created')
        ->and($event['data']['type'])->toBe('contents')
        ->and($event['data']['amount'])->toBe(297.0)
        ->and($event['data']['currency'])->toBe('USD');
});

test('registration queues a registration_completed event with hashed keys and oppref', function () {
    enableOpenAiCapi();
    Queue::fake();

    // A ChatGPT ad landing carries ?oppref=; the attribution middleware
    // captures it first-touch and CreateNewUser persists it on the user.
    $this->get('/?oppref=chatgpt-click-1');

    $this->post(route('register.store'), [
        'first_name' => 'Cora',
        'last_name' => 'Papi',
        'email' => 'openai-signup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'openai-signup@example.com')->firstOrFail();

    expect($user->signup_oppref)->toBe('chatgpt-click-1');

    Queue::assertPushed(SendOpenAiConversion::class, function (SendOpenAiConversion $job) use ($user) {
        return $job->event['id'] === 'signup-'.$user->id
            && $job->event['type'] === 'registration_completed'
            && $job->event['data']['type'] === 'customer_action'
            && $job->event['oppref'] === 'chatgpt-click-1'
            && $job->event['user']['email_sha256'] === hash('sha256', mb_strtolower(trim($user->email)));
    });
});

test('a flagged-for-review payment still queues the OpenAI conversion', function () {
    enableOpenAiCapi();
    Queue::fake();

    $payment = makePendingOpenAiPayment('subscription');

    // Amount mismatch flags the payment, but the charge is real and the
    // browser pixel counts it - CAPI must mirror it.
    app(ResaleCertPaymentService::class)->markSucceeded($payment, StripeObject::constructFrom([
        'amount_received' => 11111,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]));

    expect($payment->fresh()->requires_manual_review)->toBeTrue();

    Queue::assertPushed(SendOpenAiConversion::class, 1);
});

test('no OpenAI conversion jobs are queued while CAPI is disabled', function () {
    Queue::fake();

    $payment = makePendingOpenAiPayment('subscription');
    app(ResaleCertPaymentService::class)->markSucceeded($payment, StripeObject::constructFrom([
        'amount_received' => 29700,
        'currency' => 'usd',
        'latest_charge' => 'ch_test',
    ]));

    $this->post(route('register.store'), [
        'first_name' => 'No',
        'last_name' => 'Capi',
        'email' => 'no-openai-capi@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Queue::assertNotPushed(SendOpenAiConversion::class);
});

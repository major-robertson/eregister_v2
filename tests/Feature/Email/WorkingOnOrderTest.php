<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Services\LienPaymentService;
use App\Enums\PaymentStatus;
use App\Jobs\SendWorkingOnOrderEmail;
use App\Mail\WorkingOnOrder;
use App\Models\Payment;
use App\Models\Price;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->onboarded()->withLienOnboarding()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'created_by_user_id' => $this->user->id,
    ]);

    $this->price = Price::create([
        'product_family' => 'lien',
        'product_key' => 'prelim_notice',
        'variant_key' => 'full_service',
        'billing_type' => 'one_time',
        'amount_cents' => 4900,
        'currency' => 'usd',
        'active' => true,
    ]);
});

it('dispatches working on order job for registration orders', function () {
    Queue::fake([SendWorkingOnOrderEmail::class]);
    Mail::fake();

    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::AwaitingPayment,
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->create([
        'price_id' => $this->price->id,
        'status' => PaymentStatus::Initiated,
    ]);

    $stripePaymentIntent = new \Stripe\PaymentIntent('pi_test_123');
    $stripePaymentIntent->latest_charge = 'ch_test_123';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    Queue::assertPushed(SendWorkingOnOrderEmail::class, function ($job) use ($payment) {
        return $job->payment->id === $payment->id;
    });
});

it('does not dispatch for saas product family', function () {
    Queue::fake([SendWorkingOnOrderEmail::class]);
    Mail::fake();

    $saasPrice = Price::create([
        'product_family' => 'saas',
        'product_key' => 'resale_cert',
        'variant_key' => 'default',
        'billing_type' => 'subscription',
        'amount_cents' => 999,
        'currency' => 'usd',
        'active' => true,
    ]);

    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::AwaitingPayment,
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->create([
        'price_id' => $saasPrice->id,
        'status' => PaymentStatus::Initiated,
    ]);

    $stripePaymentIntent = new \Stripe\PaymentIntent('pi_test_123');
    $stripePaymentIntent->latest_charge = 'ch_test_123';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    Queue::assertNotPushed(SendWorkingOnOrderEmail::class);
});

it('computes send time within 6am-8pm ET window', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 15, 10, 0, 0, 'America/New_York'));

    $sendTime = SendWorkingOnOrderEmail::nextAllowedSendTime();

    expect($sendTime->timezone('America/New_York')->hour)->toBe(11);

    Carbon::setTestNow();
});

it('delays to next morning when payment is after 7pm ET', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 15, 19, 30, 0, 'America/New_York'));

    $sendTime = SendWorkingOnOrderEmail::nextAllowedSendTime();

    $sendTimeET = $sendTime->timezone('America/New_York');
    expect($sendTimeET->hour)->toBe(6);
    expect($sendTimeET->day)->toBe(16);

    Carbon::setTestNow();
});

it('delays to 6am when payment is in early morning', function () {
    Carbon::setTestNow(Carbon::create(2026, 3, 15, 3, 0, 0, 'America/New_York'));

    $sendTime = SendWorkingOnOrderEmail::nextAllowedSendTime();

    $sendTimeET = $sendTime->timezone('America/New_York');
    expect($sendTimeET->hour)->toBe(6);
    expect($sendTimeET->day)->toBe(15);

    Carbon::setTestNow();
});

it('records scheduled_at in the future and sent_at as null until job runs', function () {
    Queue::fake([SendWorkingOnOrderEmail::class]);
    Mail::fake();

    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::AwaitingPayment,
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->create([
        'price_id' => $this->price->id,
        'status' => PaymentStatus::Initiated,
    ]);

    $stripePaymentIntent = new \Stripe\PaymentIntent('pi_test_123');
    $stripePaymentIntent->latest_charge = 'ch_test_123';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    $sentEmail = SentEmail::where('email_type', 'working_on_order')
        ->where('emailable_type', 'payment')
        ->where('emailable_id', $payment->id)
        ->first();

    expect($sentEmail)->not->toBeNull();
    expect($sentEmail->scheduled_at)->not->toBeNull();
    expect($sentEmail->sent_at)->toBeNull();
});

it('renders working on order email content correctly', function () {
    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::Paid,
        'paid_at' => now(),
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
        'price_id' => $this->price->id,
    ]);

    $mailable = new WorkingOnOrder($payment);
    $rendered = $mailable->render();

    expect($rendered)->toContain($this->user->first_name);
    expect($rendered)->toContain('Major');
    expect($rendered)->toContain('eRegister');
    expect($rendered)->toContain('working on your order');
});

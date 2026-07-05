<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Services\LienPaymentService;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

it('queues payment receipt after successful payment', function () {
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
    $stripePaymentIntent->amount_received = $payment->amount_cents;
    $stripePaymentIntent->currency = 'usd';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    Mail::assertQueued(PaymentReceipt::class, function (PaymentReceipt $mail) use ($payment) {
        return $mail->payment->id === $payment->id;
    });
});

it('does not send receipt twice for the same payment (idempotency)', function () {
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
    $stripePaymentIntent->amount_received = $payment->amount_cents;
    $stripePaymentIntent->currency = 'usd';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    app(LienPaymentService::class)->markSucceeded($payment->fresh(), $stripePaymentIntent);

    Mail::assertQueued(PaymentReceipt::class, 1);
});

it('includes itemized information in the receipt', function () {
    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::AwaitingPayment,
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
        'price_id' => $this->price->id,
        'amount_cents' => 4900,
    ]);

    $mailable = new PaymentReceipt($payment);
    $rendered = $mailable->render();

    expect($rendered)->toContain('Preliminary Notice');
    expect($rendered)->toContain('$49.00');
    expect($rendered)->toContain($this->user->first_name);
});

it('labels the service level for a state-specific price variant', function () {
    // NJ full-service mechanics lien uses variant_key "NJ_full_service"; the
    // receipt must still resolve the "(Full Service)" suffix.
    // The migration seeds this row into the test DB, so upsert to avoid a
    // unique-constraint clash while keeping the test self-contained.
    $njPrice = Price::updateOrCreate(
        [
            'product_family' => 'lien',
            'product_key' => 'mechanics_lien',
            'variant_key' => 'NJ_full_service',
            'billing_type' => 'one_time',
        ],
        [
            'amount_cents' => 89900,
            'currency' => 'usd',
            'active' => true,
        ],
    );

    $filing = LienFiling::factory()->forProject($this->project)->create([
        'status' => FilingStatus::AwaitingPayment,
        'jurisdiction_state' => 'NJ',
        'created_by_user_id' => $this->user->id,
    ]);

    $payment = Payment::factory()->forPurchasable($filing)->succeeded()->create([
        'price_id' => $njPrice->id,
        'amount_cents' => 89900,
    ]);

    $rendered = (new PaymentReceipt($payment))->render();

    expect($rendered)->toContain('Mechanics Lien')
        ->and($rendered)->toContain('(Full Service)')
        ->and($rendered)->toContain('$899.00');
});

it('records sent email in sent_emails table', function () {
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
    $stripePaymentIntent->amount_received = $payment->amount_cents;
    $stripePaymentIntent->currency = 'usd';

    app(LienPaymentService::class)->markSucceeded($payment, $stripePaymentIntent);

    expect(SentEmail::where('email_type', 'payment_receipt')
        ->where('emailable_type', 'payment')
        ->where('emailable_id', $payment->id)
        ->exists())->toBeTrue();
});

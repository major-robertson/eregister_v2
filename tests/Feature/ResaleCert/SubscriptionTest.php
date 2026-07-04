<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\SubscriptionCheckout;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Subscription Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('gates certificate pages behind the subscription', function () {
    $this->get(route('resale-cert.certificates.index'))
        ->assertRedirect(route('resale-cert.dashboard'));

    $this->get(route('resale-cert.vendors.index'))
        ->assertRedirect(route('resale-cert.dashboard'));

    $this->get(route('resale-cert.onboarding'))
        ->assertRedirect(route('resale-cert.dashboard'));
});

it('shows pricing with a direct checkout CTA on the dashboard when unsubscribed', function () {
    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('$297')
        ->assertSee('Unlimited certificate generation')
        ->assertSee('Subscribe Now')
        ->assertSee(route('resale-cert.checkout'));
});

it('redirects the old subscribe url to the dashboard', function () {
    $this->get(route('resale-cert.subscribe'))
        ->assertRedirect(route('resale-cert.dashboard'));
});

it('activates the subscription through the keyless stub checkout', function () {
    config(['cashier.secret' => '']);

    Livewire::test(SubscriptionCheckout::class)
        ->assertRedirect(route('resale-cert.payment-confirmation'));

    expect($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();

    $payment = Payment::where('business_id', $this->business->id)->first();

    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->provider)->toBe('stub')
        ->and($payment->amount_cents)->toBe(29700)
        ->and($payment->billing_type)->toBe('subscription');
});

it('redirects an already-subscribed business away from checkout', function () {
    subscribeToResaleCerts($this->business);

    Livewire::test(SubscriptionCheckout::class)
        ->assertRedirect(route('resale-cert.dashboard'));
});

it('shows a grace-period notice on the dashboard for canceled subscriptions', function () {
    $this->business->subscriptions()->create([
        'type' => config('resale_cert.subscription_type'),
        'stripe_id' => 'stub_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => 'stub_price',
        'quantity' => 1,
        'ends_at' => now()->addDays(20),
    ]);

    App\Domains\ResaleCert\Models\ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $this->get(route('resale-cert.dashboard'))
        ->assertSuccessful()
        ->assertSee('canceled and access ends');
});

it('shows payment success on the confirmation page once subscribed', function () {
    subscribeToResaleCerts($this->business);

    $this->get(route('resale-cert.payment-confirmation'))
        ->assertSuccessful()
        ->assertSee('subscription is active');
});

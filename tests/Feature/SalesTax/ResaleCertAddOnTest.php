<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Domains\SalesTax\Livewire\RegistrationCheckout;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Domains\SalesTax\Services\ResaleCertAddOn;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Support\SignupIntent;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\Subscription as StripeSubscription;

/*
 * EREG-116: "Add resale certificates" on the sales tax registration order.
 * The registration PaymentIntent charges the registration only; the yearly
 * resale_cert subscription starts on the saved card after it succeeds, once,
 * and never at the registration's expense.
 */

/**
 * @param  array<int, string>  $states
 */
function addOnDraft(Business $business, User $user, array $states = ['TX']): FormApplication
{
    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $user->id,
    ]);

    foreach ($states as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    return $application;
}

/**
 * A Stripe registration payment that asked for the add-on, not yet marked paid.
 *
 * @param  array<string, mixed>  $meta
 */
function addOnRegistrationPayment(FormApplication $application, array $meta = []): Payment
{
    return Payment::create([
        'purchasable_type' => $application->getMorphClass(),
        'purchasable_id' => $application->id,
        'business_id' => $application->business_id,
        'price_id' => Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->id,
        'stripe_payment_intent_id' => 'pi_registration_'.$application->id,
        'amount_cents' => 19900,
        'currency' => 'usd',
        'status' => PaymentStatus::Initiated,
        'provider' => 'stripe',
        'livemode' => false,
        'meta' => array_merge([
            'state_count' => 1,
            'per_state_cents' => 19900,
            'rush' => false,
            'rush_cents' => 0,
            'resale_certs' => true,
            'resale_certs_cents' => 29700,
        ], $meta),
    ]);
}

function succeededRegistrationIntent(int $amount = 19900): PaymentIntent
{
    return PaymentIntent::constructFrom([
        'id' => 'pi_registration',
        'status' => 'succeeded',
        'amount_received' => $amount,
        'currency' => 'usd',
        'latest_charge' => 'ch_registration',
        'customer' => 'cus_addon',
        'payment_method' => 'pm_saved_card',
        'setup_future_usage' => 'off_session',
    ]);
}

function startedResaleSubscription(): StripeSubscription
{
    return StripeSubscription::constructFrom([
        'id' => 'sub_addon_123',
        'object' => 'subscription',
        'status' => 'active',
        'latest_invoice' => [
            'id' => 'in_addon_123',
            'object' => 'invoice',
            'amount_paid' => 29700,
            'payments' => [
                'object' => 'list',
                'data' => [[
                    'object' => 'invoice_payment',
                    'payment' => ['type' => 'payment_intent', 'payment_intent' => 'pi_addon_invoice'],
                ]],
            ],
        ],
    ]);
}

/**
 * A paid registration whose add-on already has a recorded result.
 *
 * @param  array<string, mixed>  $result
 */
function paidAddOnOrder(Business $business, User $user, array $result): FormApplication
{
    $application = addOnDraft($business, $user);
    $application->update(['paid_at' => now()]);

    $payment = addOnRegistrationPayment($application, ['resale_certs_result' => $result]);
    $payment->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);

    return $application;
}

/**
 * The add-on service with its three Stripe calls faked, bound in the
 * container so the payment service and controller use it.
 */
function fakeAddOnStripe(): Mockery\MockInterface
{
    $addOn = Mockery::mock(ResaleCertAddOn::class, [app(ResaleCertPaymentService::class)])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $addOn->shouldReceive('retrievePaymentIntent')->andReturn(succeededRegistrationIntent())->byDefault();
    $addOn->shouldReceive('findStartedSubscription')->andReturn(null)->byDefault();

    app()->instance(ResaleCertAddOn::class, $addOn);

    return $addOn;
}

beforeEach(function () {
    // Keyless by default: the checkout takes its stub path instead of
    // creating real test-mode customers and PaymentIntents.
    config(['cashier.secret' => null]);

    // Came from a resale certificate ads page.
    $this->user = User::factory()->create(['signup_landing_path' => '/lp/resale-certificate/tx']);
    $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'rush', 'billing_type' => 'one_time'],
        ['amount_cents' => 9900, 'currency' => 'usd', 'active' => true],
    );

    $this->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

describe('order screen', function () {
    it('offers resale certificates to someone from a resale page and adds them to the total today', function () {
        $application = addOnDraft($this->business, $this->user);

        Livewire::test(RegistrationCheckout::class, ['application' => $application])
            ->assertSet('resaleCertCents', 29700)
            ->assertSee('Add resale certificates: $297 a year, unlimited, every state you buy in')
            ->assertSet('addResaleCerts', false)
            ->assertDontSeeText('Total today')
            ->set('addResaleCerts', true)
            ->assertSee('Resale certificates, first year')
            ->assertSee('Total today')
            ->assertSee('$496.00')
            ->assertSee('Resale certificates renew each year at $297.00. You can cancel anytime.')
            ->set('processing', 'rush')
            ->assertSee('$595.00')
            // The registration charge itself stays registration-only.
            ->assertSet('amountCents', 29800)
            ->call('continueToPayment')
            ->assertRedirect(route('sales-tax.registrations.checkout', [
                'application' => $application,
                'step' => 'pay',
                'certificates' => 1,
            ]));
    });

    it('offers them through the sign-up intent when the landing path was lost', function (array $intent) {
        $this->user->forceFill(['signup_landing_path' => null])->saveQuietly();
        SignupIntent::store($intent);

        Livewire::test(RegistrationCheckout::class, ['application' => addOnDraft($this->business, $this->user)])
            ->assertSet('resaleCertCents', 29700);
    })->with([
        'resale-cert product' => [['product' => 'resale-cert', 'state' => 'TX']],
        'resale page "register me first"' => [['product' => 'sales-tax', 'intent' => 'resale-certificate', 'state' => 'TX']],
    ]);

    it('does not offer them to someone from the sales tax pages', function () {
        $this->user->forceFill(['signup_landing_path' => '/lp/sales-tax/tx'])->saveQuietly();

        Livewire::test(RegistrationCheckout::class, ['application' => addOnDraft($this->business, $this->user)])
            ->assertSet('resaleCertCents', null)
            ->assertDontSeeText('Add resale certificates')
            ->set('addResaleCerts', true)
            ->assertSet('addResaleCerts', false)
            ->assertDontSeeText('Total today');
    });

    it('does not offer them to a business that already subscribes', function () {
        subscribeToResaleCerts($this->business);

        Livewire::test(RegistrationCheckout::class, ['application' => addOnDraft($this->business, $this->user)])
            ->assertSet('resaleCertCents', null)
            ->assertDontSeeText('Add resale certificates');
    });

    it('keeps the box ticked when going back from the pay step', function () {
        $application = addOnDraft($this->business, $this->user);

        Livewire::withQueryParams(['certificates' => '1'])
            ->test(RegistrationCheckout::class, ['application' => $application])
            ->assertSet('step', 'order')
            ->assertSet('addResaleCerts', true)
            ->assertSee('$496.00');
    });
});

describe('paying (keyless stub)', function () {
    it('charges the registration alone and then starts the certificate subscription', function () {
        Mail::fake();

        $application = addOnDraft($this->business, $this->user);

        Livewire::withQueryParams(['step' => 'pay', 'certificates' => '1'])
            ->test(RegistrationCheckout::class, ['application' => $application])
            ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));

        $registration = $application->fresh()->payment;

        expect($application->fresh()->paid_at)->not->toBeNull()
            ->and($registration->amount_cents)->toBe(19900)
            ->and($registration->meta['resale_certs'])->toBeTrue()
            ->and($registration->meta['resale_certs_cents'])->toBe(29700)
            ->and($registration->meta['resale_certs_result']['status'])->toBe('created')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();

        $addOn = Payment::find($registration->meta['resale_certs_result']['payment_id']);

        expect($addOn->amount_cents)->toBe(29700)
            ->and($addOn->status)->toBe(PaymentStatus::Succeeded)
            ->and($addOn->billing_type)->toBe('subscription')
            ->and($addOn->purchasable->is($this->business))->toBeTrue();

        Mail::assertQueued(PaymentReceipt::class, fn (PaymentReceipt $mail) => $mail->payment->is($addOn));
    });

    it('ignores ?certificates=1 for someone who was not offered them', function () {
        $this->user->forceFill(['signup_landing_path' => '/lp/sales-tax/tx'])->saveQuietly();
        $application = addOnDraft($this->business, $this->user);

        Livewire::withQueryParams(['step' => 'pay', 'certificates' => '1'])
            ->test(RegistrationCheckout::class, ['application' => $application]);

        expect($application->fresh()->payment->meta['resale_certs'])->toBeFalse()
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeFalse();
    });

    it('leaves the order alone without the add-on', function () {
        $application = addOnDraft($this->business, $this->user);

        Livewire::withQueryParams(['step' => 'pay'])
            ->test(RegistrationCheckout::class, ['application' => $application]);

        expect($application->fresh()->payment->meta['resale_certs'])->toBeFalse()
            ->and($application->fresh()->payment->meta)->not->toHaveKey('resale_certs_result')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeFalse();
    });
});

describe('starting the subscription after a Stripe payment', function () {
    beforeEach(function () {
        // Live-mode code path; every Stripe call is faked below.
        config(['cashier.secret' => 'sk_test_fake_for_tests']);
        Mail::fake();
    });

    it('starts the yearly subscription on the saved card once, however many callers', function () {
        $addOn = fakeAddOnStripe();
        $addOn->shouldReceive('createSubscription')
            ->once()
            ->withArgs(function (array $params, string $key) {
                return $params['customer'] === 'cus_addon'
                    && $params['default_payment_method'] === 'pm_saved_card'
                    && $params['items'] === [['price' => app(ResaleCertPaymentService::class)->price()->stripePriceId()]]
                    && $params['off_session'] === true
                    && $params['payment_behavior'] === 'error_if_incomplete'
                    && $params['metadata']['app_domain'] === 'resale_cert'
                    && $key === 'resale_certs_addon_'.$params['metadata']['sales_tax_payment_id'];
            })
            ->andReturn(startedResaleSubscription());

        $application = addOnDraft($this->business, $this->user);
        $payment = addOnRegistrationPayment($application);

        // The webhook, then the confirmation page's fallback, then its
        // receipt render: one subscription.
        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());
        app(RegistrationPaymentService::class)->markSucceeded($payment->fresh(), succeededRegistrationIntent());
        $outcome = app(ResaleCertAddOn::class)->outcome($payment->fresh());

        $payment->refresh();

        expect($outcome['status'])->toBe('created')
            ->and($payment->status)->toBe(PaymentStatus::Succeeded)
            ->and($payment->amount_cents)->toBe(19900)
            ->and($application->fresh()->paid_at)->not->toBeNull()
            ->and($payment->meta['resale_certs_result']['subscription_id'])->toBe('sub_addon_123');

        $subscription = $this->business->subscriptions()->where('stripe_id', 'sub_addon_123')->first();

        expect($subscription->type)->toBe(config('resale_cert.subscription_type'))
            ->and($subscription->stripe_status)->toBe('active')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();

        $addOnPayment = Payment::where('stripe_subscription_id', 'sub_addon_123')->sole();

        expect($addOnPayment->amount_cents)->toBe(29700)
            ->and($addOnPayment->stripe_invoice_id)->toBe('in_addon_123')
            ->and($addOnPayment->stripe_payment_intent_id)->toBe('pi_addon_invoice')
            ->and($addOnPayment->meta['sales_tax_payment_id'])->toBe($payment->id);

        Mail::assertQueued(PaymentReceipt::class, fn (PaymentReceipt $mail) => $mail->payment->is($addOnPayment));
    });

    it('starts it from the Stripe webhook', function () {
        config(['cashier.webhook.secret' => 'whsec_test_secret']);

        $addOn = fakeAddOnStripe();
        $addOn->shouldReceive('createSubscription')->once()->andReturn(startedResaleSubscription());

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user));

        $payload = [
            'id' => 'evt_addon_'.uniqid(),
            'object' => 'event',
            'type' => 'payment_intent.succeeded',
            'data' => ['object' => [
                'id' => $payment->stripe_payment_intent_id,
                'object' => 'payment_intent',
                'amount_received' => 19900,
                'currency' => 'usd',
                'latest_charge' => 'ch_webhook',
                'metadata' => ['app_payment_id' => (string) $payment->id, 'app_domain' => 'tax'],
            ]],
        ];
        $json = json_encode($payload);
        $timestamp = time();

        $this->postJson(route('webhooks.stripe'), $payload, [
            'Stripe-Signature' => "t={$timestamp},v1=".hash_hmac('sha256', $timestamp.'.'.$json, 'whsec_test_secret'),
        ])->assertOk();

        expect($payment->fresh()->meta['resale_certs_result']['status'])->toBe('created')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();
    });

    it('never blocks or changes the registration when the card is declined', function () {
        Exceptions::fake();

        $addOn = fakeAddOnStripe();
        $addOn->shouldReceive('createSubscription')
            ->once()
            ->andThrow(CardException::factory('Your card was declined.', 402, null, null, null, 'card_declined'));

        $application = addOnDraft($this->business, $this->user);
        $payment = addOnRegistrationPayment($application);

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());

        $payment->refresh();
        $application->refresh();

        expect($payment->status)->toBe(PaymentStatus::Succeeded)
            ->and($payment->amount_cents)->toBe(19900)
            ->and($application->paid_at)->not->toBeNull()
            ->and($application->status)->toBe('draft')
            ->and($payment->meta['resale_certs_result']['status'])->toBe('failed')
            ->and($payment->meta['resale_certs_result']['error'])->toBe('Your card was declined.')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeFalse()
            ->and(Payment::where('business_id', $this->business->id)->count())->toBe(1);

        Exceptions::assertReported(fn (RuntimeException $e) => str_contains($e->getMessage(), 'Resale certificate add-on not started'));

        // A later call does not try the card again.
        app(ResaleCertAddOn::class)->fulfil($payment);
    });

    it('tries again after an uncertain failure without charging twice', function () {
        Exceptions::fake();

        $calls = 0;
        $addOn = fakeAddOnStripe();
        $addOn->shouldReceive('createSubscription')
            ->twice()
            ->andReturnUsing(function () use (&$calls) {
                if (++$calls === 1) {
                    throw ApiConnectionException::factory('Could not connect to Stripe.');
                }

                return startedResaleSubscription();
            });

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user));

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());

        // Unresolved: nothing recorded, the team is told, the page says "pending".
        expect($payment->fresh()->meta)->not->toHaveKey('resale_certs_result')
            ->and($payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
        Exceptions::assertReported(ApiConnectionException::class);

        // The confirmation page's call goes through with the same idempotency key.
        expect(app(ResaleCertAddOn::class)->outcome($payment->fresh())['status'])->toBe('created');
    });

    it('adopts a subscription Stripe already started instead of creating another', function () {
        $addOn = fakeAddOnStripe();
        $addOn->shouldReceive('findStartedSubscription')->andReturn(startedResaleSubscription());
        $addOn->shouldNotReceive('createSubscription');

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user));

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());

        expect($payment->fresh()->meta['resale_certs_result']['subscription_id'])->toBe('sub_addon_123')
            ->and($this->business->fresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();
    });

    it('skips a business that subscribed in the meantime', function () {
        $addOn = fakeAddOnStripe();
        $addOn->shouldNotReceive('createSubscription');
        subscribeToResaleCerts($this->business);

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user));

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());

        expect($payment->fresh()->meta['resale_certs_result'])->toMatchArray(['status' => 'skipped', 'reason' => 'already_subscribed']);
    });

    it('does not start it when the registration payment is flagged for review', function () {
        Exceptions::fake();

        $addOn = fakeAddOnStripe();
        $addOn->shouldNotReceive('createSubscription');

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user));

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent(amount: 9900));

        expect($payment->fresh()->requires_manual_review)->toBeTrue()
            ->and($payment->fresh()->meta['resale_certs_result']['status'])->toBe('failed');
        Exceptions::assertReported(RuntimeException::class);
    });

    it('makes no Stripe calls for an order without the add-on', function () {
        $addOn = fakeAddOnStripe();
        $addOn->shouldNotReceive('retrievePaymentIntent');
        $addOn->shouldNotReceive('createSubscription');

        $payment = addOnRegistrationPayment(addOnDraft($this->business, $this->user), ['resale_certs' => false, 'resale_certs_cents' => 0]);

        app(RegistrationPaymentService::class)->markSucceeded($payment, succeededRegistrationIntent());

        expect($payment->fresh()->meta)->not->toHaveKey('resale_certs_result');
    });
});

describe('confirmation page', function () {
    it('says the certificates are active', function () {
        subscribeToResaleCerts($this->business);
        $application = paidAddOnOrder($this->business, $this->user, ['status' => 'created', 'amount_cents' => 29700]);

        $this->get(route('sales-tax.registrations.payment-confirmation', $application))
            ->assertOk()
            ->assertSee('We charged $199.00 for the registration')
            ->assertSee('and $297.00 for a year of resale certificates.')
            ->assertSee('Your resale certificates are active')
            ->assertDontSeeText('We could not start your resale certificates');
    });

    it('links to the resale checkout when the certificates did not start', function () {
        $application = paidAddOnOrder($this->business, $this->user, ['status' => 'failed', 'error' => 'Your card was declined.']);

        $this->get(route('sales-tax.registrations.payment-confirmation', $application))
            ->assertOk()
            ->assertSee('Payment received. Now the questions.')
            ->assertSee('We could not start your resale certificates')
            ->assertSee('We did not charge you for the certificates.')
            ->assertSee(route('resale-cert.checkout'))
            ->assertDontSeeText('Your card was declined.');
    });

    it('reports the $297 add-on to Google Ads and GA4 on the post-payment landing', function () {
        subscribeToResaleCerts($this->business);
        $application = paidAddOnOrder($this->business, $this->user, ['status' => 'created', 'amount_cents' => 29700, 'payment_id' => 98765]);
        $registration = $application->payments()->first();

        $html = $this->get(route('sales-tax.registrations.payment-confirmation', $application).'?payment_intent='.$registration->stripe_payment_intent_id)
            ->assertOk()
            ->getContent();

        // The registration's own conversion is unchanged: $199, keyed to its payment.
        expect($html)
            ->toContain('send_to: "AW-984288380/7C62CMuqrbYBEPyYrNUD",'."\n".'            value: 199.00,')
            ->toContain('transaction_id: "'.$registration->id.'"');

        // The add-on: the subscription conversion and a GA4 purchase, $297, keyed to the add-on payment.
        expect($html)
            ->toContain("window.gtag && gtag('event', 'conversion', {\n                send_to: \"AW-984288380/_vCOCIitwbgZEPyYrNUD\",\n                value: 297.00,\n                currency: \"USD\",\n                transaction_id: '98765'")
            ->toContain("transaction_id: '98765',\n                value: 297.00,")
            ->and(substr_count($html, 'AW-984288380/_vCOCIitwbgZEPyYrNUD'))->toBe(1)
            ->and(substr_count($html, "gtag('event', 'purchase'"))->toBe(2);
    });

    it('reports nothing for the add-on unless it started, or on a later visit', function (array $result, bool $landing) {
        $application = paidAddOnOrder($this->business, $this->user, $result);
        $registration = $application->payments()->first();
        $query = $landing ? '?payment_intent='.$registration->stripe_payment_intent_id : '';

        $html = $this->get(route('sales-tax.registrations.payment-confirmation', $application).$query)
            ->assertOk()
            ->getContent();

        expect($html)->not->toContain('AW-984288380/_vCOCIitwbgZEPyYrNUD')
            ->and(substr_count($html, "gtag('event', 'purchase'"))->toBe($landing ? 1 : 0);
    })->with([
        'failed' => [['status' => 'failed', 'error' => 'Your card was declined.'], true],
        'already subscribed' => [['status' => 'skipped', 'reason' => 'already_subscribed'], true],
        'created, but a later visit' => [['status' => 'created', 'amount_cents' => 29700, 'payment_id' => 98765], false],
    ]);

    it('says nothing about certificates for an order without them', function () {
        $application = addOnDraft($this->business, $this->user);
        $application->update(['paid_at' => now()]);
        addOnRegistrationPayment($application, ['resale_certs' => false])
            ->update(['status' => PaymentStatus::Succeeded, 'paid_at' => now()]);

        $this->get(route('sales-tax.registrations.payment-confirmation', $application))
            ->assertOk()
            ->assertDontSeeText('resale certificates are active')
            ->assertDontSeeText('We could not start your resale certificates');
    });
});

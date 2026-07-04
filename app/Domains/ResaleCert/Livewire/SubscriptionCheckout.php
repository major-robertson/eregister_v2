<?php

namespace App\Domains\ResaleCert\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Stripe\StripeClient;

/**
 * Resale Certificate Generator subscription checkout — $297/yr, embedded
 * on-page card entry. Uses Stripe's embedded-subscription pattern (create
 * with payment_behavior=default_incomplete, confirm the first invoice's
 * PaymentIntent in-page), mirroring FormationCheckout minus the one-time
 * state fee.
 */
class SubscriptionCheckout extends Component
{
    public Business $business;

    public int $amountCents = 0;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public string $paymentId = '';

    public bool $isReady = false;

    public function mount(ResaleCertPaymentService $paymentService): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        // Already subscribed (browser back button, double click) — nothing to buy.
        if ($business->subscribed(config('resale_cert.subscription_type'))) {
            $this->redirect(route('resale-cert.dashboard'));

            return;
        }

        $this->business = $business;
        $this->amountCents = $paymentService->price()->amount_cents ?? 29700;

        $this->initializePayment($paymentService);
    }

    protected function initializePayment(ResaleCertPaymentService $paymentService): void
    {
        // Stub for keyless local dev: activate the subscription without Stripe.
        if (blank(config('cashier.secret'))) {
            $this->stubCheckout($paymentService);

            return;
        }

        $this->business->createOrGetStripeCustomer();

        $payment = DB::transaction(function () use ($paymentService) {
            $payment = Payment::findRetryableForWithLock($this->business);

            if (! $payment) {
                $payment = Payment::create([
                    'purchasable_type' => $this->business->getMorphClass(),
                    'purchasable_id' => $this->business->id,
                    'business_id' => $this->business->id,
                    'price_id' => $paymentService->price()->id,
                    'amount_cents' => $this->amountCents,
                    'currency' => 'usd',
                    'status' => PaymentStatus::Initiated,
                    'provider' => 'stripe',
                    'billing_type' => 'subscription',
                    'livemode' => Payment::isLiveMode(),
                ]);
            }

            return $payment;
        });

        $this->paymentId = $payment->id;
        $stripe = new StripeClient(config('cashier.secret'));
        $metadata = $this->stripeMetadata($payment);

        // Reuse the open subscription's PaymentIntent if we already created one.
        if ($payment->stripe_payment_intent_id) {
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                app(ResaleCertPaymentService::class)->markSucceeded($payment, $pi);
                $this->redirect(route('resale-cert.payment-confirmation'));

                return;
            }

            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        $recurringPriceId = $paymentService->price()->stripePriceId();

        if (! $recurringPriceId) {
            throw new \RuntimeException(
                'Resale cert Stripe price is not configured. Seed ResaleCertPriceSeeder with '.
                'stripe_price_id_test / stripe_price_id_live values.'
            );
        }

        $subscription = $stripe->subscriptions->create([
            'customer' => $this->business->stripeId(),
            'items' => [['price' => $recurringPriceId]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.confirmation_secret'],
            'metadata' => $metadata,
        ], ['idempotency_key' => 'resale_sub_'.$payment->id]);

        $invoice = $subscription->latest_invoice;
        $clientSecret = $invoice->confirmation_secret->client_secret ?? null;

        if (! $clientSecret) {
            throw new \RuntimeException('Stripe did not return a confirmation secret for the first invoice.');
        }

        // The PaymentIntent id is the prefix of its client secret (pi_x_secret_y).
        $paymentIntentId = strstr($clientSecret, '_secret_', true);

        // Tag the PI so payment_intent.* webhooks route to the resale_cert domain.
        $stripe->paymentIntents->update($paymentIntentId, ['metadata' => $metadata]);

        $payment->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_invoice_id' => $invoice->id,
        ]);

        $this->paymentIntentId = $paymentIntentId;
        $this->clientSecret = $clientSecret;
        $this->isReady = true;
    }

    /**
     * @return array<string, int|string>
     */
    protected function stripeMetadata(Payment $payment): array
    {
        return [
            'app_payment_id' => $payment->id,
            'app_domain' => 'resale_cert',
            'payment_kind' => 'resale_cert_subscription',
            'business_id' => $this->business->id,
        ];
    }

    /**
     * Keyless local-dev path: record a succeeded payment + a stub
     * subscription without touching Stripe.
     */
    protected function stubCheckout(ResaleCertPaymentService $paymentService): void
    {
        $price = $paymentService->price();

        DB::transaction(function () use ($price) {
            Payment::create([
                'purchasable_type' => $this->business->getMorphClass(),
                'purchasable_id' => $this->business->id,
                'business_id' => $this->business->id,
                'price_id' => $price->id,
                'amount_cents' => $this->amountCents,
                'currency' => 'usd',
                'status' => PaymentStatus::Succeeded,
                'provider' => 'stub',
                'billing_type' => 'subscription',
                'livemode' => false,
                'paid_at' => now(),
            ]);

            if (! $this->business->subscribed(config('resale_cert.subscription_type'))) {
                $this->business->subscriptions()->create([
                    'type' => config('resale_cert.subscription_type'),
                    'stripe_id' => 'stub_'.uniqid(),
                    'stripe_status' => 'active',
                    'stripe_price' => $price->stripePriceId() ?? 'stub_price',
                    'quantity' => 1,
                ]);
            }
        });

        session()->flash('success', 'Your subscription is now active.');

        $this->redirect(route('resale-cert.payment-confirmation'));
    }

    public function render(): View
    {
        return view('livewire.resale-cert.subscription-checkout', [
            'amountFormatted' => '$'.number_format($this->amountCents / 100, 2),
            'returnUrl' => route('resale-cert.payment-confirmation'),
        ])->layout('layouts.minimal', ['title' => 'Subscribe']);
    }
}

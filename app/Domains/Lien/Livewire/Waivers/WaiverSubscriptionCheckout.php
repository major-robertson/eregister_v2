<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Waivers\Services\WaiverPaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Stripe\StripeClient;

/**
 * Lien waiver subscription checkout: $99/mo or $990/yr (two months free),
 * embedded on-page card entry. Uses Stripe's embedded-subscription pattern
 * (create with payment_behavior=default_incomplete, confirm the first
 * invoice's PaymentIntent in-page), mirroring the resale-cert checkout plus
 * a monthly/yearly toggle. The toggle is a full redirect back to this route
 * with ?interval=. The PaymentIntent is bound to one price at creation, so
 * switching intervals must re-run mount and initialize a fresh one.
 */
class WaiverSubscriptionCheckout extends Component
{
    public Business $business;

    #[Url]
    public string $interval = 'monthly';

    public int $amountCents = 0;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public string $paymentId = '';

    public bool $isReady = false;

    public function mount(WaiverPaymentService $paymentService): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        // Already subscribed (browser back button, double click); nothing to buy.
        if ($business->subscribed(config('lien_waivers.subscription_type'))) {
            $this->redirect(route('lien.waivers.index'));

            return;
        }

        if (! in_array($this->interval, ['monthly', 'yearly'], true)) {
            $this->interval = 'monthly';
        }

        $this->business = $business;
        $price = $paymentService->price($this->interval);
        $this->amountCents = $price->amount_cents
            ?? config("lien_waivers.prices.{$this->interval}.amount_cents");

        $this->initializePayment($paymentService, $price);
    }

    protected function initializePayment(WaiverPaymentService $paymentService, Price $price): void
    {
        // Stub for keyless local dev: activate the subscription without Stripe.
        if (blank(config('cashier.secret'))) {
            $this->stubCheckout($price);

            return;
        }

        $this->business->createOrGetStripeCustomer();

        $payment = DB::transaction(function () use ($price) {
            // Not Payment::findRetryableForWithLock: that matches ANY open
            // payment on the business morph, so it could resume a pending
            // resale-cert subscription checkout (or this product's other
            // billing interval, whose Stripe subscription is bound to the
            // wrong price). Scope the retry lookup to this exact price row.
            $payment = Payment::whereMorphedTo('purchasable', $this->business)
                ->where('price_id', $price->id)
                ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::RequiresPaymentMethod])
                ->lockForUpdate()
                ->latest()
                ->first();

            if (! $payment) {
                $payment = Payment::create([
                    'purchasable_type' => $this->business->getMorphClass(),
                    'purchasable_id' => $this->business->id,
                    'business_id' => $this->business->id,
                    'price_id' => $price->id,
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
                $paymentService->markSucceeded($payment, $pi);
                $this->redirect(route('lien.waivers.payment-confirmation'));

                return;
            }

            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        $recurringPriceId = $price->stripePriceId();

        if (! $recurringPriceId) {
            throw new \RuntimeException(
                'Lien waiver Stripe price is not configured for interval "'.$this->interval.'". Set the '.
                'STRIPE_PRICE_LIEN_WAIVER_MONTHLY / STRIPE_PRICE_LIEN_WAIVER_YEARLY (and *_LIVE) env values.'
            );
        }

        $subscription = $stripe->subscriptions->create([
            'customer' => $this->business->stripeId(),
            'items' => [['price' => $recurringPriceId]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.confirmation_secret'],
            'metadata' => $metadata,
        ], ['idempotency_key' => 'lien_waiver_sub_'.$payment->id]);

        $invoice = $subscription->latest_invoice;
        $clientSecret = $invoice->confirmation_secret->client_secret ?? null;

        if (! $clientSecret) {
            throw new \RuntimeException('Stripe did not return a confirmation secret for the first invoice.');
        }

        // The PaymentIntent id is the prefix of its client secret (pi_x_secret_y).
        $paymentIntentId = strstr($clientSecret, '_secret_', true);

        // Tag the PI so payment_intent.* webhooks route to the lien_waiver domain.
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
            'app_domain' => 'lien_waiver',
            'payment_kind' => 'lien_waiver_subscription',
            'business_id' => $this->business->id,
        ];
    }

    /**
     * Keyless local-dev path: record a succeeded payment + a stub
     * subscription without touching Stripe.
     */
    protected function stubCheckout(Price $price): void
    {
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

            if (! $this->business->subscribed(config('lien_waivers.subscription_type'))) {
                $this->business->subscriptions()->create([
                    'type' => config('lien_waivers.subscription_type'),
                    'stripe_id' => 'stub_'.uniqid(),
                    'stripe_status' => 'active',
                    'stripe_price' => $price->stripePriceId() ?? 'stub_price',
                    'quantity' => 1,
                ]);
            }
        });

        session()->flash('success', 'Your subscription is now active.');

        $this->redirect(route('lien.waivers.payment-confirmation'));
    }

    public function render(): View
    {
        return view('livewire.lien.waivers.subscription-checkout', [
            'amountFormatted' => '$'.number_format($this->amountCents / 100, 2),
            'perLabel' => $this->interval === 'yearly' ? 'yr' : 'mo',
            'returnUrl' => route('lien.waivers.payment-confirmation'),
        ])->layout('layouts.minimal', ['title' => 'Subscribe']);
    }
}

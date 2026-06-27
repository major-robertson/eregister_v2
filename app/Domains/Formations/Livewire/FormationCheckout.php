<?php

namespace App\Domains\Formations\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Formations\Services\FormationPaymentService;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\Price;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Stripe\StripeClient;

/**
 * LLC formation checkout — embedded, on-page card entry (Stripe Payment
 * Element), matching the Sales Tax / Lien flows. No redirect to Stripe.
 *
 * Billed as the $299/yr membership subscription PLUS a one-time state filing
 * fee. Because a bare PaymentIntent can't open a subscription, this uses
 * Stripe's embedded-subscription pattern: create the subscription with
 * payment_behavior=default_incomplete, attach the one-time state fee to its
 * first invoice (a pending invoice item), and confirm that first invoice's
 * PaymentIntent in-page via the shared <x-billing.stripe-payment-element>.
 *
 * Billing is per-application (a business forms at most one LLC).
 */
class FormationCheckout extends Component
{
    public Business $business;

    public FormApplication $application;

    public string $stateCode = '';

    public int $membershipCents = 0;

    public int $stateFeeCents = 0;

    public int $totalCents = 0;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public string $paymentId = '';

    public bool $isReady = false;

    public function mount(FormApplication $application): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        if ($application->business_id !== $business->id) {
            abort(403);
        }

        // Already paid (e.g. browser back button) - send to the confirmation
        // page, which renders success without re-charging.
        if ($application->isPaid()) {
            $this->redirect(route('formations.payment-confirmation', $application));

            return;
        }

        // One LLC per company: block checking out a second LLC for a business
        // that already formed one (defense-in-depth behind the start guard).
        $alreadyFormed = FormApplication::where('business_id', $business->id)
            ->where('form_type', 'llc')
            ->where('id', '!=', $application->id)
            ->where(fn ($q) => $q->whereNotNull('paid_at')->orWhere('status', 'submitted'))
            ->exists();

        if ($alreadyFormed) {
            session()->flash('error', 'This company already has an LLC. A company can form only one LLC.');
            $this->redirect(route('formations.dashboard'));

            return;
        }

        Gate::authorize('checkout', $application);

        $this->business = $business;
        $this->application = $application;
        $this->stateCode = $application->selected_states[0] ?? '';

        $this->membershipCents = Price::resolve('formation', 'llc', 'membership', 'subscription')->amount_cents;
        $this->stateFeeCents = Price::resolve('formation', 'llc', $this->stateCode, 'one_time')->amount_cents;
        $this->totalCents = $this->membershipCents + $this->stateFeeCents;

        $this->initializePayment();
    }

    protected function initializePayment(): void
    {
        // Stub for keyless local dev: mark paid + submit without Stripe.
        if (blank(config('cashier.secret'))) {
            $this->stubCheckout();

            return;
        }

        $this->business->createOrGetStripeCustomer();

        $payment = DB::transaction(function () {
            $payment = Payment::findRetryableForWithLock($this->application);

            if (! $payment) {
                $price = Price::resolve('formation', 'llc', 'membership', 'subscription');
                $payment = Payment::create([
                    'purchasable_type' => $this->application->getMorphClass(),
                    'purchasable_id' => $this->application->id,
                    'business_id' => $this->business->id,
                    'price_id' => $price->id,
                    'amount_cents' => $this->totalCents,
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
                app(FormationPaymentService::class)->markSucceeded($payment, $pi);
                $this->redirect(route('formations.payment-confirmation', $this->application));

                return;
            }

            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        // Create the subscription (incomplete) with the one-time state fee on
        // its first invoice, then confirm that invoice's PaymentIntent in-page.
        $recurringPriceId = Price::resolve('formation', 'llc', 'membership', 'subscription')->stripePriceId();

        if (! $recurringPriceId) {
            throw new \RuntimeException(
                'LLC membership Stripe price is not configured. Seed FormationFeeSeeder with '.
                'the membership stripe_price_id_test / stripe_price_id_live values.'
            );
        }

        // Pending invoice item → swept into the subscription's first invoice.
        $stripe->invoiceItems->create([
            'customer' => $this->business->stripeId(),
            'amount' => $this->stateFeeCents,
            'currency' => 'usd',
            'description' => config("states.{$this->stateCode}").' LLC state filing fee',
            'metadata' => ['app_fee_kind' => 'llc_state_filing', 'state' => $this->stateCode],
        ], ['idempotency_key' => 'llc_statefee_'.$payment->id]);

        $subscription = $stripe->subscriptions->create([
            'customer' => $this->business->stripeId(),
            'items' => [['price' => $recurringPriceId]],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
            'expand' => ['latest_invoice.confirmation_secret'],
            'metadata' => $metadata,
        ], ['idempotency_key' => 'llc_sub_'.$payment->id]);

        $invoice = $subscription->latest_invoice;
        $clientSecret = $invoice->confirmation_secret->client_secret ?? null;

        if (! $clientSecret) {
            throw new \RuntimeException('Stripe did not return a confirmation secret for the first invoice.');
        }

        // The PaymentIntent id is the prefix of its client secret (pi_x_secret_y).
        $paymentIntentId = strstr($clientSecret, '_secret_', true);

        // Tag the PI so payment_intent.* webhooks route to the LLC domain + Payment.
        $stripe->paymentIntents->update($paymentIntentId, ['metadata' => $metadata]);

        $payment->update([
            'stripe_subscription_id' => $subscription->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_invoice_id' => $invoice->id,
        ]);

        EmailSequence::startFor(
            'abandon_checkout',
            $this->application,
            Auth::user(),
            $this->business,
            route('formations.checkout', $this->application)
        );

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
            'app_domain' => 'llc',
            'payment_kind' => 'llc_formation',
            'llc_application_id' => $this->application->id,
            'state' => $this->stateCode,
        ];
    }

    /**
     * Keyless local-dev path: record a succeeded payment + a stub subscription
     * and submit + lock the application without touching Stripe.
     */
    protected function stubCheckout(): void
    {
        $price = Price::resolve('formation', 'llc', 'membership', 'subscription');

        DB::transaction(function () use ($price) {
            Payment::create([
                'purchasable_type' => $this->application->getMorphClass(),
                'purchasable_id' => $this->application->id,
                'business_id' => $this->business->id,
                'price_id' => $price->id,
                'amount_cents' => $this->totalCents,
                'currency' => 'usd',
                'status' => PaymentStatus::Succeeded,
                'provider' => 'stub',
                'billing_type' => 'subscription',
                'livemode' => false,
                'paid_at' => now(),
            ]);

            if (! $this->business->subscribed('llc')) {
                $this->business->subscriptions()->create([
                    'type' => 'llc',
                    'stripe_id' => 'stub_'.uniqid(),
                    'stripe_status' => 'active',
                    'stripe_price' => $price->stripePriceId() ?? 'stub_price',
                    'quantity' => 1,
                ]);
            }

            $this->application->update([
                'paid_at' => now(),
                'status' => 'submitted',
                'submitted_at' => now(),
                'locked_at' => now(),
            ]);
        });

        session()->flash('success', 'Your application has been submitted successfully.');

        $this->redirect(route('formations.payment-confirmation', $this->application));
    }

    public function render(): View
    {
        return view('livewire.formations.checkout', [
            'stateName' => config("states.{$this->stateCode}"),
            'membershipFormatted' => '$'.number_format($this->membershipCents / 100, 2),
            'stateFeeFormatted' => '$'.number_format($this->stateFeeCents / 100, 2),
            'totalFormatted' => '$'.number_format($this->totalCents / 100, 2),
            'returnUrl' => route('formations.payment-confirmation', $this->application),
        ])->layout('layouts.minimal', ['title' => 'Checkout']);
    }
}

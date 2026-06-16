<?php

namespace App\Domains\SalesTax\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
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
 * Sales & Use Tax permit registration checkout.
 *
 * One payment per application = $199 x number of selected states, charged
 * via an inline-amount Stripe PaymentIntent (mirrors the Lien FilingCheckout
 * pattern). The amount is recomputed on every entry so a state-count change
 * between starting checkout and paying is reconciled against the open
 * PaymentIntent.
 */
class RegistrationCheckout extends Component
{
    public Business $business;

    public FormApplication $application;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public int $amountCents = 0;

    public int $stateCount = 0;

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
            $this->redirect(route('sales-tax.registrations.payment-confirmation', $application));

            return;
        }

        Gate::authorize('checkout', $application);

        $this->business = $business;
        $this->application = $application;

        $this->initializePayment();
    }

    protected function initializePayment(): void
    {
        // 1. Live pricing: $199 per selected state. Always recompute from the
        //    application; never trust a stale stored figure.
        $perStateCents = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents;
        $this->stateCount = $this->application->stateCount();
        $expected = $perStateCents * $this->stateCount;
        $this->amountCents = $expected;

        // Stub for keyless local dev: mark paid + submit without Stripe.
        if (blank(config('cashier.secret'))) {
            $this->stubCheckout($expected);

            return;
        }

        // 2. Ensure the Business has a Stripe Customer (idempotent).
        $this->business->createOrGetStripeCustomer();

        // 3. Find latest retryable payment row (locked for concurrency).
        $payment = DB::transaction(function () use ($expected) {
            $payment = Payment::findRetryableForWithLock($this->application);

            if (! $payment) {
                $price = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time');
                $payment = Payment::create([
                    'purchasable_type' => $this->application->getMorphClass(),
                    'purchasable_id' => $this->application->id,
                    'business_id' => $this->business->id,
                    'price_id' => $price->id,
                    'amount_cents' => $expected,
                    'currency' => 'usd',
                    'status' => PaymentStatus::Initiated,
                    'provider' => 'stripe',
                    'livemode' => Payment::isLiveMode(),
                ]);
            }

            return $payment;
        });

        $this->paymentId = $payment->id;

        $stripe = new StripeClient(config('cashier.secret'));
        $metadata = $this->stripeMetadata($payment);

        // 4. Reuse an existing PaymentIntent if present.
        if ($payment->stripe_payment_intent_id) {
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                app(RegistrationPaymentService::class)->markSucceeded($payment, $pi);
                $this->redirect(route('sales-tax.registrations.payment-confirmation', $this->application));

                return;
            }

            // Reconcile a stale amount (state count changed since the PI was
            // created). Stripe allows updating an open PI's amount, so the
            // same client_secret keeps working.
            if ((int) $pi->amount !== $expected) {
                $pi = $stripe->paymentIntents->update($pi->id, [
                    'amount' => $expected,
                    'metadata' => $metadata,
                ]);
                $payment->update(['amount_cents' => $expected]);
            } elseif ($payment->amount_cents !== $expected) {
                $payment->update(['amount_cents' => $expected]);
            }

            $this->amountCents = $payment->fresh()->amount_cents;
            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        // 5. Keep the stored amount aligned, then create a new PaymentIntent.
        if ($payment->amount_cents !== $expected) {
            $payment->update(['amount_cents' => $expected]);
        }

        $pi = $stripe->paymentIntents->create([
            'amount' => $expected,
            'currency' => 'usd',
            'customer' => $this->business->stripeId(),
            'payment_method_types' => ['card'],
            'metadata' => $metadata,
        ], [
            'idempotency_key' => 'payment_'.$payment->id.'_'.$payment->created_at->timestamp,
        ]);

        $payment->update(['stripe_payment_intent_id' => $pi->id]);

        EmailSequence::startFor(
            'abandon_checkout',
            $this->application,
            Auth::user(),
            $this->business,
            route('sales-tax.registrations.checkout', $this->application)
        );

        $this->paymentIntentId = $pi->id;
        $this->clientSecret = $pi->client_secret;
        $this->isReady = true;
    }

    /**
     * @return array<string, int|string>
     */
    protected function stripeMetadata(Payment $payment): array
    {
        return [
            'app_payment_id' => $payment->id,
            'app_domain' => 'tax',
            'payment_kind' => 'sales_tax_registration',
            'sales_tax_application_id' => $this->application->id,
            'state_count' => $this->stateCount,
        ];
    }

    /**
     * Keyless local-dev path: record a succeeded payment and submit + lock
     * the application without touching Stripe.
     */
    protected function stubCheckout(int $expected): void
    {
        $price = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time');

        DB::transaction(function () use ($expected, $price) {
            $payment = Payment::create([
                'purchasable_type' => $this->application->getMorphClass(),
                'purchasable_id' => $this->application->id,
                'business_id' => $this->business->id,
                'price_id' => $price->id,
                'amount_cents' => $expected,
                'currency' => 'usd',
                'status' => PaymentStatus::Succeeded,
                'provider' => 'stub',
                'livemode' => false,
                'paid_at' => now(),
            ]);

            $this->application->update([
                'paid_at' => now(),
                'status' => 'submitted',
                'submitted_at' => now(),
                'locked_at' => now(),
            ]);

            $this->paymentId = $payment->id;
        });

        session()->flash('success', 'Your application has been submitted successfully.');

        $this->redirect(route('sales-tax.registrations.payment-confirmation', $this->application));
    }

    public function render(): View
    {
        return view('livewire.sales-tax.registration-checkout', [
            'formattedPrice' => '$'.number_format($this->amountCents / 100, 2),
            'returnUrl' => route('sales-tax.registrations.payment-confirmation', $this->application),
        ])->layout('layouts.minimal', ['title' => 'Checkout']);
    }
}

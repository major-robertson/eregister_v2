<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Services\LienPaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class FilingCheckout extends Component
{
    public Business $business;

    public LienFiling $filing;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public int $amountCents = 0;

    public string $paymentId = '';

    public bool $isReady = false;

    public function mount(LienFiling $filing): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('checkout', $filing);

        if ($filing->business_id !== $business->id) {
            abort(403);
        }

        $this->business = $business;
        $this->filing = $filing;

        $this->initializePayment();
    }

    protected function initializePayment(): void
    {
        // 1. Get pricing from single source of truth
        $price = Price::resolve(
            'lien',
            $this->filing->documentType->slug,
            $this->filing->service_level->value,
            'one_time'
        );
        $this->amountCents = $price->amount_cents;

        // 2. Ensure Business has Stripe Customer (idempotent)
        $this->business->createOrGetStripeCustomer();

        // 3. Find latest retryable payment row (with lock for concurrency safety)
        $payment = DB::transaction(function () use ($price) {
            $payment = Payment::findRetryableForWithLock($this->filing);

            if (! $payment) {
                $payment = Payment::create([
                    'purchasable_type' => $this->filing->getMorphClass(),
                    'purchasable_id' => $this->filing->id,
                    'business_id' => $this->business->id,
                    'price_id' => $price->id,
                    'amount_cents' => $price->amount_cents,
                    'currency' => 'usd',
                    'status' => PaymentStatus::Initiated,
                    'provider' => 'stripe',
                    'livemode' => Payment::isLiveMode(),
                ]);
            }

            return $payment;
        });

        $this->paymentId = $payment->id;
        $this->amountCents = $payment->amount_cents;

        // 4. If PaymentIntent exists, check its status
        if ($payment->stripe_payment_intent_id) {
            $stripe = new StripeClient(config('cashier.secret'));
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                // PI already succeeded - mark DB and redirect to success
                app(LienPaymentService::class)->markSucceeded($payment, $pi);
                $this->redirect(route('lien.filings.payment-confirmation', $this->filing));

                return;
            }

            // Reuse existing PI
            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        // 5. Create new PaymentIntent with idempotency key
        $stripe = new StripeClient(config('cashier.secret'));
        $pi = $stripe->paymentIntents->create([
            'amount' => $payment->amount_cents,
            'currency' => 'usd',
            'customer' => $this->business->stripeId(),
            'payment_method_types' => ['card'], // Card only - avoids redirect complexity
            'metadata' => [
                'app_payment_id' => $payment->id,
                'app_domain' => 'lien',
                'lien_filing_id' => $this->filing->id,
                'lien_filing_public_id' => $this->filing->public_id,
                'lien_document_type' => $this->filing->documentType->slug,
                'lien_service_level' => $this->filing->service_level->value,
            ],
        ], [
            // Include created_at timestamp to avoid conflicts after DB resets
            'idempotency_key' => 'payment_'.$payment->id.'_'.$payment->created_at->timestamp,
        ]);

        // 6. Persist PaymentIntent ID
        $payment->update(['stripe_payment_intent_id' => $pi->id]);

        $this->paymentIntentId = $pi->id;
        $this->clientSecret = $pi->client_secret;
        $this->isReady = true;
    }

    public function render(): View
    {
        return view('livewire.lien.filing-checkout', [
            'formattedPrice' => '$'.number_format($this->amountCents / 100, 2),
            'returnUrl' => route('lien.filings.payment-confirmation', $this->filing),
        ])->layout('layouts.minimal', ['title' => 'Checkout']);
    }
}

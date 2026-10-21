<?php

namespace App\Domains\Lien\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Services\LienPaymentService;
use App\Domains\Lien\Services\RushTier;
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

class FilingCheckout extends Component
{
    public Business $business;

    public LienFiling $filing;

    public string $paymentIntentId = '';

    public string $clientSecret = '';

    public int $amountCents = 0;

    /**
     * What the payment charges, one row per price: the service, then any
     * rush fee. Stored on the payment as meta['items'].
     *
     * @var list<array{price_id: int, product_key: string, variant_key: string, label: string, amount_cents: int}>
     */
    public array $items = [];

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

        $referralStates = config('lien.attorney_referral_states', []);
        if (in_array($filing->jurisdiction_state, $referralStates, true)
            && $filing->documentType->slug === 'mechanics_lien') {
            $stateName = config('states.'.$filing->jurisdiction_state, $filing->jurisdiction_state);
            abort(403, $stateName.' mechanics liens must be filed directly with the court.');
        }

        $this->business = $business;
        $this->filing = $filing;

        $rushPrice = $this->syncRushTier();

        if ($rushPrice === false) {
            return;
        }

        $this->initializePayment($rushPrice);
    }

    /**
     * Work the rush tier out again for today: the customer may pay a day or
     * more after the review step. A required tier wins; otherwise the Rush
     * the customer added on the review step, while it can still be added. A
     * changed tier is stored on the filing.
     * An expedite tier the customer has not accepted sends them back to the
     * review step to tick its terms (false). Otherwise the fee row to
     * charge, or null for none.
     */
    protected function syncRushTier(): Price|false|null
    {
        $required = RushTier::forFiling($this->filing);
        $tier = $required ?? ($this->filing->rush_elected && RushTier::electableForFiling($this->filing) ? RushTier::RUSH : null);
        $price = RushTier::price($tier);
        $tier = $price ? $tier : null;
        $elected = $required === null && $tier !== null;

        if ($tier !== $this->filing->rush_tier) {
            $this->filing->update(['rush_tier' => $tier, 'rush_elected' => $elected, 'rush_acknowledged_at' => null]);
        } elseif ($elected !== (bool) $this->filing->rush_elected) {
            $this->filing->update(['rush_elected' => $elected]);
        }

        if (RushTier::requiresAcknowledgement($tier) && $this->filing->rush_acknowledged_at === null) {
            $this->redirect(route('lien.filings.start', [
                'project' => $this->filing->project,
                'deadline' => $this->filing->projectDeadline,
                'rush' => 'changed',
            ]));

            return false;
        }

        return $price;
    }

    /**
     * The service price, then the rush fee when one applies.
     *
     * @return list<array{price_id: int, product_key: string, variant_key: string, label: string, amount_cents: int}>
     */
    protected function lineItems(Price $price, ?Price $rushPrice): array
    {
        $serviceLevel = $this->filing->service_level?->label();

        $items = [[
            'price_id' => $price->id,
            'product_key' => $price->product_key,
            'variant_key' => $price->variant_key,
            'label' => $this->filing->documentType->name.($serviceLevel ? ' ('.$serviceLevel.')' : ''),
            'amount_cents' => $price->amount_cents,
        ]];

        if ($rushPrice) {
            $items[] = [
                'price_id' => $rushPrice->id,
                'product_key' => $rushPrice->product_key,
                'variant_key' => $rushPrice->variant_key,
                'label' => RushTier::itemLabel($rushPrice->variant_key),
                'amount_cents' => $rushPrice->amount_cents,
            ];
        }

        return $items;
    }

    protected function initializePayment(?Price $rushPrice): void
    {
        // 1. Get pricing from single source of truth (state-aware for liens),
        //    plus the rush fee. Recomputed on every visit; a reused payment
        //    row or PaymentIntent is brought in line below.
        $price = Price::resolveLien(
            $this->filing->documentType->slug,
            $this->filing->service_level->value,
            $this->filing->jurisdiction_state,
        );
        $this->items = $this->lineItems($price, $rushPrice);
        $expected = array_sum(array_column($this->items, 'amount_cents'));
        $this->amountCents = $expected;

        // 2. Ensure Business has Stripe Customer (idempotent)
        $this->business->createOrGetStripeCustomer();

        // 3. Find latest retryable payment row (with lock for concurrency safety)
        $payment = DB::transaction(function () use ($price, $expected) {
            $payment = Payment::findRetryableForWithLock($this->filing);

            if (! $payment) {
                $payment = Payment::create([
                    'purchasable_type' => $this->filing->getMorphClass(),
                    'purchasable_id' => $this->filing->id,
                    'business_id' => $this->business->id,
                    'price_id' => $price->id,
                    'amount_cents' => $expected,
                    'currency' => 'usd',
                    'status' => PaymentStatus::Initiated,
                    'provider' => 'stripe',
                    'livemode' => Payment::isLiveMode(),
                    'meta' => ['items' => $this->items],
                ]);
            }

            return $payment;
        });

        $this->paymentId = $payment->id;

        $stripe = $this->stripe();
        $metadata = $this->stripeMetadata($payment, $rushPrice);

        // 4. If PaymentIntent exists, check its status
        if ($payment->stripe_payment_intent_id) {
            $pi = $stripe->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            if ($pi->status === 'succeeded') {
                // PI already succeeded - mark DB and redirect to success
                app(LienPaymentService::class)->markSucceeded($payment, $pi);
                $this->redirect(route('lien.filings.payment-confirmation', $this->filing));

                return;
            }

            // The total changed since the PI was created (a rush fee came or
            // went, or the service changed): Stripe updates an open PI in
            // place, so the same client_secret keeps working.
            if ((int) $pi->amount !== $expected || ($pi->metadata['lien_rush_tier'] ?? null) !== $metadata['lien_rush_tier']) {
                $pi = $stripe->paymentIntents->update($pi->id, [
                    'amount' => $expected,
                    'metadata' => $metadata,
                ]);
            }

            $this->syncPayment($payment, $price, $expected);

            // Reuse existing PI
            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        // 5. Keep the stored amount aligned, then create a new PaymentIntent
        //    with idempotency key
        $this->syncPayment($payment, $price, $expected);

        $pi = $stripe->paymentIntents->create([
            'amount' => $expected,
            'currency' => 'usd',
            'customer' => $this->business->stripeId(),
            'payment_method_types' => ['card'], // Card only - avoids redirect complexity
            'metadata' => $metadata,
        ], [
            // Include created_at timestamp to avoid conflicts after DB resets
            'idempotency_key' => 'payment_'.$payment->id.'_'.$payment->created_at->timestamp,
        ]);

        // 6. Persist PaymentIntent ID
        $payment->update(['stripe_payment_intent_id' => $pi->id]);

        EmailSequence::startFor(
            'abandon_checkout',
            $this->filing,
            Auth::user(),
            $this->business,
            route('lien.filings.checkout', $this->filing)
        );

        $this->paymentIntentId = $pi->id;
        $this->clientSecret = $pi->client_secret;
        $this->isReady = true;
    }

    /**
     * Bring a reused payment row in line with today's total and items. Its
     * price_id stays the service price.
     */
    protected function syncPayment(Payment $payment, Price $price, int $expected): void
    {
        $meta = array_replace($payment->meta ?? [], ['items' => $this->items]);

        // Loose on meta: the JSON column hands keys back in its own order.
        if ($payment->amount_cents !== $expected || $payment->price_id !== $price->id || $payment->meta != $meta) {
            $payment->update([
                'price_id' => $price->id,
                'amount_cents' => $expected,
                'meta' => $meta,
            ]);
        }
    }

    /**
     * @return array<string, int|string>
     */
    protected function stripeMetadata(Payment $payment, ?Price $rushPrice): array
    {
        return [
            'app_payment_id' => $payment->id,
            'app_domain' => 'lien',
            'lien_filing_id' => $this->filing->id,
            'lien_filing_public_id' => $this->filing->public_id,
            'lien_document_type' => $this->filing->documentType->slug,
            'lien_service_level' => $this->filing->service_level->value,
            'lien_rush_tier' => $rushPrice?->variant_key ?? 'none',
        ];
    }

    /**
     * The Stripe client, resolved from the container the way Cashier does,
     * so tests can bind a fake.
     */
    protected function stripe(): StripeClient
    {
        return app(StripeClient::class, ['config' => config('cashier.secret')]);
    }

    public function render(): View
    {
        return view('livewire.lien.filing-checkout', [
            'formattedPrice' => '$'.number_format($this->amountCents / 100, 2),
            'returnUrl' => route('lien.filings.payment-confirmation', $this->filing),
        ])->layout('layouts.minimal', ['title' => 'Checkout']);
    }
}

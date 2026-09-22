<?php

namespace App\Domains\SalesTax\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Enums\PaymentStatus;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\Price;
use App\Support\Analytics\Gtag;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Stripe\StripeClient;

/**
 * Sales & Use Tax permit registration: order screen + checkout.
 *
 * Two screens on one URL. `order` (the default) lists the states, offers
 * rush processing and shows the total; "Continue to payment" stamps the
 * rush choice on the application and reloads with ?step=pay. `pay` charges
 * $199 x selected states (+ $99 rush) through an inline-amount Stripe
 * PaymentIntent (mirrors the Lien FilingCheckout pattern). The amount is
 * recomputed on every entry so a state-count or rush change between
 * starting checkout and paying is reconciled against the open PaymentIntent.
 *
 * The pay step is a full page load rather than a Livewire step switch: the
 * Payment Element's scripts are @push'ed by the view and only reach the
 * layout on the initial render.
 *
 * Payment comes BEFORE the questions (form_types.sales_tax_permit.pay_first):
 * RegistrationPaymentService::applyPayment marks the application paid and
 * leaves it open for the wizard. Drafts that already finished the questions
 * (pay-at-end drafts from before the order screen) skip the order step and
 * are locked on payment.
 */
class RegistrationCheckout extends Component
{
    public Business $business;

    public FormApplication $application;

    /** 'order' (states, rush choice, total) or 'pay' (card form). */
    public string $step = 'order';

    /** 'standard' (filed within 5 business days) or 'rush' (2 business days, +$99). */
    public string $processing = 'standard';

    public int $perStateCents = 0;

    /** Null when no rush price is configured; the option is then not offered. */
    public ?int $rushCents = null;

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

        // Already paid (browser back button, refresh): a submitted application
        // shows its receipt, an open one continues with the questions. Nothing
        // is charged twice.
        if ($application->isPaid()) {
            $this->redirect($this->afterPaymentUrl($application));

            return;
        }

        Gate::authorize('checkout', $application);

        $this->business = $business;
        $this->application = $application;

        $this->perStateCents = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time')->amount_cents;
        $this->rushCents = $this->resolveRushCents();
        $this->stateCount = $application->stateCount();
        $this->processing = $application->isRush() && $this->rushCents !== null ? 'rush' : 'standard';
        $this->amountCents = $this->expectedAmount();

        $this->step = $this->showsOrderScreen() && request()->query('step') !== 'pay' ? 'order' : 'pay';

        // GA4 funnel steps, drained into the page head on this render:
        // registration_order (the order screen) and begin_checkout (the card
        // form); purchase fires on the confirmation page.
        Gtag::queue($this->step === 'pay' ? 'begin_checkout' : 'registration_order', [
            'currency' => 'USD',
            'value' => $this->amountCents / 100,
            'states' => $this->stateCount,
            'rush' => $this->wantsRush(),
            'items' => [[
                'item_name' => 'Sales Tax Registration',
                'quantity' => $this->stateCount,
                'price' => $this->perStateCents / 100,
            ]],
        ]);

        if ($this->step === 'pay') {
            $this->initializePayment();

            return;
        }

        // Seeing the order without paying is the drop-off point now, so the
        // reminders start here (an hour, a day, three days later) and stop
        // on their own once the registration is paid.
        EmailSequence::startFor(
            'abandon_checkout',
            $this->application,
            Auth::user(),
            $this->business,
            route('sales-tax.registrations.checkout', $this->application)
        );
    }

    /**
     * The order screen belongs to the pay-first flow. A draft that already
     * answered everything (the wizard sent it here) has nothing left to
     * decide and goes straight to the card form.
     */
    protected function showsOrderScreen(): bool
    {
        return $this->application->paysFirst() && ! $this->questionsFinished();
    }

    protected function questionsFinished(): bool
    {
        return $this->application->isInReviewPhase() && $this->application->allStatesComplete();
    }

    public function updatedProcessing(string $value): void
    {
        $this->processing = $value === 'rush' && $this->rushCents !== null ? 'rush' : 'standard';
        $this->amountCents = $this->expectedAmount();
    }

    /**
     * Stamp the rush choice on the application (the admin board and the
     * receipt read it) and reload into the payment step.
     */
    public function continueToPayment(): void
    {
        $this->application->update([
            'rush_requested_at' => $this->wantsRush() ? ($this->application->rush_requested_at ?? now()) : null,
        ]);

        $this->redirect(route('sales-tax.registrations.checkout', [
            'application' => $this->application,
            'step' => 'pay',
        ]));
    }

    protected function wantsRush(): bool
    {
        return $this->processing === 'rush' && $this->rushCents !== null;
    }

    protected function expectedAmount(): int
    {
        return $this->perStateCents * $this->stateCount + ($this->wantsRush() ? $this->rushCents : 0);
    }

    protected function resolveRushCents(): ?int
    {
        try {
            return Price::resolve('tax', 'sales_tax_permit', 'rush', 'one_time')->amount_cents;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function afterPaymentUrl(FormApplication $application): string
    {
        return $application->isLocked()
            ? route('sales-tax.registrations.payment-confirmation', $application)
            : route('sales-tax.registrations.show', $application);
    }

    protected function initializePayment(): void
    {
        // 1. Live pricing: $199 per selected state (+ rush). Always recompute
        //    from the application; never trust a stale stored figure.
        $expected = $this->expectedAmount();
        $this->amountCents = $expected;
        $meta = $this->paymentMeta();

        // Stub for keyless local dev: record the payment without Stripe.
        if (blank(config('cashier.secret'))) {
            $this->stubCheckout($expected, $meta);

            return;
        }

        // 2. Ensure the Business has a Stripe Customer (idempotent).
        $this->business->createOrGetStripeCustomer();

        // 3. Find latest retryable payment row (locked for concurrency).
        $payment = DB::transaction(function () use ($expected, $meta) {
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
                    'meta' => $meta,
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

            // Reconcile a stale amount (state count or rush changed since the
            // PI was created). Stripe allows updating an open PI's amount, so
            // the same client_secret keeps working.
            if ((int) $pi->amount !== $expected) {
                $pi = $stripe->paymentIntents->update($pi->id, [
                    'amount' => $expected,
                    'metadata' => $metadata,
                ]);
            }

            if ($payment->amount_cents !== $expected || $payment->meta !== $meta) {
                $payment->update(['amount_cents' => $expected, 'meta' => $meta]);
            }

            $this->amountCents = $payment->fresh()->amount_cents;
            $this->paymentIntentId = $pi->id;
            $this->clientSecret = $pi->client_secret;
            $this->isReady = true;

            return;
        }

        // 5. Keep the stored amount aligned, then create a new PaymentIntent.
        if ($payment->amount_cents !== $expected || $payment->meta !== $meta) {
            $payment->update(['amount_cents' => $expected, 'meta' => $meta]);
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
     * The order breakdown, recorded on the payment row so the receipt and
     * the admin side can itemize it without re-deriving prices later.
     *
     * @return array{state_count: int, per_state_cents: int, rush: bool, rush_cents: int}
     */
    protected function paymentMeta(): array
    {
        return [
            'state_count' => $this->stateCount,
            'per_state_cents' => $this->perStateCents,
            'rush' => $this->wantsRush(),
            'rush_cents' => $this->wantsRush() ? (int) $this->rushCents : 0,
        ];
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
            'rush' => $this->wantsRush() ? 1 : 0,
        ];
    }

    /**
     * Keyless local-dev path: record a succeeded payment and apply it to the
     * application (paid and open for the questions, or locked when the
     * questions were already finished) without touching Stripe.
     *
     * @param  array{state_count: int, per_state_cents: int, rush: bool, rush_cents: int}  $meta
     */
    protected function stubCheckout(int $expected, array $meta): void
    {
        $price = Price::resolve('tax', 'sales_tax_permit', 'per_state', 'one_time');

        DB::transaction(function () use ($expected, $price, $meta) {
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
                'meta' => $meta,
            ]);

            app(RegistrationPaymentService::class)->applyPayment($this->application, null, $meta['rush']);

            $this->paymentId = $payment->id;
        });

        session()->flash('success', $this->application->isLocked()
            ? 'Your application has been submitted successfully.'
            : 'Payment received. Next, answer the questions so we can prepare your filing.');

        $this->redirect(route('sales-tax.registrations.payment-confirmation', $this->application));
    }

    public function render(): View
    {
        $stateNames = collect($this->application->selected_states ?? [])
            ->mapWithKeys(fn (string $code) => [$code => config("states.{$code}", $code)])
            ->all();

        return view('livewire.sales-tax.registration-checkout', [
            'stateNames' => $stateNames,
            'perStateFormatted' => '$'.number_format($this->perStateCents / 100, 2),
            'statesSubtotal' => '$'.number_format($this->perStateCents * $this->stateCount / 100, 2),
            'rushFormatted' => $this->rushCents !== null ? '$'.number_format($this->rushCents / 100, 2) : null,
            'formattedPrice' => '$'.number_format($this->amountCents / 100, 2),
            'showsOrderLink' => $this->showsOrderScreen(),
            'returnUrl' => route('sales-tax.registrations.payment-confirmation', $this->application),
            'orderUrl' => route('sales-tax.registrations.checkout', $this->application),
            'changeStatesUrl' => route('sales-tax.registrations.start'),
        ])->layout('layouts.minimal', ['title' => $this->step === 'pay' ? 'Checkout' : 'Your order']);
    }
}

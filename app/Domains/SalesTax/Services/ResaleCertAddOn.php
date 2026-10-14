<?php

namespace App\Domains\SalesTax\Services;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Services\ResaleCertPaymentService;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Models\User;
use App\Support\SignupIntent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Cashier;
use RuntimeException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeObject;

/**
 * "Add resale certificates" on the sales tax registration order (EREG-116).
 *
 * Someone who came from a resale certificate page can add the yearly
 * generator subscription to their registration order. The registration
 * PaymentIntent stays registration-only; when the add-on is chosen it also
 * saves the card for off-session use (setup_future_usage). Once that
 * payment has succeeded, fulfil() starts the resale_cert subscription on
 * the saved card, at the generator checkout's price.
 *
 * The registration never depends on it. fulfil() runs after the
 * registration is recorded and never throws. A definite failure (declined
 * card, unusable card) is logged, reported to Sentry and recorded on the
 * registration payment's meta, so the confirmation page can link to the
 * normal resale checkout. Anything uncertain (a network error mid-call) is
 * left unresolved and tried again on the next call.
 *
 * It runs once. The webhook and the confirmation page both call it; the
 * registration payment row stays locked while it runs, the Stripe call uses
 * an idempotency key, and a subscription Stripe already started for this
 * order is adopted rather than created twice.
 */
class ResaleCertAddOn
{
    /** The sign-up intent the resale ads page sends with "No, register me first". */
    public const INTENT = 'resale-certificate';

    public function __construct(private ResaleCertPaymentService $resalePayments) {}

    /**
     * The add-on price in cents when this person should see the offer, else
     * null: only people who came for resale certificates, and only while the
     * business has no resale subscription.
     */
    public function offerCents(User $user, Business $business): ?int
    {
        if (! static::cameForCertificates($user)) {
            return null;
        }

        if ($business->subscribed(config('resale_cert.subscription_type'))) {
            return null;
        }

        try {
            $price = $this->resalePayments->price();
        } catch (\Throwable) {
            return null;
        }

        // A live subscription needs the Stripe recurring price. Without it
        // the add-on could never start, so it is not offered.
        if (filled(config('cashier.secret')) && ! $price->stripePriceId()) {
            return null;
        }

        return $price->amount_cents;
    }

    /**
     * A resale certificate page sent them: the landing path, the product
     * door they clicked, or the resale page's "register me first" answer.
     */
    public static function cameForCertificates(User $user): bool
    {
        $intent = SignupIntent::get() ?? [];

        return $user->signedUpFromResaleCerts()
            || ($intent['product'] ?? null) === 'resale-cert'
            || ($intent['intent'] ?? null) === self::INTENT;
    }

    /**
     * What happened to the add-on, starting it first if it is still due:
     * null when the order has no add-on, else a result with a status of
     * created, failed, skipped or pending.
     *
     * @return array<string, mixed>|null
     */
    public function outcome(Payment $registrationPayment): ?array
    {
        if (empty($registrationPayment->meta['resale_certs'])) {
            return null;
        }

        return $this->fulfil($registrationPayment) ?? ['status' => 'pending'];
    }

    /**
     * Start the subscription for a paid registration that asked for it. Safe
     * to call any number of times. Returns the recorded result, or null when
     * there is nothing to record yet.
     *
     * @return array<string, mixed>|null
     */
    public function fulfil(Payment $registrationPayment): ?array
    {
        if (empty($registrationPayment->meta['resale_certs'])) {
            return null;
        }

        try {
            return DB::transaction(function () use ($registrationPayment) {
                $payment = Payment::lockForUpdate()->find($registrationPayment->id);

                if (! $payment || empty($payment->meta['resale_certs'])) {
                    return null;
                }

                if (isset($payment->meta['resale_certs_result'])) {
                    return $payment->meta['resale_certs_result'];
                }

                if ($payment->status !== PaymentStatus::Succeeded) {
                    return null;
                }

                $result = $this->start($payment) + ['at' => now()->toIso8601String()];

                $payment->update(['meta' => array_merge($payment->meta, ['resale_certs_result' => $result])]);

                return $result;
            });
        } catch (\Throwable $e) {
            // Unresolved (network error, lock timeout): the next call tries
            // again, and the idempotency key keeps that from charging twice.
            report($e);

            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function start(Payment $payment): array
    {
        $business = $payment->business;

        if ($business->subscribed(config('resale_cert.subscription_type'))) {
            return ['status' => 'skipped', 'reason' => 'already_subscribed'];
        }

        if ($payment->requires_manual_review) {
            return $this->failed($payment, 'The registration payment is waiting for a manual review.');
        }

        $price = $this->resalePayments->price();

        if (blank(config('cashier.secret'))) {
            return $this->startStub($payment, $business, $price->id, (int) $price->amount_cents);
        }

        $stripePriceId = $price->stripePriceId();

        if (! $stripePriceId || ! $payment->stripe_payment_intent_id) {
            return $this->failed($payment, 'No Stripe price or registration PaymentIntent to start from.');
        }

        $paymentIntent = $this->retrievePaymentIntent($payment->stripe_payment_intent_id);
        $customerId = is_string($paymentIntent->customer ?? null) ? $paymentIntent->customer : ($paymentIntent->customer->id ?? null);
        $paymentMethodId = is_string($paymentIntent->payment_method ?? null) ? $paymentIntent->payment_method : ($paymentIntent->payment_method->id ?? null);

        if (($paymentIntent->status ?? null) !== 'succeeded' || ! $customerId || ! $paymentMethodId) {
            return $this->failed($payment, 'The registration payment did not leave a saved card.');
        }

        try {
            $subscription = $this->findStartedSubscription($customerId, $payment->id)
                ?? $this->createSubscription([
                    'customer' => $customerId,
                    'items' => [['price' => $stripePriceId]],
                    'default_payment_method' => $paymentMethodId,
                    'off_session' => true,
                    // Charge now or create nothing: no half-started subscription.
                    'payment_behavior' => 'error_if_incomplete',
                    'metadata' => [
                        'app_domain' => 'resale_cert',
                        'payment_kind' => 'resale_cert_subscription',
                        'business_id' => $business->id,
                        'sales_tax_payment_id' => $payment->id,
                    ],
                    'expand' => ['latest_invoice.payments'],
                ], 'resale_certs_addon_'.$payment->id);
        } catch (CardException|InvalidRequestException $e) {
            // Stripe refused it outright, so nothing was created or charged.
            return $this->failed($payment, $e->getMessage());
        }

        return $this->recordStarted($payment, $business, $subscription, $price->id, $stripePriceId, (int) $price->amount_cents);
    }

    /**
     * @return array<string, mixed>
     */
    protected function recordStarted(Payment $payment, Business $business, StripeObject $subscription, int $priceId, string $stripePriceId, int $priceCents): array
    {
        $invoice = $subscription->latest_invoice ?? null;
        $invoiceId = is_string($invoice) ? $invoice : ($invoice->id ?? null);
        $invoicePaymentIntent = is_object($invoice) ? ($invoice->payments->data[0]->payment->payment_intent ?? null) : null;
        $amountPaid = is_object($invoice) && isset($invoice->amount_paid) ? (int) $invoice->amount_paid : $priceCents;

        // subscribed('resale_cert') is the access gate, so the local row has
        // to exist now (Cashier's webhook auto-sync is not registered here).
        $business->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscription->id],
            [
                'type' => config('resale_cert.subscription_type'),
                'stripe_status' => $subscription->status ?? 'active',
                'stripe_price' => $stripePriceId,
                'quantity' => 1,
            ]
        );

        $addOnPayment = Payment::firstOrCreate(
            ['stripe_subscription_id' => $subscription->id, 'business_id' => $business->id],
            [
                'purchasable_type' => $business->getMorphClass(),
                'purchasable_id' => $business->id,
                'price_id' => $priceId,
                'amount_cents' => $amountPaid,
                'currency' => 'usd',
                'status' => PaymentStatus::Succeeded,
                'provider' => 'stripe',
                'billing_type' => 'subscription',
                'stripe_invoice_id' => $invoiceId,
                'stripe_payment_intent_id' => is_string($invoicePaymentIntent) ? $invoicePaymentIntent : null,
                'livemode' => Payment::isLiveMode(),
                'paid_at' => now(),
                'meta' => ['sales_tax_payment_id' => $payment->id],
            ]
        );

        $this->sendReceipt($addOnPayment);

        return [
            'status' => 'created',
            'subscription_id' => $subscription->id,
            'payment_id' => $addOnPayment->id,
            'amount_cents' => $amountPaid,
        ];
    }

    /**
     * Keyless local dev: a stub subscription and payment, like the resale
     * checkout's own stub.
     *
     * @return array<string, mixed>
     */
    protected function startStub(Payment $payment, Business $business, int $priceId, int $priceCents): array
    {
        $stripeId = 'stub_'.uniqid();

        $business->subscriptions()->create([
            'type' => config('resale_cert.subscription_type'),
            'stripe_id' => $stripeId,
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => 1,
        ]);

        $addOnPayment = Payment::create([
            'purchasable_type' => $business->getMorphClass(),
            'purchasable_id' => $business->id,
            'business_id' => $business->id,
            'price_id' => $priceId,
            'amount_cents' => $priceCents,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'provider' => 'stub',
            'billing_type' => 'subscription',
            'stripe_subscription_id' => $stripeId,
            'livemode' => false,
            'paid_at' => now(),
            'meta' => ['sales_tax_payment_id' => $payment->id],
        ]);

        $this->sendReceipt($addOnPayment);

        return [
            'status' => 'created',
            'subscription_id' => $stripeId,
            'payment_id' => $addOnPayment->id,
            'amount_cents' => $priceCents,
        ];
    }

    /**
     * Its own receipt: the certificates are a separate charge on the card.
     */
    protected function sendReceipt(Payment $addOnPayment): void
    {
        DB::afterCommit(function () use ($addOnPayment) {
            $user = $addOnPayment->business->users()->first();

            if (! $user) {
                return;
            }

            SentEmail::recordOrSkip('payment_receipt', $addOnPayment, $user, function () use ($addOnPayment, $user) {
                Mail::to($user)->queue(new PaymentReceipt($addOnPayment));
            });
        });
    }

    /**
     * The customer asked for certificates and did not get them: tell the
     * team (log + Sentry), the same way other payment problems surface.
     *
     * @return array{status: string, error: string}
     */
    protected function failed(Payment $payment, string $error): array
    {
        Log::error('Sales tax order: resale certificate add-on not started', [
            'payment_id' => $payment->id,
            'business_id' => $payment->business_id,
            'error' => $error,
        ]);

        report(new RuntimeException(
            "Resale certificate add-on not started for sales tax payment {$payment->id} (business {$payment->business_id}): {$error}"
        ));

        return ['status' => 'failed', 'error' => $error];
    }

    protected function retrievePaymentIntent(string $paymentIntentId): StripeObject
    {
        return Cashier::stripe()->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * A subscription Stripe already started for this order: the earlier
     * call charged the card but its result was never saved.
     */
    protected function findStartedSubscription(string $customerId, int $paymentId): ?StripeObject
    {
        $subscriptions = Cashier::stripe()->subscriptions->all([
            'customer' => $customerId,
            'status' => 'all',
            'limit' => 100,
            'expand' => ['data.latest_invoice.payments'],
        ]);

        foreach ($subscriptions->data as $subscription) {
            if ((string) ($subscription->metadata->sales_tax_payment_id ?? '') === (string) $paymentId) {
                return $subscription;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $params
     */
    protected function createSubscription(array $params, string $idempotencyKey): StripeObject
    {
        return Cashier::stripe()->subscriptions->create($params, ['idempotency_key' => $idempotencyKey]);
    }
}

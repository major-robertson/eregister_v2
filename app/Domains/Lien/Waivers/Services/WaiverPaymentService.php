<?php

namespace App\Domains\Lien\Waivers\Services;

use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\SentEmail;
use App\Services\OpenAiConversionsApi;
use App\Services\RedditConversionsApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeObject;

/**
 * Marks the initial lien-waiver subscription payment succeeded and records
 * the local Cashier subscription row. Idempotent: callable from both the
 * payment_intent.succeeded webhook and the confirmation-page fallback.
 *
 * Mirrors ResaleCertPaymentService, except prices come in two intervals
 * (monthly/yearly variant_key), so the price is resolved per interval and
 * the subscription row derives its stripe_price from the payment's price
 * relation rather than a single canonical price.
 */
class WaiverPaymentService
{
    /**
     * @param  'monthly'|'yearly'  $interval
     */
    public function price(string $interval): Price
    {
        return Price::resolve('lien', 'lien_waiver', $interval, 'subscription');
    }

    public function markSucceeded(Payment $payment, StripeObject $paymentIntent): void
    {
        $sendEmails = false;
        $queueConversion = false;

        DB::transaction(function () use ($payment, $paymentIntent, &$sendEmails, &$queueConversion) {
            $payment = Payment::lockForUpdate()->find($payment->id);

            if ($payment->status === PaymentStatus::Succeeded) {
                return;
            }

            $flagForReview = $this->shouldFlagForReview($payment, $paymentIntent);

            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'stripe_charge_id' => $paymentIntent->latest_charge ?? null,
                'paid_at' => now(),
                'requires_manual_review' => $flagForReview,
                'error_message' => $flagForReview ? $this->buildReviewMessage($payment, $paymentIntent) : null,
            ]);

            $queueConversion = true;

            // subscribed('lien_waiver') is the access gate, so the local row
            // must exist the moment payment lands (Cashier's own webhook
            // auto-sync is not registered in this app). The payment's price
            // relation carries the chosen interval, so its Stripe price is
            // the correct one; never hardcode monthly or yearly here.
            $this->recordSubscription($payment->business, $payment->stripe_subscription_id, $payment->price);

            if ($flagForReview) {
                Log::info('Lien waiver subscription payment succeeded but flagged for review', [
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $sendEmails = true;
        });

        // Fire on the first transition to succeeded even when flagged for
        // review - the charge is real and the browser pixel counts it, so
        // CAPI must mirror it or coverage skews by ad blocker.
        if ($queueConversion) {
            $conversionPayment = $payment->fresh();
            app(RedditConversionsApi::class)->queuePurchase($conversionPayment);
            app(OpenAiConversionsApi::class)->queuePurchase($conversionPayment);
        }

        if (! $sendEmails) {
            return;
        }

        DB::afterCommit(function () use ($payment) {
            $payment = $payment->fresh();

            $user = $payment->business->users()->first();

            if (! $user) {
                return;
            }

            SentEmail::recordOrSkip('payment_receipt', $payment, $user, function () use ($payment, $user) {
                Mail::to($user)->queue(new PaymentReceipt($payment));
            });
        });
    }

    private function recordSubscription(Business $business, ?string $subscriptionId, ?Price $price): void
    {
        if (! $subscriptionId) {
            return;
        }

        $business->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscriptionId],
            [
                'type' => config('lien_waivers.subscription_type'),
                'stripe_status' => 'active',
                'stripe_price' => $price?->stripePriceId(),
                'quantity' => 1,
            ]
        );
    }

    /**
     * Record a cycle-2+ renewal invoice as a Payment. Idempotent by
     * stripe_invoice_id, so webhook replays are no-ops.
     */
    public function recordRenewalPayment(StripeObject $invoice, Business $business): void
    {
        $invoiceId = $invoice->id ?? null;

        if (! $invoiceId) {
            return;
        }

        $created = false;

        $payment = DB::transaction(function () use ($invoice, $invoiceId, $business, &$created) {
            $existing = Payment::where('stripe_invoice_id', $invoiceId)->lockForUpdate()->first();

            if ($existing) {
                return $existing;
            }

            $created = true;

            return Payment::create([
                'business_id' => $business->id,
                'purchasable_type' => $business->getMorphClass(),
                'purchasable_id' => $business->id,
                'price_id' => $this->renewalPrice($invoice)?->id,
                'amount_cents' => $invoice->amount_paid ?? $invoice->total ?? 0,
                'currency' => strtolower($invoice->currency ?? 'usd'),
                'status' => PaymentStatus::Succeeded,
                'provider' => 'stripe',
                'billing_type' => 'subscription',
                'stripe_subscription_id' => $invoice->subscription
                    ?? ($invoice->subscription_details->subscription ?? null),
                'stripe_invoice_id' => $invoiceId,
                'stripe_charge_id' => $invoice->charge ?? null,
                'livemode' => (bool) ($invoice->livemode ?? false),
                'paid_at' => now(),
            ]);
        });

        if (! $created) {
            return;
        }

        DB::afterCommit(function () use ($payment, $business) {
            $user = $business->users()->first();

            if (! $user) {
                return;
            }

            SentEmail::recordOrSkip('payment_receipt', $payment, $user, function () use ($payment, $user) {
                Mail::to($user)->queue(new PaymentReceipt($payment));
            });
        });
    }

    /**
     * Which interval's price row a renewal invoice belongs to. Invoices don't
     * carry our variant_key, so match on the charged amount ($99 monthly vs
     * $990 yearly); fall back to the first row so a promo-priced invoice
     * still records a Payment rather than dropping the receipt.
     */
    private function renewalPrice(StripeObject $invoice): ?Price
    {
        $prices = Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->get();

        $amount = $invoice->amount_paid ?? $invoice->total ?? null;

        return $prices->firstWhere('amount_cents', $amount) ?? $prices->first();
    }

    private function shouldFlagForReview(Payment $payment, StripeObject $paymentIntent): bool
    {
        $amountReceived = $paymentIntent->amount_received ?? null;

        if ($amountReceived !== null && $amountReceived !== $payment->amount_cents) {
            Log::error('Lien waiver payment amount mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount_cents,
                'received' => $amountReceived,
            ]);

            return true;
        }

        if ($paymentIntent->currency !== null && strtolower($paymentIntent->currency) !== 'usd') {
            Log::error('Lien waiver payment currency mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'currency' => $paymentIntent->currency,
            ]);

            return true;
        }

        return false;
    }

    /**
     * @return non-empty-string
     */
    private function buildReviewMessage(Payment $payment, StripeObject $paymentIntent): string
    {
        $messages = [];

        $amountReceived = $paymentIntent->amount_received ?? null;
        if ($amountReceived !== null && $amountReceived !== $payment->amount_cents) {
            $messages[] = 'Amount mismatch: expected '.$payment->amount_cents.', received '.$amountReceived;
        }

        if ($paymentIntent->currency !== null && strtolower($paymentIntent->currency) !== 'usd') {
            $messages[] = 'Currency mismatch: '.$paymentIntent->currency;
        }

        return implode('; ', $messages) ?: 'Flagged for review';
    }
}

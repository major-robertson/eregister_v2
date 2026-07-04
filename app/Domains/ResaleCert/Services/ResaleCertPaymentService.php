<?php

namespace App\Domains\ResaleCert\Services;

use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\SentEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeObject;

/**
 * Marks the initial resale-cert subscription payment succeeded and records
 * the local Cashier subscription row. Idempotent — callable from both the
 * payment_intent.succeeded webhook and the confirmation-page fallback.
 */
class ResaleCertPaymentService
{
    public function markSucceeded(Payment $payment, StripeObject $paymentIntent): void
    {
        $sendEmails = false;

        DB::transaction(function () use ($payment, $paymentIntent, &$sendEmails) {
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

            // subscribed('resale_cert') is the access gate, so the local row
            // must exist the moment payment lands (Cashier's own webhook
            // auto-sync is not registered in this app).
            $this->recordSubscription($payment->business, $payment->stripe_subscription_id);

            if ($flagForReview) {
                Log::info('Resale cert subscription payment succeeded but flagged for review', [
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $sendEmails = true;
        });

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

    private function recordSubscription(Business $business, ?string $subscriptionId): void
    {
        if (! $subscriptionId) {
            return;
        }

        $business->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscriptionId],
            [
                'type' => config('resale_cert.subscription_type'),
                'stripe_status' => 'active',
                'stripe_price' => $this->price()->stripePriceId(),
                'quantity' => 1,
            ]
        );
    }

    /**
     * Record a year-2+ renewal invoice as a Payment. Idempotent by
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
                'price_id' => Price::query()
                    ->where('product_family', config('resale_cert.price_family'))
                    ->where('product_key', config('resale_cert.price_key'))
                    ->where('billing_type', 'subscription')
                    ->value('id'),
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

    public function price(): Price
    {
        return Price::resolve(
            config('resale_cert.price_family'),
            config('resale_cert.price_key'),
            'default',
            'subscription',
        );
    }

    private function shouldFlagForReview(Payment $payment, StripeObject $paymentIntent): bool
    {
        $amountReceived = $paymentIntent->amount_received ?? null;

        if ($amountReceived !== null && $amountReceived !== $payment->amount_cents) {
            Log::error('Resale cert payment amount mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount_cents,
                'received' => $amountReceived,
            ]);

            return true;
        }

        if ($paymentIntent->currency !== null && strtolower($paymentIntent->currency) !== 'usd') {
            Log::error('Resale cert payment currency mismatch - flagging for review', [
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

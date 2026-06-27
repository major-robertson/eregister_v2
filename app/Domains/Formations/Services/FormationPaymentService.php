<?php

namespace App\Domains\Formations\Services;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
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
 * Marks an LLC formation payment as succeeded, records the membership
 * subscription locally, and submits + locks the underlying FormApplication.
 * Idempotent - safe to call from both the webhook (checkout.session.completed)
 * and the confirmation-page fallback.
 *
 * Mirrors the Sales Tax RegistrationPaymentService: an amount/currency
 * mismatch is flagged for manual review and skips the auto-submit.
 */
class FormationPaymentService
{
    /**
     * Mark the first formation payment succeeded, record the membership
     * subscription, and submit + lock the application. Idempotent — safe to
     * call from the payment_intent.succeeded webhook and the confirmation-page
     * fallback.
     *
     * @param  StripeObject  $paymentIntent  The succeeded first-invoice PaymentIntent.
     */
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

            // Record the membership subscription locally so subscribed('llc')
            // reflects reality (the only Stripe webhook endpoint is our custom
            // router; Cashier's auto-sync webhook is not registered). The
            // subscription id was stored on the Payment at checkout time.
            $this->recordSubscription($payment->business, $payment->stripe_subscription_id);

            if ($flagForReview) {
                Log::info('LLC formation payment succeeded but flagged for review', [
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $sendEmails = true;

            $application = $payment->purchasable;

            // Paying submits + locks the application, matching the stub-checkout
            // behavior. The dashboard and blocked-state logic key off paid_at.
            if ($application && ! $application->isLocked()) {
                $application->update([
                    'paid_at' => now(),
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'locked_at' => now(),
                    'stripe_subscription_id' => $payment->stripe_subscription_id,
                ]);
            }
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

    /**
     * Upsert the local Cashier subscription row for the membership. Status sync
     * on renewal/cancel is handled by the customer.subscription.* webhook events.
     */
    private function recordSubscription(Business $business, ?string $subscriptionId): void
    {
        if (! $subscriptionId) {
            return;
        }

        $business->subscriptions()->updateOrCreate(
            ['stripe_id' => $subscriptionId],
            [
                'type' => 'llc',
                'stripe_status' => 'active',
                'stripe_price' => Price::resolve('formation', 'llc', 'membership', 'subscription')->stripePriceId(),
                'quantity' => 1,
            ]
        );
    }

    private function shouldFlagForReview(Payment $payment, StripeObject $paymentIntent): bool
    {
        $amountReceived = $paymentIntent->amount_received ?? null;

        if ($amountReceived !== null && $amountReceived !== $payment->amount_cents) {
            Log::error('LLC formation payment amount mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount_cents,
                'received' => $amountReceived,
            ]);

            return true;
        }

        if ($paymentIntent->currency !== null && strtolower($paymentIntent->currency) !== 'usd') {
            Log::error('LLC formation payment currency mismatch - flagging for review', [
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

    /**
     * Record a year-2+ membership renewal payment (membership + any ongoing
     * state fee items) against the formation application. Idempotent by
     * stripe_invoice_id, so a webhook replay is a no-op. Queues a receipt.
     *
     * @param  StripeObject  $invoice  The paid Stripe renewal invoice.
     */
    public function recordRenewalPayment(StripeObject $invoice, Business $business, FormApplication $application): void
    {
        $invoiceId = $invoice->id ?? null;

        if (! $invoiceId) {
            return;
        }

        $created = false;

        $payment = DB::transaction(function () use ($invoice, $invoiceId, $business, $application, &$created) {
            $existing = Payment::where('stripe_invoice_id', $invoiceId)->lockForUpdate()->first();

            if ($existing) {
                return $existing;
            }

            $created = true;

            return Payment::create([
                'business_id' => $business->id,
                'purchasable_type' => $application->getMorphClass(),
                'purchasable_id' => $application->id,
                // Point at the membership price so renewal revenue is captured
                // by the formation revenue filter (product_family = 'formation').
                // Null-safe (no throw) in case the catalog isn't seeded.
                'price_id' => Price::query()
                    ->where('product_family', 'formation')
                    ->where('product_key', 'llc')
                    ->where('variant_key', 'membership')
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
}

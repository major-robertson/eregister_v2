<?php

namespace App\Domains\SalesTax\Services;

use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Services\OpenAiConversionsApi;
use App\Services\RedditConversionsApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeObject;

/**
 * Marks a sales-tax permit registration payment as succeeded and submits +
 * locks the underlying FormApplication. Idempotent - safe to call from both
 * the webhook and the confirmation-page fallback.
 *
 * Mirrors the Lien LienPaymentService pattern (amount/currency mismatch is
 * flagged for manual review and skips the auto-submit).
 */
class RegistrationPaymentService
{
    public function markSucceeded(Payment $payment, StripeObject $stripePaymentIntent): void
    {
        $sendEmails = false;
        $queueConversion = false;

        DB::transaction(function () use ($payment, $stripePaymentIntent, &$sendEmails, &$queueConversion) {
            $payment = Payment::lockForUpdate()->find($payment->id);

            if ($payment->status === PaymentStatus::Succeeded) {
                return;
            }

            $flagForReview = $this->shouldFlagForReview($payment, $stripePaymentIntent);

            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'stripe_charge_id' => $stripePaymentIntent->latest_charge,
                'paid_at' => now(),
                'requires_manual_review' => $flagForReview,
                'error_message' => $flagForReview ? $this->buildReviewMessage($payment, $stripePaymentIntent) : null,
            ]);

            $queueConversion = true;

            if ($flagForReview) {
                Log::info('Sales tax payment succeeded but flagged for review', [
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $sendEmails = true;

            $application = $payment->purchasable;

            // Paying for the registration submits + locks the application,
            // matching the existing stub-checkout behavior. The admin kanban
            // and blocked-state logic key off the application's paid_at.
            if ($application && ! $application->isLocked()) {
                $application->update([
                    'paid_at' => now(),
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'locked_at' => now(),
                    'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
                ]);
            }
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

    private function shouldFlagForReview(Payment $payment, StripeObject $stripePaymentIntent): bool
    {
        if ($stripePaymentIntent->amount_received !== $payment->amount_cents) {
            Log::error('Sales tax payment amount mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount_cents,
                'received' => $stripePaymentIntent->amount_received,
            ]);

            return true;
        }

        if (strtolower($stripePaymentIntent->currency) !== 'usd') {
            Log::error('Sales tax payment currency mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'currency' => $stripePaymentIntent->currency,
            ]);

            return true;
        }

        return false;
    }

    /**
     * @return non-empty-string
     */
    private function buildReviewMessage(Payment $payment, StripeObject $stripePaymentIntent): string
    {
        $messages = [];

        if ($stripePaymentIntent->amount_received !== $payment->amount_cents) {
            $messages[] = 'Amount mismatch: expected '.$payment->amount_cents.', received '.$stripePaymentIntent->amount_received;
        }

        if (strtolower($stripePaymentIntent->currency) !== 'usd') {
            $messages[] = 'Currency mismatch: '.$stripePaymentIntent->currency;
        }

        return implode('; ', $messages);
    }
}

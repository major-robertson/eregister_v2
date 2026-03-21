<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFulfillmentTask;
use App\Enums\PaymentStatus;
use App\Jobs\SendWorkingOnOrderEmail;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\SentEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeObject;

class LienPaymentService
{
    /**
     * Mark a payment as succeeded and transition the filing status.
     * This is idempotent - safe to call multiple times.
     *
     * Verifies the Stripe PaymentIntent amount/currency against the local Payment record.
     * Mismatches are flagged for manual review (fulfillment and emails are skipped).
     */
    public function markSucceeded(Payment $payment, StripeObject $stripePaymentIntent): void
    {
        $sendEmails = false;

        DB::transaction(function () use ($payment, $stripePaymentIntent, &$sendEmails) {
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

            if ($flagForReview) {
                Log::info('Payment succeeded but flagged for review', [
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $sendEmails = true;

            $filing = $payment->purchasable;

            if ($filing->status === FilingStatus::AwaitingPayment) {
                $filing->transitionTo(FilingStatus::Paid);
            }

            if ($filing->isFullService() && $filing->status === FilingStatus::Paid) {
                $filing->transitionTo(FilingStatus::InFulfillment);
                LienFulfillmentTask::firstOrCreate(
                    ['filing_id' => $filing->id],
                    ['business_id' => $filing->business_id, 'status' => 'queued']
                );
            }

            if ($filing->projectDeadline && $filing->projectDeadline->status->value === 'pending') {
                $filing->projectDeadline->update([
                    'status' => 'completed',
                    'completed_filing_id' => $filing->id,
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

            if ($payment->isRegistrationOrder()) {
                $scheduledAt = SendWorkingOnOrderEmail::nextAllowedSendTime();
                SentEmail::recordOrSkip('working_on_order', $payment, $user, function () use ($payment) {
                    SendWorkingOnOrderEmail::dispatch($payment);
                }, $scheduledAt);
            }
        });
    }

    private function shouldFlagForReview(Payment $payment, StripeObject $stripePaymentIntent): bool
    {
        if ($stripePaymentIntent->amount_received !== $payment->amount_cents) {
            Log::error('Payment amount mismatch - flagging for review', [
                'payment_id' => $payment->id,
                'expected' => $payment->amount_cents,
                'received' => $stripePaymentIntent->amount_received,
            ]);

            return true;
        }

        if (strtolower($stripePaymentIntent->currency) !== 'usd') {
            Log::error('Payment currency mismatch - flagging for review', [
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

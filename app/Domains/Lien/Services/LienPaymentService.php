<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFulfillmentTask;
use App\Enums\PaymentStatus;
use App\Jobs\SendWorkingOnOrderEmail;
use App\Mail\PaymentReceipt;
use App\Mail\PropertyDescriptionSteps;
use App\Models\Payment;
use App\Models\SentEmail;
use App\Services\OpenAiConversionsApi;
use App\Services\RedditConversionsApi;
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
        $queueConversion = false;
        $propertyStepsFiling = null;

        DB::transaction(function () use ($payment, $stripePaymentIntent, &$sendEmails, &$queueConversion, &$propertyStepsFiling) {
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

            // "I need help finding it" at order time (EREG-136): the filing
            // waits for the customer's legal description or parcel number,
            // which they enter on the filing page. Awaiting Client starts the
            // usual reminders; the steps email goes out after commit.
            // A full-service order still gets its fulfillment task; it moves to
            // In Fulfillment once staff have reviewed what the customer entered.
            if ($filing->status === FilingStatus::Paid && $filing->needsPropertyDescription()) {
                $filing->transitionTo(FilingStatus::AwaitingClient, ['note' => LienFiling::PROPERTY_DESCRIPTION_NOTE]);
                $propertyStepsFiling = $filing;

                if ($filing->isFullService()) {
                    LienFulfillmentTask::firstOrCreate(
                        ['filing_id' => $filing->id],
                        ['business_id' => $filing->business_id, 'status' => 'queued']
                    );
                }
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

            if ($payment->isRegistrationOrder()) {
                $scheduledAt = SendWorkingOnOrderEmail::nextAllowedSendTime();
                SentEmail::recordOrSkip('working_on_order', $payment, $user, function () use ($payment) {
                    SendWorkingOnOrderEmail::dispatch($payment);
                }, $scheduledAt);
            }
        });

        if ($propertyStepsFiling !== null) {
            DB::afterCommit(fn () => $this->sendPropertyDescriptionSteps($propertyStepsFiling));
        }
    }

    /**
     * Email the customer who chose "I need help finding it" how to find the
     * property's legal description or parcel number, once per filing. It
     * goes to whoever placed the order, like the filing reminders.
     */
    private function sendPropertyDescriptionSteps(LienFiling $filing): void
    {
        $filing = $filing->fresh(['createdBy', 'documentType', 'project']);
        $user = $filing?->createdBy ?? $filing?->business?->users()->first();

        if (! $filing || ! $user) {
            return;
        }

        SentEmail::recordOrSkip('property_description_steps', $filing, $user, function () use ($filing, $user) {
            Mail::to($user)->queue(new PropertyDescriptionSteps($filing));
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

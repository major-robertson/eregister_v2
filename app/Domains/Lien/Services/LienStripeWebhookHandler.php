<?php

namespace App\Domains\Lien\Services;

use App\Contracts\StripeWebhookHandlerInterface;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFulfillmentTask;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

class LienStripeWebhookHandler implements StripeWebhookHandlerInterface
{
    /**
     * Handle a Stripe webhook event for the lien domain.
     */
    public function handle(Event $event): Response
    {
        return match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event),
            // Legacy: checkout.session.completed for backward compatibility
            'checkout.session.completed' => $this->handleCheckoutCompleted($event),
            // Future: 'invoice.paid' => $this->handleInvoicePaid($event),
            default => response('Event type not handled by lien domain', 200),
        };
    }

    private function handlePaymentSucceeded(Event $event): Response
    {
        $pi = $event->data->object;
        $paymentId = $pi->metadata->app_payment_id ?? null;

        if (! $paymentId) {
            Log::info('Lien webhook: No app_payment_id in metadata', ['pi' => $pi->id]);

            return response('No app_payment_id', 200);
        }

        // Transaction + row lock for idempotency
        DB::transaction(function () use ($pi, $paymentId) {
            $payment = Payment::lockForUpdate()->find($paymentId);

            if (! $payment) {
                Log::warning('Lien webhook: Payment not found', ['payment_id' => $paymentId]);

                return;
            }

            // Already succeeded - idempotent return
            if ($payment->status === PaymentStatus::Succeeded) {
                return;
            }

            $flagForReview = false;
            $errorMessages = [];

            // Verify amount matches - flag for review instead of auto-fail
            if ($pi->amount_received !== $payment->amount_cents) {
                Log::error('Lien webhook: Amount mismatch - flagging for review', [
                    'payment_id' => $paymentId,
                    'expected' => $payment->amount_cents,
                    'received' => $pi->amount_received,
                ]);
                $flagForReview = true;
                $errorMessages[] = 'Amount mismatch: expected '.$payment->amount_cents.', received '.$pi->amount_received;
            }

            // Verify currency - flag for review
            if (strtolower($pi->currency) !== 'usd') {
                Log::error('Lien webhook: Currency mismatch - flagging for review', ['currency' => $pi->currency]);
                $flagForReview = true;
                $errorMessages[] = 'Currency mismatch: '.$pi->currency;
            }

            // Update payment as succeeded
            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'stripe_charge_id' => $pi->latest_charge,
                'paid_at' => now(),
                'requires_manual_review' => $flagForReview,
                'error_message' => $flagForReview ? implode('; ', $errorMessages) : null,
            ]);

            // Only proceed with fulfillment if NOT flagged for review
            if (! $flagForReview) {
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

                // Mark deadline as completed
                if ($filing->projectDeadline && $filing->projectDeadline->status->value === 'pending') {
                    $filing->projectDeadline->update([
                        'status' => 'completed',
                        'completed_filing_id' => $filing->id,
                    ]);
                }
            }

            Log::info('Lien webhook: Payment succeeded', [
                'payment_id' => $paymentId,
                'requires_review' => $flagForReview,
            ]);
        });

        return response('OK', 200);
    }

    private function handlePaymentFailed(Event $event): Response
    {
        $pi = $event->data->object;
        $paymentId = $pi->metadata->app_payment_id ?? null;

        if (! $paymentId) {
            return response('No app_payment_id', 200);
        }

        DB::transaction(function () use ($pi, $paymentId) {
            $payment = Payment::lockForUpdate()->find($paymentId);

            if (! $payment || $payment->status->isTerminal()) {
                return;
            }

            // payment_failed is retryable - set to RequiresPaymentMethod, not Failed
            // The PaymentIntent is still usable; customer can try again
            $payment->update([
                'status' => PaymentStatus::RequiresPaymentMethod,
                'error_message' => $pi->last_payment_error?->message ?? 'Payment failed',
            ]);

            Log::info('Lien webhook: Payment failed (retryable)', ['payment_id' => $paymentId]);
        });

        return response('OK', 200);
    }

    private function handlePaymentCanceled(Event $event): Response
    {
        $pi = $event->data->object;
        $paymentId = $pi->metadata->app_payment_id ?? null;

        if (! $paymentId) {
            return response('No app_payment_id', 200);
        }

        DB::transaction(function () use ($paymentId) {
            $payment = Payment::lockForUpdate()->find($paymentId);

            if (! $payment || $payment->status->isTerminal()) {
                return;
            }

            $payment->update([
                'status' => PaymentStatus::Canceled,
            ]);

            Log::info('Lien webhook: Payment canceled', ['payment_id' => $paymentId]);
        });

        return response('OK', 200);
    }

    /**
     * Handle checkout.session.completed for backward compatibility.
     * This handles payments made via the old Stripe Checkout flow.
     *
     * @deprecated Will be removed after migration to PaymentIntent flow is complete
     */
    private function handleCheckoutCompleted(Event $event): Response
    {
        $session = $event->data->object;
        $filingId = $session->metadata->filing_id ?? null;

        if (! $filingId) {
            Log::warning('Lien webhook: No filing_id in checkout metadata', ['session' => $session->id]);

            return response('No filing_id in metadata', 200);
        }

        // Use the new payment-based approach if we have app_payment_id
        if (isset($session->metadata->app_payment_id)) {
            Log::info('Lien webhook: Checkout session with app_payment_id, using new flow');

            return response('Handled via new flow', 200);
        }

        // Legacy handling for old checkout sessions
        DB::transaction(function () use ($session, $filingId) {
            $filing = LienFiling::withoutGlobalScope('business')
                ->lockForUpdate()
                ->find($filingId);

            if (! $filing) {
                Log::warning('Lien webhook: Filing not found', ['filing_id' => $filingId]);

                return;
            }

            // Create payment record
            Payment::firstOrCreate(
                ['stripe_checkout_session_id' => $session->id],
                [
                    'business_id' => $filing->business_id,
                    'purchasable_type' => $filing->getMorphClass(),
                    'purchasable_id' => $filing->id,
                    'stripe_payment_intent_id' => $session->payment_intent ?? null,
                    'amount_cents' => $session->amount_total ?? 0,
                    'currency' => $session->currency ?? 'usd',
                    'status' => PaymentStatus::Succeeded,
                    'provider' => 'stripe',
                    'livemode' => Payment::isLiveMode(),
                    'paid_at' => now(),
                ]
            );

            // Update filing
            $filing->update([
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
            ]);

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

            Log::info('Lien webhook: Legacy checkout payment processed', [
                'filing_id' => $filing->id,
                'status' => $filing->status->value,
            ]);
        });

        return response('OK', 200);
    }
}

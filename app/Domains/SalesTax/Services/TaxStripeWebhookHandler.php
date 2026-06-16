<?php

namespace App\Domains\SalesTax\Services;

use App\Contracts\StripeWebhookHandlerInterface;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

/**
 * Handles Stripe webhook events for the tax domain (app_domain = 'tax').
 *
 * Kept deliberately simple: it loads the Payment by metadata.app_payment_id
 * and hands off to RegistrationPaymentService. The PaymentIntent already
 * carries payment_kind = 'sales_tax_registration'; when a second tax
 * purchase type (e.g. filing/remittance) is added, branch on payment_kind
 * here - no generic registry yet.
 */
class TaxStripeWebhookHandler implements StripeWebhookHandlerInterface
{
    public function __construct(private RegistrationPaymentService $registrationPaymentService) {}

    public function handle(Event $event): Response
    {
        return match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event),
            default => response('Event type not handled by tax domain', 200),
        };
    }

    private function handlePaymentSucceeded(Event $event): Response
    {
        $pi = $event->data->object;
        $payment = $this->resolvePayment($pi);

        if (! $payment) {
            return response('Payment not found', 200);
        }

        $this->registrationPaymentService->markSucceeded($payment, $pi);

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

            // Retryable - the PaymentIntent is still usable.
            $payment->update([
                'status' => PaymentStatus::RequiresPaymentMethod,
                'error_message' => $pi->last_payment_error?->message ?? 'Payment failed',
            ]);

            Log::info('Tax webhook: Payment failed (retryable)', ['payment_id' => $paymentId]);
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

            $payment->update(['status' => PaymentStatus::Canceled]);

            Log::info('Tax webhook: Payment canceled', ['payment_id' => $paymentId]);
        });

        return response('OK', 200);
    }

    private function resolvePayment(object $pi): ?Payment
    {
        $paymentId = $pi->metadata->app_payment_id ?? null;

        if (! $paymentId) {
            Log::info('Tax webhook: No app_payment_id in metadata', ['pi' => $pi->id ?? null]);

            return null;
        }

        $payment = Payment::find($paymentId);

        if (! $payment) {
            Log::warning('Tax webhook: Payment not found', ['payment_id' => $paymentId]);
        }

        return $payment;
    }
}

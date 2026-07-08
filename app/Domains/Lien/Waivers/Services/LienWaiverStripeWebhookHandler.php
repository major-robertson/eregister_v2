<?php

namespace App\Domains\Lien\Waivers\Services;

use App\Contracts\StripeWebhookHandlerInterface;
use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Stripe\Event;

/**
 * Stripe webhook events for the lien waiver domain
 * (app_domain = 'lien_waiver', payment_kind = 'lien_waiver_subscription').
 *
 * - payment_intent.succeeded    → record the initial subscription purchase.
 * - payment_intent.payment_failed / .canceled → mark the payment retryable/canceled.
 * - invoice.paid / invoice.payment_succeeded → record cycle-2+ renewal Payments.
 * - invoice.payment_failed      → log (Stripe dunning handles retries).
 * - customer.subscription.*     → sync the local subscription row, scoped to
 *                                 type 'lien_waiver' (subscribed('lien_waiver')
 *                                 is the access gate).
 */
class LienWaiverStripeWebhookHandler implements StripeWebhookHandlerInterface
{
    public function __construct(private WaiverPaymentService $paymentService) {}

    public function handle(Event $event): Response
    {
        return match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event),
            // Stripe emits both names depending on API version; renewal
            // recording is idempotent by invoice id so handling both is safe.
            'invoice.paid',
            'invoice.payment_succeeded' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChanged($event),
            default => response('Event type not handled by lien_waiver domain', 200),
        };
    }

    private function handlePaymentSucceeded(Event $event): Response
    {
        $pi = $event->data->object;

        if (($pi->metadata->payment_kind ?? null) !== 'lien_waiver_subscription') {
            return response('Payment kind not handled by lien_waiver domain', 200);
        }

        $paymentId = $pi->metadata->app_payment_id ?? null;
        $payment = $paymentId ? Payment::find($paymentId) : null;

        if (! $payment) {
            Log::info('Lien waiver webhook: Payment not found', ['payment_id' => $paymentId, 'pi' => $pi->id ?? null]);

            return response('Payment not found', 200);
        }

        $this->paymentService->markSucceeded($payment, $pi);

        return response('OK', 200);
    }

    private function handlePaymentFailed(Event $event): Response
    {
        $pi = $event->data->object;

        DB::transaction(function () use ($pi) {
            $payment = $this->lockPayment($pi);

            if (! $payment || $payment->status->isTerminal()) {
                return;
            }

            $payment->update([
                'status' => PaymentStatus::RequiresPaymentMethod,
                'error_message' => $pi->last_payment_error?->message ?? 'Payment failed',
            ]);
        });

        return response('OK', 200);
    }

    private function handlePaymentCanceled(Event $event): Response
    {
        $pi = $event->data->object;

        DB::transaction(function () use ($pi) {
            $payment = $this->lockPayment($pi);

            if (! $payment || $payment->status->isTerminal()) {
                return;
            }

            $payment->update(['status' => PaymentStatus::Canceled]);
        });

        return response('OK', 200);
    }

    private function lockPayment(object $pi): ?Payment
    {
        $paymentId = $pi->metadata->app_payment_id ?? null;

        return $paymentId ? Payment::lockForUpdate()->find($paymentId) : null;
    }

    private function handleInvoicePaid(Event $event): Response
    {
        $invoice = $event->data->object;

        // The first invoice is recorded by payment_intent.succeeded; only
        // record genuine renewals here.
        if (($invoice->billing_reason ?? null) !== 'subscription_cycle') {
            return response('Not a renewal cycle', 200);
        }

        $localSub = $this->resolveLocalSubscription($invoice);

        if (! $localSub) {
            return response('Unresolved lien_waiver subscription', 200);
        }

        $business = Business::find($localSub->business_id);

        if (! $business) {
            return response('Business not found', 200);
        }

        $this->paymentService->recordRenewalPayment($invoice, $business);

        return response('OK', 200);
    }

    private function handleInvoicePaymentFailed(Event $event): Response
    {
        $invoice = $event->data->object;

        Log::info('Lien waiver webhook: Invoice payment failed', [
            'invoice' => $invoice->id ?? null,
            'subscription' => $invoice->subscription ?? null,
        ]);

        return response('OK', 200);
    }

    private function handleSubscriptionChanged(Event $event): Response
    {
        $stripeSubscription = $event->data->object;
        $stripeId = $stripeSubscription->id ?? null;

        if (! $stripeId) {
            return response('No subscription id', 200);
        }

        DB::transaction(function () use ($stripeSubscription, $stripeId) {
            $subscription = Subscription::where('stripe_id', $stripeId)
                ->where('type', config('lien_waivers.subscription_type'))
                ->lockForUpdate()
                ->first();

            if (! $subscription) {
                return;
            }

            $endsAt = ($stripeSubscription->cancel_at_period_end ?? false)
                ? ($stripeSubscription->current_period_end ?? null)
                : ($stripeSubscription->canceled_at ?? null);

            $subscription->update([
                'stripe_status' => $stripeSubscription->status ?? $subscription->stripe_status,
                'ends_at' => $endsAt ? Carbon::createFromTimestamp($endsAt) : $subscription->ends_at,
            ]);
        });

        return response('OK', 200);
    }

    private function resolveLocalSubscription(object $invoice): ?Subscription
    {
        $subscriptionId = $invoice->subscription
            ?? ($invoice->subscription_details->subscription ?? null)
            ?? ($invoice->parent->subscription_details->subscription ?? null);

        if (! $subscriptionId) {
            return null;
        }

        $localSub = Subscription::where('stripe_id', $subscriptionId)->first();

        return $localSub && $localSub->type === config('lien_waivers.subscription_type')
            ? $localSub
            : null;
    }
}

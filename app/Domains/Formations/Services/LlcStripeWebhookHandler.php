<?php

namespace App\Domains\Formations\Services;

use App\Contracts\StripeWebhookHandlerInterface;
use App\Domains\Business\Models\Business;
use App\Domains\Formations\Models\FormationRenewalFeeItem;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;
use Stripe\Event;
use Stripe\StripeClient;

/**
 * Handles Stripe webhook events for the LLC formation domain
 * (app_domain = 'llc').
 *
 * - payment_intent.succeeded    → record the initial purchase + subscription
 *                                 (the embedded first-invoice PaymentIntent).
 * - payment_intent.payment_failed / .canceled → mark the payment retryable/canceled.
 * - invoice.upcoming            → PRIMARY: add the year-2+ ongoing state fee to
 *                                 the forthcoming renewal invoice (sub-scoped).
 * - invoice.created             → FALLBACK: attach the fee to the draft renewal
 *                                 invoice if invoice.upcoming didn't.
 * - invoice.paid                → record the renewal Payment + mark fees paid.
 * - invoice.payment_failed      → log.
 * - customer.subscription.*     → keep the local subscription row in sync.
 */
class LlcStripeWebhookHandler implements StripeWebhookHandlerInterface
{
    public function __construct(
        private FormationPaymentService $paymentService,
        private FormationFeeSchedule $schedule,
    ) {}

    public function handle(Event $event): Response
    {
        return match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'payment_intent.canceled' => $this->handlePaymentCanceled($event),
            'invoice.upcoming' => $this->applyOngoingFees($event->data->object, attachToInvoice: false),
            'invoice.created' => $this->applyOngoingFees($event->data->object, attachToInvoice: true),
            'invoice.paid' => $this->handleInvoicePaid($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChanged($event),
            default => response('Event type not handled by llc domain', 200),
        };
    }

    private function handlePaymentSucceeded(Event $event): Response
    {
        $pi = $event->data->object;
        $payment = $this->resolvePayment($pi);

        if (! $payment) {
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

    private function resolvePayment(object $pi): ?Payment
    {
        $paymentId = $pi->metadata->app_payment_id ?? null;

        if (! $paymentId) {
            Log::info('LLC webhook: No app_payment_id in PaymentIntent metadata', ['pi' => $pi->id ?? null]);

            return null;
        }

        $payment = Payment::find($paymentId);

        if (! $payment) {
            Log::warning('LLC webhook: Payment not found', ['payment_id' => $paymentId]);
        }

        return $payment;
    }

    private function lockPayment(object $pi): ?Payment
    {
        $paymentId = $pi->metadata->app_payment_id ?? null;

        return $paymentId ? Payment::lockForUpdate()->find($paymentId) : null;
    }

    /**
     * Add the recurring state fee(s) due at this renewal to the membership
     * invoice. Only acts on true subscription renewals (`subscription_cycle`),
     * never the first invoice (the year-1 formation fee is a checkout line
     * item). Idempotent via the renewal-fee ledger + a Stripe idempotency key.
     */
    private function applyOngoingFees(object $invoice, bool $attachToInvoice): Response
    {
        if (($invoice->billing_reason ?? null) !== 'subscription_cycle') {
            return response('Not a renewal cycle', 200);
        }

        $ctx = $this->resolveContext($invoice);
        if (! $ctx) {
            return response('Unresolved LLC renewal context', 200);
        }

        $due = $this->schedule->dueCharges($ctx['state'], $ctx['cycle']);
        if (empty($due)) {
            return response('No ongoing fees due', 200);
        }

        foreach ($due as $component) {
            $this->ensureFeeItem($invoice, $ctx, $component, $attachToInvoice);
        }

        return response('OK', 200);
    }

    /**
     * Ensure a single ongoing-fee component is added exactly once for this
     * (subscription, cycle, component): the ledger bridges the gap between the
     * invoice.upcoming and invoice.created events, the Stripe idempotency key
     * guards same-window retries.
     *
     * @param  array<string, mixed>  $ctx
     * @param  array{component_key: string, label: string, amount_cents: int}  $component
     */
    private function ensureFeeItem(object $invoice, array $ctx, array $component, bool $attachToInvoice): void
    {
        $subId = $ctx['subscriptionId'];
        $cycle = $ctx['cycle'];
        $componentKey = $component['component_key'];

        $ledger = FormationRenewalFeeItem::firstOrCreate(
            [
                'stripe_subscription_id' => $subId,
                'cycle_number' => $cycle,
                'component_key' => $componentKey,
            ],
            [
                'business_id' => $ctx['business']->id,
                'form_application_id' => $ctx['application']?->id,
                'state' => $ctx['state'],
                'amount_cents' => $component['amount_cents'],
                'currency' => 'usd',
                'status' => 'pending',
            ]
        );

        if ($ledger->stripe_invoice_item_id) {
            return; // already added (by the other event or a retry)
        }

        if (blank(config('cashier.secret'))) {
            return; // keyless local/test: ledger row recorded, no Stripe call
        }

        // The fallback path can only edit a still-draft invoice.
        if ($attachToInvoice && ($invoice->status ?? null) !== 'draft') {
            return;
        }

        $params = [
            'customer' => $ctx['customerId'],
            'amount' => $component['amount_cents'],
            'currency' => 'usd',
            'description' => $component['label'],
            'metadata' => [
                'app_fee_kind' => 'llc_ongoing',
                'app_domain' => 'llc',
                'state' => $ctx['state'],
                'component' => $componentKey,
                'cycle' => $cycle,
            ],
        ];

        // Primary (invoice.upcoming): scope to the subscription so the item
        // attaches to that subscription's forthcoming invoice. Fallback
        // (invoice.created): attach directly to the draft invoice.
        if ($attachToInvoice) {
            $params['invoice'] = $invoice->id;
        } else {
            $params['subscription'] = $subId;
        }

        $item = (new StripeClient(config('cashier.secret')))->invoiceItems->create(
            $params,
            ['idempotency_key' => "llc_state_fee:{$subId}:{$cycle}:{$componentKey}"]
        );

        $ledger->update([
            'stripe_invoice_item_id' => $item->id,
            'status' => 'added',
        ]);
    }

    private function handleInvoicePaid(Event $event): Response
    {
        $invoice = $event->data->object;

        // The first invoice (subscription_create) is already finalized by
        // checkout.session.completed; only record genuine renewals here.
        if (($invoice->billing_reason ?? null) !== 'subscription_cycle') {
            return response('Not a renewal cycle', 200);
        }

        $ctx = $this->resolveContext($invoice);
        if (! $ctx || ! $ctx['application']) {
            return response('Unresolved LLC renewal context', 200);
        }

        $this->paymentService->recordRenewalPayment($invoice, $ctx['business'], $ctx['application']);

        FormationRenewalFeeItem::where('stripe_subscription_id', $ctx['subscriptionId'])
            ->where('cycle_number', $ctx['cycle'])
            ->update([
                'status' => 'paid',
                'stripe_invoice_id' => $invoice->id,
                'charged_at' => now(),
            ]);

        return response('OK', 200);
    }

    private function handleInvoicePaymentFailed(Event $event): Response
    {
        $invoice = $event->data->object;

        Log::info('LLC webhook: Invoice payment failed', [
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
            $subscription = Subscription::where('stripe_id', $stripeId)->lockForUpdate()->first();

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

    /**
     * Resolve the LLC renewal context from an invoice: the local subscription,
     * Business, formation application, state, Stripe customer id, and the
     * renewal cycle number. Returns null when the invoice isn't a known LLC
     * subscription or the state can't be determined.
     *
     * @return array<string, mixed>|null
     */
    private function resolveContext(object $invoice): ?array
    {
        $subscriptionId = $invoice->subscription
            ?? ($invoice->subscription_details->subscription ?? null)
            ?? ($invoice->parent->subscription_details->subscription ?? null);

        if (! $subscriptionId) {
            return null;
        }

        $localSub = Subscription::where('stripe_id', $subscriptionId)->first();
        if (! $localSub || $localSub->type !== 'llc') {
            return null;
        }

        $business = Business::find($localSub->business_id);
        if (! $business) {
            return null;
        }

        $meta = $invoice->subscription_details->metadata ?? null;
        $state = is_object($meta) ? ($meta->state ?? null) : null;
        $applicationId = is_object($meta) ? ($meta->llc_application_id ?? null) : null;

        $application = $applicationId ? FormApplication::find($applicationId) : null;

        // 1:1:1 fallback — a Business has exactly one LLC formation.
        if (! $application) {
            $application = FormApplication::where('business_id', $business->id)
                ->where('form_type', 'llc')
                ->latest()
                ->first();
        }

        $state = $state ?: ($application?->selected_states[0] ?? null);
        if (! $state) {
            return null;
        }

        $periodStart = $invoice->period_start ?? null;
        $invoicePeriodStart = $periodStart ? Carbon::createFromTimestamp($periodStart) : now();

        return [
            'subscriptionId' => $subscriptionId,
            'subscription' => $localSub,
            'business' => $business,
            'customerId' => $invoice->customer ?? $business->stripeId(),
            'application' => $application,
            'state' => $state,
            'cycle' => $this->schedule->cycleNumberFor($localSub->created_at, $invoicePeriodStart),
        ];
    }
}

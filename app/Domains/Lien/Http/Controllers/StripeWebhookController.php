<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFulfillmentTask;
use App\Domains\Lien\Models\LienPayment;
use App\Domains\Lien\Models\LienStripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController
{
    public function handle(Request $request): Response
    {
        $payload = $request->all();
        $eventId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? null;

        if (! $eventId) {
            return response('Missing event ID', 400);
        }

        // Idempotency check - exit early if already processed
        if (LienStripeWebhookEvent::isProcessed($eventId)) {
            return response('Already processed', 200);
        }

        // Record the event
        LienStripeWebhookEvent::record($eventId, $eventType ?? 'unknown', $payload);

        // Handle specific event types
        return match ($eventType) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($payload),
            default => response('Event type not handled', 200),
        };
    }

    private function handleCheckoutCompleted(array $payload): Response
    {
        $session = $payload['data']['object'] ?? [];
        $filingId = $session['metadata']['filing_id'] ?? null;

        if (! $filingId) {
            Log::warning('Lien webhook: No filing_id in metadata', ['session' => $session['id'] ?? null]);

            return response('No filing_id in metadata', 200);
        }

        $filing = LienFiling::withoutGlobalScope('business')->find($filingId);

        if (! $filing) {
            Log::warning('Lien webhook: Filing not found', ['filing_id' => $filingId]);

            return response('Filing not found', 200);
        }

        // Create payment record (unique on checkout_session_id prevents duplicates)
        LienPayment::firstOrCreate(
            ['stripe_checkout_session_id' => $session['id']],
            [
                'business_id' => $filing->business_id,
                'filing_id' => $filing->id,
                'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
                'amount_cents' => $session['amount_total'] ?? 0,
                'currency' => $session['currency'] ?? 'usd',
                'status' => 'paid',
                'paid_at' => now(),
            ]
        );

        // Update filing with payment intent ID
        $filing->update([
            'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
        ]);

        // Transition filing status
        if ($filing->status !== FilingStatus::Paid) {
            $filing->transitionTo(FilingStatus::Paid);
        }

        // If full-service, immediately transition to in_fulfillment and create task
        if ($filing->isFullService() && $filing->status === FilingStatus::Paid) {
            $filing->transitionTo(FilingStatus::InFulfillment);

            LienFulfillmentTask::firstOrCreate(
                ['filing_id' => $filing->id],
                [
                    'business_id' => $filing->business_id,
                    'status' => 'queued',
                ]
            );
        }

        // Mark deadline as completed
        if ($filing->projectDeadline && $filing->projectDeadline->status->value === 'pending') {
            $filing->projectDeadline->update([
                'status' => 'completed',
                'completed_filing_id' => $filing->id,
            ]);
        }

        Log::info('Lien webhook: Payment processed', [
            'filing_id' => $filing->id,
            'status' => $filing->status->value,
        ]);

        return response('OK', 200);
    }
}

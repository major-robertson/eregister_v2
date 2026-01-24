<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Services\LienStripeWebhookHandler;
use App\Models\StripeWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class StripeWebhookController
{
    public function handle(Request $request): Response
    {
        $rawPayload = $request->getContent();

        // 1. Verify signature using STRIPE_WEBHOOK_SECRET
        try {
            $event = Webhook::constructEvent(
                $rawPayload,
                $request->header('Stripe-Signature'),
                config('cashier.webhook.secret')
            );
        } catch (\Exception $e) {
            Log::warning('Stripe webhook: Signature verification failed', ['error' => $e->getMessage()]);

            return response('Invalid signature', 400);
        }

        // 2. Two-phase idempotency: insert pending with raw payload
        if (! StripeWebhookEvent::insertPending($event->id, $event->type, $rawPayload)) {
            // Already exists - check if fully processed
            if (StripeWebhookEvent::isProcessed($event->id)) {
                return response('Already processed', 200);
            }
            // Exists but not processed - likely a retry after crash, let it proceed
        }

        // 3. Route to domain handler based on app_domain metadata
        try {
            $result = $this->routeToDomainHandler($event);

            // 4. Mark as processed only on success
            StripeWebhookEvent::markProcessed($event->id);

            return $result;
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Handler failed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $e->getMessage(),
            ]);
            // Don't mark as processed - Stripe will retry

            return response('Handler failed', 500);
        }
    }

    /**
     * Route the event to the appropriate domain handler based on app_domain metadata.
     */
    private function routeToDomainHandler(\Stripe\Event $event): Response
    {
        $object = $event->data->object;
        $metadata = $object->metadata ?? new \stdClass;
        $domain = $metadata->app_domain ?? null;

        if (! $domain) {
            Log::info('Stripe webhook: No app_domain in metadata', [
                'event_id' => $event->id,
                'event_type' => $event->type,
            ]);

            return response('No app_domain', 200);
        }

        $handler = match ($domain) {
            'lien' => app(LienStripeWebhookHandler::class),
            // Future domains:
            // 'llc' => app(LlcStripeWebhookHandler::class),
            // 'tax' => app(TaxStripeWebhookHandler::class),
            // 'saas' => app(SaasStripeWebhookHandler::class),
            default => null,
        };

        if (! $handler) {
            Log::warning('Stripe webhook: Unknown domain', [
                'domain' => $domain,
                'event_id' => $event->id,
            ]);

            return response('Unknown domain', 200);
        }

        // Pass full event - handler decides what event types it supports
        return $handler->handle($event);
    }
}

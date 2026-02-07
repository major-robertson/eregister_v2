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
        $logPath = base_path('.cursor/debug.log');
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
            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'signature failed, returning 400', 'data' => ['error' => $e->getMessage()], 'hypothesisId' => 'H3'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return response('Invalid signature', 400);
        }

        // #region agent log
        file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'signature ok, inserting pending', 'data' => ['event_id' => $event->id ?? null, 'event_type' => $event->type ?? null], 'hypothesisId' => 'H2'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion

        // 2. Two-phase idempotency: insert pending with raw payload
        try {
            if (! StripeWebhookEvent::insertPending($event->id, $event->type, $rawPayload)) {
                // Already exists - check if fully processed
                if (StripeWebhookEvent::isProcessed($event->id)) {
                    // #region agent log
                    file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'already processed, returning 200', 'data' => ['event_id' => $event->id], 'hypothesisId' => 'H2'])."\n", FILE_APPEND | LOCK_EX);
                    // #endregion

                    return response('Already processed', 200);
                }
                // Exists but not processed - likely a retry after crash, let it proceed
            }
        } catch (\Throwable $e) {
            Log::error('Stripe webhook: insertPending failed', ['event_id' => $event->id ?? null, 'error' => $e->getMessage()]);
            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'insertPending threw', 'data' => ['event_id' => $event->id ?? null, 'error' => $e->getMessage()], 'hypothesisId' => 'H2'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return response('Idempotency error', 500);
        }

        // 3. Route to domain handler based on app_domain metadata
        try {
            $result = $this->routeToDomainHandler($event);

            // 4. Mark as processed only on success
            StripeWebhookEvent::markProcessed($event->id);

            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'handler success, returning', 'data' => ['event_id' => $event->id, 'status' => $result->getStatusCode()], 'hypothesisId' => 'H3'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return $result;
        } catch (\Exception $e) {
            Log::error('Stripe webhook: Handler failed', [
                'event_id' => $event->id,
                'event_type' => $event->type,
                'error' => $e->getMessage(),
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:handle', 'message' => 'handler threw, returning 500', 'data' => ['event_id' => $event->id, 'error' => $e->getMessage()], 'hypothesisId' => 'H1'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion
            // Don't mark as processed - Stripe will retry

            return response('Handler failed', 500);
        }
    }

    /**
     * Route the event to the appropriate domain handler based on app_domain metadata.
     * Safely extracts object/metadata so missing or unexpected event shape never throws (avoids 500 to Stripe).
     */
    private function routeToDomainHandler(\Stripe\Event $event): Response
    {
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:routeToDomainHandler', 'message' => 'webhook route entry', 'data' => ['event_id' => $event->id ?? null, 'event_type' => $event->type ?? null], 'hypothesisId' => 'H1'])."\n", FILE_APPEND | LOCK_EX);
        // #endregion

        $object = ($event->data ?? null)?->object ?? null;
        if ($object === null) {
            Log::info('Stripe webhook: No event data object', [
                'event_id' => $event->id ?? null,
                'event_type' => $event->type ?? null,
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:no_object', 'message' => 'no data object, returning 200', 'data' => ['event_id' => $event->id ?? null], 'hypothesisId' => 'H1'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion

            return response('No object', 200);
        }

        $metadata = $object->metadata ?? new \stdClass;
        $domain = is_object($metadata) ? ($metadata->app_domain ?? null) : null;

        if (! $domain) {
            Log::info('Stripe webhook: No app_domain in metadata', [
                'event_id' => $event->id,
                'event_type' => $event->type,
            ]);
            // #region agent log
            file_put_contents($logPath, json_encode(['id' => 'wh_'.uniqid(), 'timestamp' => (int) (microtime(true) * 1000), 'location' => 'StripeWebhookController.php:no_app_domain', 'message' => 'no app_domain, returning 200', 'data' => ['event_id' => $event->id], 'hypothesisId' => 'H1'])."\n", FILE_APPEND | LOCK_EX);
            // #endregion

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

<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFrontWebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front rule webhook (EREG-132): a rule on the contact@ inbox posts every
 * inbound message here, so a customer's email flags their lien filings.
 *
 * Front gives this endpoint 5 seconds for a 2xx and never retries; enough
 * missed windows and it disables the webhook. So nothing slow happens here:
 * verify the signature, queue the event, answer. ProcessFrontWebhookEvent
 * does the matching on the queue. Message bodies are never logged.
 */
class FrontWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        // Raw body: the signature covers the exact bytes Front sent.
        $payload = $request->getContent();

        if (! app()->environment(['local', 'testing']) && ! $this->verifySignature(
            $request->header('x-front-signature'),
            $request->header('x-front-request-timestamp'),
            $payload,
            config('services.front.webhook_key'),
        )) {
            Log::warning('Front webhook - Invalid signature');

            return response('Invalid signature', 401);
        }

        // Front sends a validation request when the webhook is (re-)enabled
        // and expects the challenge echoed back.
        if ($challenge = $request->header('x-front-challenge')) {
            return response()->json(['challenge' => $challenge]);
        }

        $data = json_decode($payload, true);

        if (! is_array($data)) {
            Log::warning('Front webhook - Invalid JSON', ['error' => json_last_error_msg()]);

            return response('Invalid JSON', 400);
        }

        try {
            if (($data['type'] ?? null) === 'inbound_received') {
                ProcessFrontWebhookEvent::dispatch($data);
            }
        } catch (\Throwable $e) {
            Log::error('Front webhook - Could not queue the event', [
                'conversation_id' => $data['payload']['conversation']['id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        // Always 200 once verified: an error response counts toward Front
        // disabling the webhook.
        return response('Event received', 200);
    }

    /**
     * Front signs base64(HMAC-SHA256("{timestamp}:{body}")) with the rule's
     * signing secret.
     */
    private function verifySignature(?string $signature, ?string $timestamp, string $payload, ?string $secret): bool
    {
        if (blank($signature) || blank($timestamp) || blank($secret)) {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $timestamp.':'.$payload, $secret, true));

        return hash_equals($expected, $signature);
    }
}

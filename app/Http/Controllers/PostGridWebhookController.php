<?php

namespace App\Http\Controllers;

use App\Domains\Marketing\Enums\MailProvider;
use App\Domains\Marketing\Models\MarketingMailing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PostGridWebhookController extends Controller
{
    /**
     * Handle PostGrid webhook events.
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify the webhook signature
        if (! $this->verifySignature($request)) {
            Log::warning('PostGrid webhook signature verification failed');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $eventType = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];

        Log::info('PostGrid webhook received', [
            'type' => $eventType,
            'id' => $data['id'] ?? null,
        ]);

        // Handle known event types
        if (in_array($eventType, ['letter.created', 'letter.updated', 'postcard.created', 'postcard.updated'])) {
            $this->handleMailpieceEvent($data);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle mailpiece events (letter or postcard).
     */
    protected function handleMailpieceEvent(array $data): void
    {
        $providerId = $data['id'] ?? null;

        if (! $providerId) {
            Log::warning('PostGrid webhook missing provider ID', ['data' => $data]);

            return;
        }

        // Look up the mailing by provider ID
        $mailing = MarketingMailing::query()
            ->where('provider', MailProvider::PostGrid)
            ->where('provider_id', $providerId)
            ->first();

        if (! $mailing) {
            Log::info('PostGrid webhook: mailing not found', ['provider_id' => $providerId]);

            return;
        }

        // Update the mailing with webhook data
        $mailing->updateFromWebhook($data);

        Log::info('PostGrid webhook: mailing updated', [
            'mailing_id' => $mailing->id,
            'status' => $data['status'] ?? null,
        ]);
    }

    /**
     * Verify the PostGrid webhook signature.
     *
     * PostGrid sends a PostGrid-Signature header in the format:
     * t=timestamp,v1=signature
     *
     * The signature is HMAC-SHA256(timestamp . "." . rawBody, webhook_secret)
     */
    protected function verifySignature(Request $request): bool
    {
        $secret = config('services.postgrid.webhook_secret');

        // If no secret is configured, skip verification (development mode)
        if (empty($secret)) {
            Log::warning('PostGrid webhook secret not configured, skipping verification');

            return true;
        }

        $signatureHeader = $request->header('PostGrid-Signature');

        if (! $signatureHeader) {
            return false;
        }

        // Parse the signature header
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['v1'] ?? null;

        if (! $timestamp || ! $signature) {
            return false;
        }

        // Check timestamp tolerance (reject if older than 5 minutes)
        $tolerance = config('services.postgrid.webhook_tolerance_seconds', 300);
        $timestampAge = time() - (int) $timestamp;

        if ($timestampAge > $tolerance || $timestampAge < -60) {
            Log::warning('PostGrid webhook timestamp out of tolerance', [
                'timestamp' => $timestamp,
                'age_seconds' => $timestampAge,
            ]);

            return false;
        }

        // Compute expected signature
        $rawBody = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        // Constant-time comparison
        return hash_equals($expectedSignature, $signature);
    }
}

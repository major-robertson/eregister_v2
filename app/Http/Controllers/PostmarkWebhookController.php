<?php

namespace App\Http\Controllers;

use App\Support\Email\RecordEmailBounce;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Postmark delivery-event webhook: flags user accounts whose address Postmark
 * has suppressed (hard bounce or spam complaint), so we stop queueing mail to
 * them and the portal prompts the user to update their email.
 *
 * Soft/transient bounces are deliberately ignored: Postmark retries those
 * itself and the address may recover.
 *
 * Configure in Postmark under Server > Webhooks with the Bounce, Spam
 * Complaint, and Subscription Change events, pointing at
 * /webhooks/postmark?token={POSTMARK_WEBHOOK_TOKEN}.
 */
class PostmarkWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifyToken($request)) {
            Log::warning('Postmark webhook token verification failed');

            return response()->json(['error' => 'Invalid token'], 401);
        }

        $recordType = $request->input('RecordType');

        // SubscriptionChange payloads carry the address in Recipient; the
        // Bounce and SpamComplaint payloads use Email.
        $email = $request->input('Email') ?? $request->input('Recipient');

        $reason = match ($recordType) {
            'Bounce' => $request->input('Type') === 'HardBounce' ? 'hard_bounce' : null,
            'SpamComplaint' => 'spam_complaint',
            'SubscriptionChange' => $this->subscriptionChangeReason($request),
            default => null,
        };

        if ($reason !== null && is_string($email) && $email !== '') {
            RecordEmailBounce::record($email, $reason);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Only suppressions Postmark imposed (bounce/complaint) flag the address.
     * ManualSuppression is the recipient unsubscribing via Postmark's link —
     * that's an opt-out, not a dead address, and reactivations
     * (SuppressSending=false) never flag.
     */
    protected function subscriptionChangeReason(Request $request): ?string
    {
        if (! $request->boolean('SuppressSending')) {
            return null;
        }

        return match ($request->input('SuppressionReason')) {
            'HardBounce' => 'hard_bounce',
            'SpamComplaint' => 'spam_complaint',
            default => null,
        };
    }

    protected function verifyToken(Request $request): bool
    {
        $token = config('services.postmark.webhook_token');

        // If no token is configured, skip verification (development mode)
        if (empty($token)) {
            Log::warning('Postmark webhook token not configured, skipping verification');

            return true;
        }

        $provided = $request->query('token') ?? $request->header('X-Postmark-Webhook-Token');

        return is_string($provided) && hash_equals($token, $provided);
    }
}

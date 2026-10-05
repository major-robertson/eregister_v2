<?php

namespace App\Http\Controllers;

use App\Models\EmailUnsubscribe;
use App\Models\User;
use App\Models\WebsiteInvitation;
use App\Support\Email\RecordEmailBounce;
use App\Support\HappyWebsites;
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
 * /webhooks/postmark?token={POSTMARK_WEBHOOK_TOKEN}. Webhooks are set per
 * message stream, so the broadcast stream (promotional mail) needs the same
 * webhook as the transactional one.
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

        if ($recordType === 'SubscriptionChange' && is_string($email) && $email !== '') {
            $this->syncMarketingOptOut($request, $email);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Inbound mail: a customer answered one of the Happy Websites intro
     * emails. The reply itself goes straight to the Happy Websites inboxes.
     * One more Reply-To address, signed per customer, brings a copy here so
     * the reminder skips them (HappyWebsites::replyTrackingAddress).
     *
     * Configure in Postmark on the inbound stream, pointing at
     * /webhooks/postmark/inbound?token={POSTMARK_WEBHOOK_TOKEN}.
     */
    public function inbound(Request $request): JsonResponse
    {
        if (! $this->verifyToken($request)) {
            Log::warning('Postmark inbound webhook token verification failed');

            return response()->json(['error' => 'Invalid token'], 401);
        }

        $userId = HappyWebsites::userIdFromReplyHash($request->input('MailboxHash'));

        if ($userId !== null) {
            WebsiteInvitation::query()
                ->where('user_id', $userId)
                ->whereNull('replied_at')
                ->update(['replied_at' => now()]);

            // "Stop" or "unsubscribe" as the answer is an opt-out. People read
            // these replies too, but this one must not depend on that.
            $reply = (string) ($request->input('StrippedTextReply') ?: $request->input('TextBody'));

            if (preg_match('/^\W*(stop|unsubscribe|remove me|opt out)\b/i', $reply) === 1 && $user = User::find($userId)) {
                EmailUnsubscribe::unsubscribe($user, EmailUnsubscribe::CATEGORY_MARKETING);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * On the broadcast stream (promotional mail) the recipient unsubscribing
     * through Postmark's link is our "marketing" opt-out, and resubscribing
     * on Postmark's page takes it back. Postmark suppresses only that one
     * stream; recording it here stops marketing mail everywhere.
     */
    protected function syncMarketingOptOut(Request $request, string $email): void
    {
        if ($request->input('MessageStream') !== config('services.postmark.broadcast_stream')) {
            return;
        }

        $optedOut = $request->boolean('SuppressSending') && $request->input('SuppressionReason') === 'ManualSuppression';
        $optedBackIn = ! $request->boolean('SuppressSending') && $request->input('Origin') === 'Recipient';

        if (! $optedOut && ! $optedBackIn) {
            return;
        }

        foreach (User::query()->where('email', $email)->get() as $user) {
            $optedOut
                ? EmailUnsubscribe::unsubscribe($user, EmailUnsubscribe::CATEGORY_MARKETING)
                : EmailUnsubscribe::resubscribe($user, EmailUnsubscribe::CATEGORY_MARKETING);
        }
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

<?php

namespace App\Support;

use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Happy Websites, our sister company (see config/happy_websites.php).
 */
class HappyWebsites
{
    /** The sent_emails type that marks a customer's one request for a free mockup. */
    public const REQUEST_EMAIL_TYPE = 'happy_websites_request';

    /**
     * A link to their site, tagged so their analytics and their lead emails
     * show the visitor came from eRegister. $medium says from where:
     * "portal" or "site".
     */
    public static function url(string $path, string $medium): string
    {
        return rtrim(config('happy_websites.url'), '/').'/'.ltrim($path, '/').'?'.http_build_query([
            'utm_source' => 'eregister',
            'utm_medium' => $medium,
            'utm_campaign' => 'websites',
        ]);
    }

    /**
     * The inboxes that get customers' requests and their replies.
     *
     * @return list<string>
     */
    public static function inboxes(): array
    {
        return array_values((array) config('happy_websites.lead_email'));
    }

    /**
     * The extra Reply-To address on the intro emails that tells the app a
     * customer answered (PostmarkWebhookController::inbound), or null while
     * Postmark inbound is not set up. "abc@inbound.postmarkapp.com" becomes
     * "abc+w123s4f9c2a1b7d3e@inbound.postmarkapp.com": Postmark hands the part
     * after the plus sign back as MailboxHash.
     */
    public static function replyTrackingAddress(User $user): ?string
    {
        $inbound = config('happy_websites.intro.inbound_address');

        if (! is_string($inbound) || ! str_contains($inbound, '@')) {
            return null;
        }

        [$mailbox, $domain] = explode('@', $inbound, 2);

        return $mailbox.'+'.self::replyHash($user->getKey()).'@'.$domain;
    }

    /** Letters and digits only, signed so a hash can't be made up for another customer. */
    public static function replyHash(int $userId): string
    {
        return 'w'.$userId.'s'.substr(hash_hmac('sha256', 'websites-intro-reply:'.$userId, (string) config('app.key')), 0, 12);
    }

    /** The customer a reply hash belongs to, or null when the hash is not one of ours. */
    public static function userIdFromReplyHash(mixed $hash): ?int
    {
        if (! is_string($hash) || preg_match('/^w(\d+)s[0-9a-f]{12}$/', $hash, $matches) !== 1) {
            return null;
        }

        $userId = (int) $matches[1];

        return hash_equals(self::replyHash($userId), $hash) ? $userId : null;
    }

    public static function requestedBy(User $user): bool
    {
        return self::requestRows($user)->exists();
    }

    /**
     * Take the request back, so the customer can ask again after a send failed.
     */
    public static function forgetRequest(User $user): void
    {
        self::requestRows($user)->delete();
    }

    private static function requestRows(User $user): Builder
    {
        return SentEmail::query()
            ->where('email_type', self::REQUEST_EMAIL_TYPE)
            ->where('emailable_type', $user->getMorphClass())
            ->where('emailable_id', $user->getKey());
    }
}

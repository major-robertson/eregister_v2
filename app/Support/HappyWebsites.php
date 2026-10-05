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

<?php

namespace App\Domains\ResaleCert;

use App\Domains\Business\Models\Business;
use App\Models\EmailSequence;
use App\Models\User;
use App\Support\SignupIntent;

/**
 * Starts the resale certificate follow-up emails: the resale_started
 * sequence, whose timing and stop rules live on EmailSequence. Someone who
 * came for resale certificates and has not subscribed gets a reminder an
 * hour later, a day after that, and three days after that. One series per
 * person, ever.
 *
 * Three moments start it:
 * - registration, when the visitor picked the certificate door (or, with no
 *   door recorded, landed on a resale certificate page);
 * - the resale dashboard, for people who came from those pages. It also
 *   attaches the business once it exists, so the emails can name its state;
 * - the checkout page, for anyone: opening it is a clear sign of intent.
 */
class ResaleFollowUp
{
    public const SEQUENCE = 'resale_started';

    /**
     * Called right after registration, while the sign-up intent is still in
     * the session.
     */
    public static function onSignup(User $user): ?EmailSequence
    {
        $product = SignupIntent::product();

        // The door they clicked wins: a resale page visitor who chose the
        // permit door wants a registration, not certificates.
        $cameForCertificates = $product !== null
            ? $product === 'resale-cert'
            : $user->signedUpFromResaleCerts();

        return $cameForCertificates ? static::start($user) : null;
    }

    /**
     * The dashboard doubles as the pricing page, so browsing it alone does
     * not start the emails: only people who came for certificates get them.
     */
    public static function onDashboard(User $user, Business $business): ?EmailSequence
    {
        if (static::existing($user) === null && ! $user->signedUpFromResaleCerts()) {
            return null;
        }

        return static::start($user, $business);
    }

    public static function onCheckout(User $user, Business $business): ?EmailSequence
    {
        return static::start($user, $business);
    }

    public static function start(User $user, ?Business $business = null): ?EmailSequence
    {
        if ($business?->subscribed(config('resale_cert.subscription_type'))) {
            return null;
        }

        $sequence = EmailSequence::firstOrCreate(
            [
                'sequence_type' => self::SEQUENCE,
                'sequenceable_type' => $user->getMorphClass(),
                'sequenceable_id' => $user->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'business_id' => $business?->getKey(),
                'customer_type' => $business ? EmailSequence::detectCustomerType($business) : 'new',
                'resume_url' => route('resale-cert.checkout'),
                'next_send_at' => now()->addMinutes(
                    (new EmailSequence(['sequence_type' => self::SEQUENCE]))->config()['delays'][0]
                ),
            ]
        );

        // Signed up before setting up the business: attach it once it exists.
        if ($business !== null && $sequence->business_id === null) {
            $sequence->update(['business_id' => $business->getKey()]);
        }

        return $sequence;
    }

    private static function existing(User $user): ?EmailSequence
    {
        return EmailSequence::query()
            ->where('sequence_type', self::SEQUENCE)
            ->where('sequenceable_type', $user->getMorphClass())
            ->where('sequenceable_id', $user->getKey())
            ->first();
    }
}

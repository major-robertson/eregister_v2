<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\EmailSequence;
use App\Models\User;

/**
 * Starts the lien waiver follow-up emails (the sequences themselves, their
 * timing and their stop rules live on EmailSequence).
 *
 * - waiver_started: a signup from a waiver page who hasn't made a waiver.
 * - waiver_unsigned: a free-plan user saved a waiver; the series sells
 *   signing it here (Pro) and, on day 7, filing to protect the payment.
 */
class WaiverNurture
{
    /**
     * Called right after registration. The starter's choices are still in the
     * session, so the emails can link back to the same state and waiver type.
     */
    public static function onSignup(User $user): ?EmailSequence
    {
        if (! $user->signedUpFromWaivers()) {
            return null;
        }

        return EmailSequence::firstOrCreate(
            [
                'sequence_type' => 'waiver_started',
                'sequenceable_type' => $user->getMorphClass(),
                'sequenceable_id' => $user->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'customer_type' => 'new',
                'resume_url' => static::resumeUrl(WaiverIntent::get()),
                'next_send_at' => now()->addMinutes(static::firstDelay('waiver_started')),
            ]
        );
    }

    /**
     * Called when the wizard saves a new waiver. Pro seat holders already
     * have what the series sells; states that require signing on paper can't
     * use it; and one series at a time per person is plenty.
     */
    public static function onWaiverSaved(LienWaiver $waiver, User $user, Business $business): ?EmailSequence
    {
        if (WaiverEntitlements::hasPaidAccess($business, $user)) {
            return null;
        }

        // Same test as WaiverFormResolver: a notary or witness rules e-sign out.
        $rules = WaiverStateRegistry::for((string) $waiver->state);

        if (! $rules['esign_allowed'] || $rules['notarization_required'] || $rules['witness_required']) {
            return null;
        }

        $alreadyRunning = EmailSequence::query()
            ->where('sequence_type', 'waiver_unsigned')
            ->where('user_id', $user->getKey())
            ->whereNull('completed_at')
            ->whereNull('suppressed_at')
            ->exists();

        if ($alreadyRunning) {
            return null;
        }

        return EmailSequence::firstOrCreate(
            [
                'sequence_type' => 'waiver_unsigned',
                'sequenceable_type' => $waiver->getMorphClass(),
                'sequenceable_id' => $waiver->getKey(),
            ],
            [
                'user_id' => $user->getKey(),
                'business_id' => $business->getKey(),
                'customer_type' => EmailSequence::detectCustomerType($business),
                'resume_url' => route('lien.waivers.show', $waiver),
                'next_send_at' => now()->addMinutes(static::firstDelay('waiver_unsigned')),
            ]
        );
    }

    /**
     * Back into the wizard by way of the starter route, which re-stores the
     * choices and sends a logged-out visitor to log in (not to register).
     *
     * @param  array{state: ?string, direction: ?string, kind: ?string, source: ?string}|null  $intent
     */
    public static function resumeUrl(?array $intent): string
    {
        if (($intent['state'] ?? null) === null) {
            return route('lien.waivers.create');
        }

        return route('liens.lien-waivers.start', array_filter([
            'state' => strtolower($intent['state']),
            'direction' => $intent['direction'] ?? null,
            'kind' => $intent['kind'] ?? null,
            'returning' => 1,
        ]));
    }

    private static function firstDelay(string $sequenceType): int
    {
        return (new EmailSequence(['sequence_type' => $sequenceType]))->config()['delays'][0];
    }
}

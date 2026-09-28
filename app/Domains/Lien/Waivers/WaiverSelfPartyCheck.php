<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Models\User;

/**
 * Catches a waiver whose other party is the user: their own company, their
 * own name, or their own email. It happens when someone picks the wrong side
 * of the payment (a contractor getting paid chooses "I'm paying someone") and
 * then types their own details where the sub or the customer belongs. The
 * form would then print them on both sides of the waiver.
 */
class WaiverSelfPartyCheck
{
    public const COMPANY = 'company';

    public const EMAIL = 'email';

    /** Words that don't tell two company names apart. */
    private const ENTITY_WORDS = [
        'the', 'llc', 'inc', 'incorporated', 'corp', 'corporation', 'co', 'company', 'ltd', 'limited', 'lp', 'llp', 'pllc', 'pc',
    ];

    /**
     * COMPANY when the company or person typed for the other party is the
     * user's business (name, legal name, DBA) or the user's own name; EMAIL
     * when only the email is theirs; null when the other party is someone else.
     */
    public static function match(User $user, ?Business $business, ?string $company, ?string $person, ?string $email): ?string
    {
        $own = array_filter(array_map(self::normalizeName(...), [
            $business?->name,
            $business?->legal_name,
            $business?->dba_name,
            $user->name,
        ]));

        foreach ([$company, $person] as $typed) {
            $typed = self::normalizeName($typed);

            if ($typed !== '' && in_array($typed, $own, true)) {
                return self::COMPANY;
            }
        }

        if (filled($email) && strcasecmp(trim($email), trim((string) $user->email)) === 0) {
            return self::EMAIL;
        }

        return null;
    }

    /**
     * Case, punctuation and entity words aside: "Rural Concrete Creations, LLC"
     * and "rural concrete creations" normalize to the same string.
     */
    public static function normalizeName(?string $name): string
    {
        $words = preg_split('/[^a-z0-9]+/', str_replace('&', ' and ', strtolower((string) $name)), -1, PREG_SPLIT_NO_EMPTY);

        return implode('', array_diff($words, self::ENTITY_WORDS));
    }
}

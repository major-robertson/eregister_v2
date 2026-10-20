<?php

namespace App\Domains\Lien\Support;

/**
 * Which state a ZIP code belongs to, by its first three digits (EREG-135).
 * Party addresses are checked against it so a ZIP code and a state that
 * don't seem to agree are flagged before the order goes in. A mismatch is a
 * warning the customer can confirm ("This address is correct as typed"),
 * never a hard block: the table is not authoritative.
 *
 * TODO: no USPS or other address-verification client exists in the repo
 * yet. If one is added, check the full address against it as well.
 */
final class ZipStateRanges
{
    /**
     * Three-digit ZIP prefixes by state, as inclusive [low, high] ranges.
     * Covers the 50 states, DC, the territories and the military codes.
     *
     * @var array<string, list<array{int, int}>>
     */
    public const RANGES = [
        'AL' => [[350, 369]],
        'AK' => [[995, 999]],
        'AZ' => [[850, 865]],
        'AR' => [[716, 729]],
        'CA' => [[900, 961]],
        'CO' => [[800, 816]],
        'CT' => [[60, 69]],
        'DE' => [[197, 199]],
        'DC' => [[200, 200], [202, 205], [569, 569]],
        'FL' => [[320, 339], [341, 349]],
        'GA' => [[300, 319], [398, 399]],
        'HI' => [[967, 968]],
        'ID' => [[832, 838]],
        'IL' => [[600, 629]],
        'IN' => [[460, 479]],
        'IA' => [[500, 528]],
        'KS' => [[660, 679]],
        'KY' => [[400, 427]],
        'LA' => [[700, 715]],
        'ME' => [[39, 49]],
        'MD' => [[206, 219]],
        'MA' => [[10, 27], [55, 55]],
        'MI' => [[480, 499]],
        'MN' => [[550, 567]],
        'MS' => [[386, 397]],
        'MO' => [[630, 658]],
        'MT' => [[590, 599]],
        'NE' => [[680, 693]],
        'NV' => [[889, 898]],
        'NH' => [[30, 38]],
        'NJ' => [[70, 89]],
        'NM' => [[870, 884]],
        'NY' => [[5, 5], [100, 149]],
        'NC' => [[270, 289]],
        'ND' => [[580, 588]],
        'OH' => [[430, 459]],
        'OK' => [[730, 731], [734, 749]],
        'OR' => [[970, 979]],
        'PA' => [[150, 196]],
        'RI' => [[28, 29]],
        'SC' => [[290, 299]],
        'SD' => [[570, 577]],
        'TN' => [[370, 385]],
        'TX' => [[733, 733], [750, 799], [885, 885]],
        'UT' => [[840, 847]],
        'VT' => [[50, 54], [56, 59]],
        'VA' => [[201, 201], [220, 246]],
        'WA' => [[980, 994]],
        'WV' => [[247, 268]],
        'WI' => [[530, 549]],
        'WY' => [[820, 831]],
        'PR' => [[6, 7], [9, 9]],
        'VI' => [[8, 8]],
        'GU' => [[969, 969]],
        'MP' => [[969, 969]],
        'PW' => [[969, 969]],
        'FM' => [[969, 969]],
        'MH' => [[969, 969]],
        'AS' => [[967, 967]],
        'AA' => [[340, 340]],
        'AE' => [[90, 98]],
        'AP' => [[962, 966]],
    ];

    /**
     * Known ZIP codes whose post office sits across a state line from some
     * of the addresses it serves. Not exhaustive.
     *
     * @var array<string, list<string>>
     */
    public const CROSS_STATE = [
        '06390' => ['NY'], // Fishers Island, NY
        '42223' => ['TN'], // Fort Campbell, KY
        '59221' => ['ND'], // Fairview, MT
        '71749' => ['LA'], // Junction City, AR
        '73949' => ['TX'], // Texhoma, OK
        '82063' => ['CO'], // Jelm, WY
        '83414' => ['WY'], // Alta, WY
        '84536' => ['AZ'], // Monument Valley, UT
        '89439' => ['CA'], // Verdi, NV
        '97635' => ['CA'], // New Pine Creek, OR
    ];

    /** Names for the codes config('states') doesn't list. */
    private const OTHER_NAMES = [
        'DC' => 'Washington, DC',
        'PR' => 'Puerto Rico',
        'VI' => 'the U.S. Virgin Islands',
        'GU' => 'Guam',
        'MP' => 'the Northern Mariana Islands',
        'PW' => 'Palau',
        'FM' => 'Micronesia',
        'MH' => 'the Marshall Islands',
        'AS' => 'American Samoa',
        'AA' => 'Armed Forces Americas',
        'AE' => 'Armed Forces Europe',
        'AP' => 'Armed Forces Pacific',
    ];

    /**
     * The states a ZIP code belongs to (most have one), or [] when its
     * prefix isn't assigned or it doesn't start with five digits.
     *
     * @return list<string>
     */
    public static function statesFor(?string $zip): array
    {
        $zip5 = self::zip5($zip);

        if ($zip5 === null) {
            return [];
        }

        $prefix = (int) substr($zip5, 0, 3);
        $states = [];

        foreach (self::RANGES as $state => $ranges) {
            foreach ($ranges as [$low, $high]) {
                if ($prefix >= $low && $prefix <= $high) {
                    $states[] = $state;

                    break;
                }
            }
        }

        return array_values(array_unique([...$states, ...(self::CROSS_STATE[$zip5] ?? [])]));
    }

    /**
     * Whether the ZIP code and the state agree. True when either is blank,
     * the ZIP code isn't a five-digit (or ZIP+4) code, its prefix isn't in
     * the table, or the state isn't one the table knows: there is nothing to
     * check then.
     */
    public static function matches(?string $zip, ?string $state): bool
    {
        $state = strtoupper(trim((string) $state));
        $states = self::statesFor($zip);

        if ($states === [] || ! array_key_exists($state, self::RANGES)) {
            return true;
        }

        return in_array($state, $states, true);
    }

    /**
     * The plain-words warning for a ZIP code that seems to be in another
     * state, or null when they agree.
     */
    public static function mismatchMessage(?string $zip, ?string $state): ?string
    {
        if (self::matches($zip, $state)) {
            return null;
        }

        $actual = self::stateName(self::statesFor($zip)[0]);
        $stateName = self::stateName(strtoupper(trim((string) $state)));

        return 'ZIP code '.self::zip5($zip)." is in {$actual}, not {$stateName}. Check the ZIP code and the state.";
    }

    public static function stateName(string $state): string
    {
        return config('states')[$state] ?? self::OTHER_NAMES[$state] ?? $state;
    }

    /**
     * The five-digit ZIP code ("85210" from "85210-1234"), or null.
     */
    private static function zip5(?string $zip): ?string
    {
        return preg_match('/^\s*(\d{5})(?:[\s-]?\d{4})?\s*$/', (string) $zip, $match) === 1 ? $match[1] : null;
    }
}

<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Models\LienProject;

/**
 * Checks on a party's mailing address in the filing wizard (EREG-135).
 */
final class PartyAddress
{
    /** Street words compared in their short form, so "123 Main Street" matches "123 Main St". */
    private const SHORT_FORMS = [
        'street' => 'st',
        'avenue' => 'ave',
        'road' => 'rd',
        'drive' => 'dr',
        'boulevard' => 'blvd',
        'lane' => 'ln',
        'court' => 'ct',
        'place' => 'pl',
        'circle' => 'cir',
        'terrace' => 'ter',
        'highway' => 'hwy',
        'parkway' => 'pkwy',
        'north' => 'n',
        'south' => 's',
        'east' => 'e',
        'west' => 'w',
    ];

    /**
     * Whether an address is the project's jobsite address: the same street
     * line, and the same ZIP code (or the same city when either has no ZIP).
     */
    public static function sameAsJobsite(LienProject $project, ?string $line1, ?string $city, ?string $zip): bool
    {
        $line = self::normalize($line1);
        $jobsiteLine = self::normalize($project->jobsite_address1);

        if ($line === '' || $line !== $jobsiteLine) {
            return false;
        }

        $zip5 = substr(preg_replace('/\D/', '', (string) $zip), 0, 5);
        $jobsiteZip5 = substr(preg_replace('/\D/', '', (string) $project->jobsite_zip), 0, 5);

        if ($zip5 !== '' && $jobsiteZip5 !== '') {
            return $zip5 === $jobsiteZip5;
        }

        return self::normalize($city) === self::normalize($project->jobsite_city);
    }

    /**
     * Whether a street line was entered without a street number ("Main St").
     * A blank line is not flagged here; the required rules catch it.
     */
    public static function lacksStreetNumber(?string $line1): bool
    {
        $line1 = trim((string) $line1);

        return $line1 !== '' && preg_match('/\d/', $line1) !== 1;
    }

    private static function normalize(?string $value): string
    {
        $words = preg_split('/\s+/', trim((string) preg_replace('/[^a-z0-9]+/', ' ', strtolower((string) $value))), -1, PREG_SPLIT_NO_EMPTY);

        return implode(' ', array_map(fn (string $word) => self::SHORT_FORMS[$word] ?? $word, $words ?: []));
    }
}

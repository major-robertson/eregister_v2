<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Str;

/**
 * Turns the free-text county on a filing or project into the key of a county
 * data file. Google gives "Los Angeles County", users type "Los Angeles",
 * both must find lien_counties/ca/los-angeles.php; "St. Louis City" must not
 * collide with "St. Louis County".
 */
class LienCountyKey
{
    public static function normalize(?string $county): ?string
    {
        $value = trim((string) preg_replace('/\s+/', ' ', (string) $county));

        if ($value === '') {
            return null;
        }

        $value = trim((string) preg_replace('/\b(county|parish|borough)$/i', '', $value));
        $slug = Str::slug($value);

        return $slug === '' ? null : $slug;
    }

    public static function forFiling(LienFiling $filing): ?string
    {
        $project = $filing->project;

        return self::normalize(
            $filing->jurisdiction_county
                ?: $project?->jobsite_county
                ?: $project?->jobsite_county_google
        );
    }

    /**
     * The county as it should print ("Jackson County" → "Jackson"), for the
     * STATE OF / COUNTY OF caption.
     */
    public static function displayName(?string $county): ?string
    {
        $value = trim((string) preg_replace('/\s+/', ' ', (string) $county));

        if ($value === '') {
            return null;
        }

        $value = trim((string) preg_replace('/\b(county|parish|borough)$/i', '', $value));

        return $value === '' ? null : $value;
    }
}

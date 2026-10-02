<?php

namespace App\Support\Seo;

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Illuminate\Support\Str;

/**
 * URL slugs for the per-state SEO pages. Every state landing (mechanics
 * liens, resale certificates, ...) shares this so "/liens/new-york" and
 * "/resale-certificates/new-york" always agree on what a state is called.
 */
final class States
{
    /**
     * The 50 states, code => name.
     *
     * @return array<string, string>
     */
    public static function names(): array
    {
        return WaiverStateRegistry::STATE_NAMES;
    }

    public static function name(string $code): ?string
    {
        return self::names()[strtoupper($code)] ?? null;
    }

    /** "New York" => "new-york" */
    public static function slug(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Resolve a URL segment to a state code. Accepts the full-name slug and,
     * for redirect purposes, the two-letter code in any case.
     *
     * @param  array<string, string>|null  $names  code => name; defaults to the 50 states
     */
    public static function codeFromSlug(string $segment, ?array $names = null): ?string
    {
        $names ??= self::names();
        $segment = strtolower($segment);

        if (strlen($segment) === 2 && isset($names[strtoupper($segment)])) {
            return strtoupper($segment);
        }

        foreach ($names as $code => $name) {
            if (self::slug($name) === $segment) {
                return $code;
            }
        }

        return null;
    }

    /**
     * States that share a land border, plus sensible companions for the two
     * that have none (Alaska, Hawaii) and for DC. Used for the "nearby
     * states" cross-links so a Texas page points at Oklahoma and Louisiana
     * rather than at Tennessee and Utah.
     *
     * @var array<string, array<int, string>>
     */
    private const BORDERS = [
        'AL' => ['FL', 'GA', 'MS', 'TN'],
        'AK' => ['WA', 'OR', 'HI', 'CA'],
        'AZ' => ['CA', 'CO', 'NV', 'NM', 'UT'],
        'AR' => ['LA', 'MS', 'MO', 'OK', 'TN', 'TX'],
        'CA' => ['AZ', 'NV', 'OR'],
        'CO' => ['AZ', 'KS', 'NE', 'NM', 'OK', 'UT', 'WY'],
        'CT' => ['MA', 'NY', 'RI'],
        'DE' => ['MD', 'NJ', 'PA'],
        'DC' => ['MD', 'VA', 'DE', 'PA'],
        'FL' => ['AL', 'GA'],
        'GA' => ['AL', 'FL', 'NC', 'SC', 'TN'],
        'HI' => ['CA', 'WA', 'OR', 'AK'],
        'ID' => ['MT', 'NV', 'OR', 'UT', 'WA', 'WY'],
        'IL' => ['IN', 'IA', 'KY', 'MO', 'WI'],
        'IN' => ['IL', 'KY', 'MI', 'OH'],
        'IA' => ['IL', 'MN', 'MO', 'NE', 'SD', 'WI'],
        'KS' => ['CO', 'MO', 'NE', 'OK'],
        'KY' => ['IL', 'IN', 'MO', 'OH', 'TN', 'VA', 'WV'],
        'LA' => ['AR', 'MS', 'TX'],
        'ME' => ['NH', 'MA', 'VT'],
        'MD' => ['DE', 'PA', 'VA', 'WV', 'DC'],
        'MA' => ['CT', 'NH', 'NY', 'RI', 'VT'],
        'MI' => ['IN', 'OH', 'WI'],
        'MN' => ['IA', 'ND', 'SD', 'WI'],
        'MS' => ['AL', 'AR', 'LA', 'TN'],
        'MO' => ['AR', 'IL', 'IA', 'KS', 'KY', 'NE', 'OK', 'TN'],
        'MT' => ['ID', 'ND', 'SD', 'WY'],
        'NE' => ['CO', 'IA', 'KS', 'MO', 'SD', 'WY'],
        'NV' => ['AZ', 'CA', 'ID', 'OR', 'UT'],
        'NH' => ['ME', 'MA', 'VT'],
        'NJ' => ['DE', 'NY', 'PA'],
        'NM' => ['AZ', 'CO', 'OK', 'TX', 'UT'],
        'NY' => ['CT', 'MA', 'NJ', 'PA', 'VT'],
        'NC' => ['GA', 'SC', 'TN', 'VA'],
        'ND' => ['MN', 'MT', 'SD'],
        'OH' => ['IN', 'KY', 'MI', 'PA', 'WV'],
        'OK' => ['AR', 'CO', 'KS', 'MO', 'NM', 'TX'],
        'OR' => ['CA', 'ID', 'NV', 'WA'],
        'PA' => ['DE', 'MD', 'NJ', 'NY', 'OH', 'WV'],
        'RI' => ['CT', 'MA'],
        'SC' => ['GA', 'NC'],
        'SD' => ['IA', 'MN', 'MT', 'NE', 'ND', 'WY'],
        'TN' => ['AL', 'AR', 'GA', 'KY', 'MS', 'MO', 'NC', 'VA'],
        'TX' => ['AR', 'LA', 'NM', 'OK'],
        'UT' => ['AZ', 'CO', 'ID', 'NV', 'NM', 'WY'],
        'VT' => ['MA', 'NH', 'NY'],
        'VA' => ['KY', 'MD', 'NC', 'TN', 'WV', 'DC'],
        'WA' => ['ID', 'OR'],
        'WV' => ['KY', 'MD', 'OH', 'PA', 'VA'],
        'WI' => ['IL', 'IA', 'MI', 'MN'],
        'WY' => ['CO', 'ID', 'MT', 'NE', 'SD', 'UT'],
    ];

    /**
     * Bordering states for a cross-link strip, restricted to the states that
     * actually have a page in the given set, and padded from the alphabetical
     * neighbours when a state has fewer bordering pages than $min.
     *
     * @param  array<string, string>|null  $names  code => name (defaults to the 50 states)
     * @return array<string, string> code => name
     */
    public static function bordering(string $code, int $min = 4, ?array $names = null): array
    {
        $names ??= self::names();
        $code = strtoupper($code);

        $picked = [];
        foreach (self::BORDERS[$code] ?? [] as $border) {
            if (isset($names[$border])) {
                $picked[$border] = $names[$border];
            }
        }

        if (count($picked) < $min) {
            foreach (self::neighbours($code, $min * 2, $names) as $fill => $name) {
                if (count($picked) >= $min) {
                    break;
                }
                $picked[$fill] ??= $name;
            }
        }

        unset($picked[$code]);

        return $picked;
    }

    /**
     * Alphabetical neighbours for a cross-link strip, wrapping at both ends
     * so the first and last states still get a full set of links.
     *
     * @param  array<string, string>|null  $names  code => name
     * @return array<string, string> code => name
     */
    public static function neighbours(string $code, int $count = 4, ?array $names = null): array
    {
        $names ??= self::names();
        $codes = array_keys($names);
        $index = array_search(strtoupper($code), $codes, true);
        $total = count($codes);

        if ($index === false || $total <= 1) {
            return [];
        }

        $picked = [];
        $half = intdiv($count, 2);
        for ($offset = -$half; $offset <= $count - $half; $offset++) {
            if ($offset === 0) {
                continue;
            }
            $neighbour = $codes[(($index + $offset) % $total + $total) % $total];
            $picked[$neighbour] = $names[$neighbour];
            if (count($picked) === $count) {
                break;
            }
        }

        return $picked;
    }
}

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

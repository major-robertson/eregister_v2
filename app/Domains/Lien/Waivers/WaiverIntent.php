<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;

/**
 * The choices a visitor makes on a marketing page before they have an
 * account: the project's state, which side of the exchange they are on, and
 * which waiver they need. Kept in the session across registration and
 * business setup so the wizard opens where the landing page left off.
 */
class WaiverIntent
{
    private const SESSION_KEY = 'waiver_intent';

    /**
     * Unknown states, directions, and kinds become null rather than errors:
     * the intent only ever pre-selects, the wizard still validates.
     *
     * @param  array<string, mixed>  $intent
     * @return array{state: ?string, direction: ?string, kind: ?string, source: ?string}
     */
    public static function normalize(array $intent): array
    {
        $state = strtoupper((string) ($intent['state'] ?? ''));

        return [
            'state' => WaiverStateRegistry::isSupported($state) ? $state : null,
            'direction' => WaiverDirection::tryFrom((string) ($intent['direction'] ?? ''))?->value,
            'kind' => WaiverKind::tryFrom((string) ($intent['kind'] ?? ''))?->value,
            'source' => filled($intent['source'] ?? null) ? (string) $intent['source'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $intent
     * @return array{state: ?string, direction: ?string, kind: ?string, source: ?string}
     */
    public static function store(array $intent): array
    {
        $normalized = self::normalize($intent);

        session()->put(self::SESSION_KEY, $normalized);

        return $normalized;
    }

    /**
     * @return array{state: ?string, direction: ?string, kind: ?string, source: ?string}|null
     */
    public static function get(): ?array
    {
        $intent = session()->get(self::SESSION_KEY);

        return is_array($intent) ? self::normalize($intent) : null;
    }

    /**
     * Read and clear: the wizard consumes the intent exactly once.
     *
     * @return array{state: ?string, direction: ?string, kind: ?string, source: ?string}|null
     */
    public static function pull(): ?array
    {
        $intent = self::get();

        session()->forget(self::SESSION_KEY);

        return $intent;
    }
}

<?php

namespace App\Domains\Lien\Support;

/**
 * The lien product a visitor picked in the chooser and the state it was for
 * (EREG-143), kept in the session across sign-up, business setup and lien
 * onboarding, so the first project form starts in that state and the project
 * page offers that document first. It only ever pre-selects: the wizard
 * still decides what can be filed.
 */
final class LienProductIntent
{
    private const SESSION_KEY = 'lien_product_intent';

    /**
     * Unknown products and states become null rather than errors.
     *
     * @param  array<string, mixed>  $intent
     * @return array{product: ?string, state: ?string}
     */
    public static function normalize(array $intent): array
    {
        $product = (string) ($intent['product'] ?? '');
        $state = strtoupper((string) ($intent['state'] ?? ''));

        return [
            'product' => LienProducts::isOrderable($product) ? $product : null,
            'state' => array_key_exists($state, config('states', [])) ? $state : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $intent
     * @return array{product: ?string, state: ?string}
     */
    public static function store(array $intent): array
    {
        $normalized = self::normalize($intent);

        session()->put(self::SESSION_KEY, $normalized);

        return $normalized;
    }

    /**
     * @return array{product: ?string, state: ?string}|null
     */
    public static function get(): ?array
    {
        $intent = session()->get(self::SESSION_KEY);

        return is_array($intent) ? self::normalize($intent) : null;
    }

    public static function product(): ?string
    {
        return self::get()['product'] ?? null;
    }

    public static function state(): ?string
    {
        return self::get()['state'] ?? null;
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}

<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * What a visitor came to do, carried in the session from the marketing page
 * through registration and business setup: which product (sales tax
 * registration, resale certificates, liens, LLC), the ad keyword variant they
 * saw, and the state they picked. It replaces guessing the product from the
 * Referer header of the register page, which is lost whenever a browser or
 * an ad click strips it.
 *
 * The intent only ever pre-selects. Every flow still validates.
 */
class SignupIntent
{
    private const SESSION_KEY = 'signup_intent';

    public const PRODUCTS = ['sales-tax', 'resale-cert', 'liens', 'llc'];

    /**
     * Landing paths reported for a product when the referer did not arrive,
     * so sign-up attribution (users.signup_landing_path) keeps working.
     */
    public const LANDING_PATHS = [
        'sales-tax' => '/sales-tax-registration',
        'resale-cert' => '/resale-certificates',
        'liens' => '/liens',
        'llc' => '/llc',
    ];

    /**
     * @param  array<string, mixed>  $intent
     * @return array{product: ?string, intent: ?string, state: ?string, source: ?string}
     */
    public static function normalize(array $intent): array
    {
        $product = (string) ($intent['product'] ?? '');
        $state = strtoupper((string) ($intent['state'] ?? ''));
        $slug = (string) ($intent['intent'] ?? '');

        return [
            'product' => in_array($product, self::PRODUCTS, true) ? $product : null,
            'intent' => preg_match('/^[a-z0-9-]{1,40}$/', $slug) === 1 ? $slug : null,
            'state' => array_key_exists($state, config('states', [])) ? $state : null,
            'source' => filled($intent['source'] ?? null) ? Str::limit((string) $intent['source'], 120, '') : null,
        ];
    }

    /**
     * Store the intent carried by a request's query string (?product=,
     * ?intent=, ?state=). A request without any of them leaves the stored
     * intent alone, so a reload or a "back" keeps what the visitor chose.
     *
     * @return array{product: ?string, intent: ?string, state: ?string, source: ?string}|null
     */
    public static function captureFromRequest(Request $request): ?array
    {
        if (! $request->hasAny(['product', 'intent', 'state'])) {
            return self::get();
        }

        $current = self::get() ?? [];

        return self::store([
            'product' => $request->query('product', $current['product'] ?? null),
            'intent' => $request->query('intent', $current['intent'] ?? null),
            'state' => $request->query('state', $current['state'] ?? null),
            'source' => $request->query('source', $current['source'] ?? null),
        ]);
    }

    /**
     * @param  array<string, mixed>  $intent
     * @return array{product: ?string, intent: ?string, state: ?string, source: ?string}
     */
    public static function store(array $intent): array
    {
        $normalized = self::normalize($intent);

        session()->put(self::SESSION_KEY, $normalized);

        return $normalized;
    }

    /**
     * @return array{product: ?string, intent: ?string, state: ?string, source: ?string}|null
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

    /**
     * The landing path to report for the stored product when none was
     * captured from the referer.
     */
    public static function fallbackLandingPath(): ?string
    {
        $product = self::product();

        return $product ? (self::LANDING_PATHS[$product] ?? null) : null;
    }
}

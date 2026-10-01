<?php

namespace App\Domains\ResaleCert\Seo;

/**
 * Researched, state-specific copy for "/resale-certificates/{state}", loaded
 * from database/data/resale_states/{xx}.php: the agency, the form, the
 * buyer's registration number, what the state accepts, the expiration,
 * good-faith and misuse rules, sourced facts and a plain-English summary.
 *
 * A state without a file returns null and its page falls back to the rule
 * row alone. The files are display copy only: ResaleStateRuleSeeder still
 * drives the certificate generator.
 *
 * Data file contract (null where the research could not confirm a value):
 *
 *   'state' => 'TX', 'researched_on' => '2026-10-01',
 *   'agency' => ['name' => '…', 'short' => 'the Comptroller', 'url' => 'https://…'],
 *   'resale_page_url' => 'https://…',
 *   'form' => ['number' => '01-339', 'title' => '…', 'pdf_url' => '…', 'prescribed' => true,
 *              'revision' => '…', 'notes' => '…',
 *              'label' => '…', 'pdf_label' => '…'], // optional: override the composed form label / link text
 *   'issuer_model' => 'purchaser_completed', // or state_issued
 *   'registration' => ['name' => '…', 'number_name' => '…', 'format' => '…', 'verify_url' => '…'],
 *   'accepts' => [
 *       'mtc' => ['value' => true, 'cite' => '…'],
 *       'sst' => ['value' => false, 'cite' => '…'],
 *       'out_of_state_registration' => ['value' => true, 'cite' => '…', 'notes' => '…'],
 *       'blanket' => ['value' => true, 'cite' => '…'],
 *   ],
 *   'expiration' => ['label' => 'No expiration', 'summary' => '…', 'cite' => '…'],
 *   'good_faith' => ['summary' => '…', 'cite' => '…'],
 *   'misuse_penalty' => ['summary' => '…', 'cite' => '…'],
 *   'facts' => [['text' => '…', 'source_url' => 'https://…'], …],
 *   'state_notes' => '…',
 *   'sources' => [['title' => '…', 'url' => 'https://…'], …],
 */
final class ResaleStateContent
{
    /** @var array<string, array<string, mixed>|null> */
    private static array $cache = [];

    public static function directory(): string
    {
        return database_path('data/resale_states');
    }

    /** @return array<string, mixed>|null */
    public static function for(string $code): ?array
    {
        $code = strtoupper($code);

        if (array_key_exists($code, self::$cache)) {
            return self::$cache[$code];
        }

        $path = self::directory().'/'.strtolower($code).'.php';

        return self::$cache[$code] = is_file($path) ? require $path : null;
    }

    public static function exists(string $code): bool
    {
        return self::for($code) !== null;
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$cache = [];
    }
}

<?php

namespace App\Domains\Lien\Seo;

use App\Support\Seo\States;

/**
 * Researched depth for the "/liens/{state}" pages of the busiest states,
 * loaded from database/data/lien_depth/{xx}.php: how to file step by step,
 * what the claim must contain, the recording offices of the ten most
 * populous counties, recent law changes and six more FAQs. A state without
 * a file returns null and its page renders from the rule tables alone.
 *
 * Data file contract (null where the office or statute does not publish it):
 *
 *   'state' => 'TX', 'researched_on' => '2026-10-02',
 *   'filing_verb' => 'record',             // 'file' where the claim goes to a court clerk
 *   'county_intro' => '…',                 // state-wide facts above the county table, or null
 *   'how_to_file' => [['step' => 1, 'title' => '…', 'text' => '…', 'cite' => '…', 'residential_note' => null], …],
 *   'claim_contents' => ['document_name' => '…', 'required' => ['…'], 'cite' => '…',
 *                        'official_form_url' => null, 'notes' => null],
 *   'counties' => [['county' => 'Harris', 'office' => '…', 'url' => '…', 'erecording' => true|false|null,
 *                   'erecording_vendors' => ['…'], 'fee' => ['first_page_cents' => 2500,
 *                   'additional_page_cents' => 400, 'flat_cents' => null, 'summary' => '…', 'url' => '…'],
 *                   'mailing_address' => '…', 'notes' => '…'], …],   // notes: research provenance, never rendered
 *   'recent_changes' => [['title' => '…', 'effective' => '2022-01-01', 'summary' => '…', 'cite' => '…'], …],
 *                       // effective null: pending, not law
 *   'faqs' => [['q' => '…', 'a' => '…', 'cite' => '…'], …],
 *   'sources' => [['title' => '…', 'url' => '…', 'accessed' => '2026-10-02'], …],
 */
final class LienStateDepth
{
    /** The states with depth files, in the order the /liens hub lists them. */
    public const POPULAR = ['TX', 'CA', 'FL', 'NY', 'GA', 'NC', 'AZ', 'WA', 'PA', 'IL'];

    /** @var array<string, array<string, mixed>|null> */
    private static array $cache = [];

    public static function directory(): string
    {
        return database_path('data/lien_depth');
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

    /**
     * The popular states that have a depth file, code => name.
     *
     * @return array<string, string>
     */
    public static function popular(): array
    {
        $states = [];
        foreach (self::POPULAR as $code) {
            if (self::exists($code)) {
                $states[$code] = States::name($code);
            }
        }

        return $states;
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$cache = [];
    }
}

<?php

namespace App\Domains\SalesTax\Seo;

/**
 * Researched copy for "/sales-tax-registration/{state}", loaded from
 * database/data/sales_tax_states/{xx}.php: one file per state with a
 * statewide sales tax, plus DC. A state without a file has no page.
 *
 * Data file contract (null where the research could not confirm a value):
 *
 *   'state' => 'TX', 'name' => 'Texas', 'researched_on' => '2026-10-02',
 *   'term_label' => '…', // optional: display name when the researched term carries a long qualifier
 *   'agency' => ['name' => '…', 'short' => 'the Comptroller', 'url' => 'https://…'],
 *   'registration' => [
 *       'term' => 'Sales and Use Tax Permit',
 *       'portal' => ['name' => '…', 'url' => 'https://…'],
 *       'form' => ['number' => 'AP-201', 'title' => '…', 'pdf_url' => '…', 'online_only' => false],
 *       'fee' => ['amount_cents' => 0, 'summary' => '…', 'cite' => '…'],
 *       'renewal' => ['required' => false, 'summary' => '…', 'cite' => '…'],
 *       'timing' => ['online' => '2 to 3 weeks', 'paper' => '…', 'temporary_number' => false, 'summary' => '…', 'cite' => '…'],
 *       'number' => ['name' => 'Texas taxpayer number', 'format' => '11 digits', 'cite' => '…'],
 *   ],
 *   'nexus' => [
 *       'physical' => '…',
 *       'economic' => ['revenue_usd' => 500000, 'transactions' => null, 'period' => '…', 'effective' => '2019-10-01', 'cite' => '…'],
 *       'marketplace' => '…',
 *   ],
 *   'filing' => ['frequencies' => '…', 'rule' => '…', 'due_day' => '…', 'zero_return_required' => true, 'prepayments' => null, 'cite' => '…'],
 *   'rates' => ['state_rate_pct' => 6.25, 'local' => '…', 'sourcing' => '…', 'cite' => '…'],
 *   'local_registration' => ['required' => false, 'summary' => null],
 *   'penalties' => ['no_permit' => ['summary' => '…', 'cite' => '…'], 'late_filing' => ['summary' => '…', 'cite' => '…']],
 *   'connected' => ['covers' => ['sales tax', …], 'prerequisites' => '…', 'cite' => '…'],
 *   'facts' => [['text' => '…', 'source_url' => 'https://…'], …],
 *   'state_notes' => '…',
 *   'sources' => [['title' => '…', 'url' => 'https://…', 'accessed' => '2026-10-02'], …],
 */
final class SalesTaxStateContent
{
    /** @var array<string, array<string, mixed>|null> */
    private static array $cache = [];

    /** @var array<string, string>|null */
    private static ?array $all = null;

    public static function directory(): string
    {
        return database_path('data/sales_tax_states');
    }

    /** @return array<string, mixed>|null */
    public static function for(string $code): ?array
    {
        $code = strtoupper($code);

        if (array_key_exists($code, self::$cache)) {
            return self::$cache[$code];
        }

        $path = self::directory().'/'.strtolower($code).'.php';

        return self::$cache[$code] = preg_match('/^[A-Z]{2}$/', $code) && is_file($path) ? require $path : null;
    }

    public static function exists(string $code): bool
    {
        return self::for($code) !== null;
    }

    /**
     * Every state with a content file, code => name, sorted by name.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        if (self::$all !== null) {
            return self::$all;
        }

        $states = [];
        foreach (glob(self::directory().'/*.php') ?: [] as $file) {
            $code = strtoupper(basename($file, '.php'));
            if ($content = self::for($code)) {
                $states[$code] = $content['name'];
            }
        }
        asort($states);

        return self::$all = $states;
    }

    /** Used by tests to force re-reads of the data files. */
    public static function flush(): void
    {
        self::$cache = [];
        self::$all = null;
    }
}

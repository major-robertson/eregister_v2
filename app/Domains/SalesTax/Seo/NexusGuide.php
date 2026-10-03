<?php

namespace App\Domains\SalesTax\Seo;

use App\Domains\ResaleCert\Seo\ResaleStateContent;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Support\Seo\States;
use Illuminate\Support\Facades\Cache;

/**
 * Rows for the economic nexus thresholds guide: one per state with a sales
 * tax content file (the "/sales-tax-registration/{state}" pages), built
 * through the same cached SalesTaxStatePage objects, plus the states with no
 * general sales tax. Cached for a day; text and flags only, no URLs.
 */
final class NexusGuide
{
    /** Bump when the row shape changes. SalesTaxStatePage::CACHE_VERSION is part of the key too. */
    public const CACHE_VERSION = 1;

    /** States with no general sales tax, so no economic nexus threshold. */
    public const NO_SALES_TAX_STATES = ['AK', 'DE', 'MT', 'NH', 'OR'];

    public static function cacheKey(): string
    {
        return 'seo.nexus-guide.v'.SalesTaxStatePage::CACHE_VERSION.'.'.self::CACHE_VERSION;
    }

    /**
     * @return array{
     *     rows: array<string, array{code: string, name: string, list_name: string, slug: string, threshold: string, period: ?string, effective: ?string,
     *         agency: string, agency_url: ?string, revenue: int, transactions: ?int, both_tests: bool}>,
     *     no_tax: array<string, array{code: string, name: string, sentence: string, resale_slug: ?string}>
     * }
     */
    public static function data(): array
    {
        return Cache::remember(self::cacheKey(), now()->addDay(), function () {
            $states = Cache::remember(SalesTaxStatePage::statesCacheKey(), now()->addDay(), fn () => SalesTaxStatePage::availableStates());

            $rows = [];
            foreach (array_keys($states) as $code) {
                $page = Cache::remember(SalesTaxStatePage::cacheKey($code), now()->addDay(), fn () => SalesTaxStatePage::forCode($code));
                if (! $page) {
                    continue;
                }
                $economic = $page->content['nexus']['economic'];
                $rows[$code] = [
                    'code' => $code,
                    'name' => $page->name,
                    // For running text: "the District of Columbia".
                    'list_name' => $page->inName,
                    'slug' => $page->slug,
                    'threshold' => $page->thresholdPhrase(),
                    'period' => $page->thresholdPeriod(),
                    'effective' => $page->thresholdEffective(),
                    'agency' => $page->agencyName(),
                    'agency_url' => $page->agencyUrl(),
                    'revenue' => (int) $economic['revenue_usd'],
                    'transactions' => isset($economic['transactions']) ? (int) $economic['transactions'] : null,
                    'both_tests' => str_contains($page->thresholdPhrase(), ' and '),
                ];
            }

            $resaleStates = Cache::remember(ResaleStatePage::statesCacheKey(), now()->addDay(), fn () => ResaleStatePage::availableStates());
            $noTax = [];
            foreach (self::NO_SALES_TAX_STATES as $code) {
                $name = States::name($code);
                $noTax[$code] = [
                    'code' => $code,
                    'name' => $name,
                    'sentence' => self::noTaxSentence($code, $name),
                    'resale_slug' => isset($resaleStates[$code]) ? States::slug($name) : null,
                ];
            }

            return ['rows' => $rows, 'no_tax' => $noTax];
        });
    }

    /**
     * The first sentence of the resale research's note on the state, cut
     * before the resale advice ("Delaware has no state or local sales tax."),
     * or a generic sentence when the research has none.
     */
    private static function noTaxSentence(string $code, string $name): string
    {
        $content = ResaleStateContent::for($code) ?? [];
        $note = $content['no_sales_tax_note'] ?? $content['state_notes'] ?? null;

        if (is_string($note) && preg_match('/^(.+?[.!?])(?:\s|$)/', trim($note), $m) && str_contains($m[1], 'sales tax')) {
            $sentence = preg_replace('/, so .*$/', '.', $m[1]);

            return rtrim($sentence, '.').'.';
        }

        return "{$name} has no statewide sales tax.";
    }

    /** @return array<int, string> names of the states that also count transactions */
    public static function transactionStates(array $data): array
    {
        return array_column(array_filter($data['rows'], fn (array $row) => $row['transactions'] !== null), 'list_name');
    }

    /** @return array<int, string> names of the states where a seller must meet both tests */
    public static function bothTestStates(array $data): array
    {
        return array_column(array_filter($data['rows'], fn (array $row) => $row['both_tests']), 'list_name');
    }

    /**
     * The four FAQs on the economic nexus guide, worded from the data.
     *
     * @param  array{rows: array<string, array<string, mixed>>, no_tax: array<string, array<string, mixed>>}  $data
     * @return array<int, array{q: string, a: string}>
     */
    public static function faq(array $data): array
    {
        $rows = $data['rows'];
        $total = count($rows);

        $counts = array_count_values(array_column($rows, 'revenue'));
        arsort($counts);
        $common = (int) array_key_first($counts);
        $withCount = count(array_filter($rows, fn (array $row) => $row['revenue'] === $common && $row['transactions'] !== null));

        $highest = max(array_column($rows, 'revenue'));
        $top = array_map(
            fn (array $row) => "{$row['list_name']} ({$row['threshold']})",
            array_values(array_filter($rows, fn (array $row) => $row['revenue'] === $highest)),
        );

        $transactions = self::transactionStates($data);
        $both = self::bothTestStates($data);

        $noTax = $data['no_tax'];
        $extra = array_values(array_filter(array_column($noTax, 'sentence'), fn (string $sentence) => str_contains($sentence, ' but ')));

        return [
            [
                'q' => 'What is the most common economic nexus threshold?',
                'a' => self::money($common)." in sales. {$counts[$common]} of the {$total} jurisdictions in the table use that amount"
                    .($withCount ? ", and {$withCount} of them also count transactions." : '.'),
            ],
            [
                'q' => 'Which states have the highest threshold?',
                'a' => self::list($top).(count($top) === 1 ? ' sets' : ' set').' the highest threshold in the table.',
            ],
            [
                'q' => 'Which states count transactions as well as sales?',
                'a' => self::list($transactions).' also count transactions.'
                    .($both ? ' In '.self::list($both).', a seller must meet both tests. In the others, meeting either one is enough.' : ' Meeting either test is enough.'),
            ],
            [
                'q' => 'Which states have no sales tax?',
                'a' => self::list(array_column($noTax, 'name')).' have no statewide sales tax, so none of them sets a state economic nexus threshold.'
                    .($extra ? ' '.implode(' ', $extra) : ''),
            ],
        ];
    }

    /** "Alabama, Alaska and Arizona" */
    public static function list(array $names): string
    {
        $names = array_values($names);
        if (count($names) <= 2) {
            return implode(' and ', $names);
        }

        return implode(', ', array_slice($names, 0, -1)).' and '.end($names);
    }

    /** "$100,000" */
    public static function money(int $dollars): string
    {
        return '$'.number_format($dollars);
    }
}

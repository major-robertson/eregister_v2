<?php

namespace App\Domains\ResaleCert\Console;

use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use Database\Seeders\ResaleStateRuleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Brings resale_state_rules on a live site in line with ResaleStateRuleSeeder,
 * the source of truth for which forms each state accepts.
 *
 * Dry run by default: prints every field that differs (database value, then
 * seeder value) and every row missing from the database. --apply writes the
 * differing rows with updateOrCreate. Nothing is deleted. Safe to run again.
 */
class SyncStateRules extends Command
{
    protected $signature = 'resale:sync-state-rules
        {--apply : Write the changes (without it, only print them)}';

    protected $description = 'Compare resale_state_rules with ResaleStateRuleSeeder and, with --apply, write the differences';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $current = ResaleStateRule::query()->get()->keyBy('state_code');

        $lines = [];
        $changedRows = [];
        $creates = 0;

        foreach (ResaleStateRuleSeeder::rows() as $row) {
            $code = $row['state_code'];
            $existing = $current->get($code);

            if ($existing === null) {
                $lines[] = [$code, '(row)', 'missing', 'create'];
                $changedRows[$code] = $row;
                $creates++;

                continue;
            }

            foreach (ResaleStateRuleSeeder::FIELDS as $field) {
                $from = $existing->getAttribute($field);
                $to = $row[$field];

                if (! $this->sameValue($from, $to)) {
                    $lines[] = [$code, $field, $this->describe($from), $this->describe($to)];
                    $changedRows[$code] = $row;
                }
            }
        }

        if ($lines === []) {
            $this->info('resale_state_rules match the seeder. Nothing to do.');

            return self::SUCCESS;
        }

        $this->table(['State', 'Field', 'Database', 'Seeder'], $lines);

        $updates = count($lines) - $creates;
        $states = count($changedRows) - $creates;
        $scope = sprintf(
            '%d %s in %d %s, %d %s',
            $updates,
            $updates === 1 ? 'field' : 'fields',
            $states,
            $states === 1 ? 'state' : 'states',
            $creates,
            $creates === 1 ? 'missing row' : 'missing rows',
        );

        if (! $apply) {
            $this->line("Differences: {$scope}.");
            $this->warn('Dry run: nothing written. Re-run with --apply to write.');

            return self::SUCCESS;
        }

        foreach ($changedRows as $code => $row) {
            $rule = ResaleStateRule::updateOrCreate(['state_code' => $code], $row);
            $this->line(($rule->wasRecentlyCreated ? 'Created ' : 'Updated ').$code);

            // The public state page caches its rule row for a day.
            Cache::forget(ResaleStatePage::cacheKey($code));
        }

        Cache::forget(ResaleStatePage::statesCacheKey());

        $this->info("Wrote {$scope}. Nothing was deleted.");

        return self::SUCCESS;
    }

    /**
     * Compares a model attribute (booleans and metadata already cast) with a
     * seeder value. expiration_months has no cast, so it may come back as a
     * numeric string.
     */
    private function sameValue(mixed $database, mixed $seeder): bool
    {
        if ($database === null || $seeder === null) {
            return $database === $seeder;
        }

        if (is_bool($seeder)) {
            return (bool) $database === $seeder;
        }

        if (is_int($seeder)) {
            return is_numeric($database) && (int) $database === $seeder;
        }

        if (is_array($seeder)) {
            return is_array($database) && $database == $seeder;
        }

        return (string) $database === (string) $seeder;
    }

    private function describe(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_array($value) => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            default => (string) $value,
        };
    }
}

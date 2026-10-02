<?php

namespace App\Console\Commands;

use Database\Seeders\LienStateRuleSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Brings the public lien rule text in the database in line with the seed JSON
 * on a live site, without a full reseed.
 *
 * - lien_state_rules: replaced from lien_state_rules.json by LienStateRuleSeeder
 *   (delete + insert). Safe, because nothing references those rows by id.
 * - lien_deadline_rules: only the zero-offset preliminary-notice rows get their
 *   conditions_json "display" text copied from lien_deadline_rules.json. Never
 *   run LienDeadlineRuleSeeder on a live site: it deletes every row, and
 *   lien_project_deadlines.deadline_rule_id is nullOnDelete, so every customer
 *   deadline would lose its rule.
 *
 * Dry run by default. Pass --apply to write. Safe to run again.
 */
class SyncLienPublicRules extends Command
{
    protected $signature = 'lien:sync-public-rules
        {--apply : Write the changes (without it, only print the plan)}';

    protected $description = 'Sync lien state rules and zero-offset deadline display text from the seed JSON';

    /** Columns the public lien pages read, compared and printed one by one. */
    private const PAGE_FIELDS = [
        'public_notes',
        'enforcement_calc_method',
        'enforcement_deadline_days',
        'enforcement_deadline_months',
        'enforcement_deadline_trigger',
        'filing_location',
        'efile_allowed',
        'wrongful_lien_penalty',
        'penalty_details',
        'statute_references',
        'statute_url',
        'notarization_required',
        'verification_type',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->info('lien_state_rules');
        $stateChanges = $this->planStateRules();

        if ($apply && $stateChanges > 0) {
            // The seeder deletes then inserts; a transaction keeps the table
            // from being left empty if the insert fails.
            DB::transaction(fn () => $this->laravel->make(LienStateRuleSeeder::class)->run());
            $this->line("  Reseeded lien_state_rules from the JSON ({$stateChanges} states changed).");
        } elseif ($apply) {
            $this->line('  lien_state_rules already match the JSON; not reseeded.');
        }

        $this->newLine();
        $this->info('lien_deadline_rules (zero-offset display text)');
        [$displayChanges, $missing] = $this->syncDeadlineDisplay($apply);

        $total = $stateChanges + $displayChanges;

        $this->newLine();

        if ($apply) {
            if ($total > 0) {
                // The cache store holds only the SEO page caches (lien, resale
                // and sales tax state pages and blank documents, all under
                // versioned keys), rate limiter counters and framework items,
                // so a full flush is the simple, safe way to drop every page
                // built from the old rows. It is what `cache:clear` does.
                Cache::flush();
                $this->line('Cache flushed so the state pages rebuild from the new rows.');
            }

            $this->info("Done: {$stateChanges} states reseeded, {$displayChanges} deadline rows updated, {$missing} not found.");
        } else {
            $this->warn("Dry run: {$stateChanges} states and {$displayChanges} deadline rows would change, {$missing} not found. Nothing was saved.");
            if ($total > 0) {
                $this->line('Re-run with --apply to write.');
            }
        }

        return $missing > 0 ? 2 : self::SUCCESS;
    }

    /**
     * Prints one line per state whose row differs from the JSON and returns
     * how many states differ (including states that would be added or removed).
     */
    private function planStateRules(): int
    {
        $json = collect(json_decode(file_get_contents(database_path('seeders/data/lien_state_rules.json')), true))
            ->keyBy('state');
        $db = DB::table('lien_state_rules')->get()->keyBy('state');

        $changed = 0;
        $inSync = 0;

        foreach ($json as $state => $row) {
            $current = $db->get($state);

            if ($current === null) {
                $this->line("  {$state}: not in the database (would be added)");
                $changed++;

                continue;
            }

            $current = (array) $current;
            $diffs = [];
            $otherColumns = [];

            foreach ($row as $column => $value) {
                if ($this->sameValue($value, $current[$column] ?? null)) {
                    continue;
                }

                if (in_array($column, self::PAGE_FIELDS, true)) {
                    $from = $this->describe($column, $current[$column] ?? null);
                    $to = $this->describe($column, $value);
                    $diffs[] = $column.': '.$from.' -> '.$to.($from === $to ? ' (text changed)' : '');
                } else {
                    $otherColumns[] = $column;
                }
            }

            if ($diffs === [] && $otherColumns === []) {
                $inSync++;

                continue;
            }

            if ($otherColumns !== []) {
                $diffs[] = 'other columns: '.implode(', ', $otherColumns);
            }

            $this->line("  {$state}: ".implode('; ', $diffs));
            $changed++;
        }

        foreach ($db->keys()->diff($json->keys()) as $state) {
            $this->line("  {$state}: in the database but not the JSON (would be removed)");
            $changed++;
        }

        $this->line("  {$inSync} states in sync, {$changed} differ.");

        return $changed;
    }

    /**
     * Copies conditions_json.display from the JSON onto the matching
     * zero-offset deadline rows. Only conditions_json and updated_at change.
     *
     * @return array{0: int, 1: int} [rows changed (or that would change), rows not found]
     */
    private function syncDeadlineDisplay(bool $apply): array
    {
        $rows = json_decode(file_get_contents(database_path('seeders/data/lien_deadline_rules.json')), true);

        $wanted = [];
        foreach ($rows as $row) {
            if (($row['calc_method'] ?? null) !== 'days_after_date' || (int) ($row['offset_days'] ?? 0) !== 0) {
                continue;
            }

            $conditions = is_string($row['conditions_json'] ?? null)
                ? json_decode($row['conditions_json'], true)
                : ($row['conditions_json'] ?? null);

            if (empty($conditions['display'])) {
                continue;
            }

            $wanted[] = ['row' => $row, 'display' => $conditions['display']];
        }

        $types = DB::table('lien_document_types')->pluck('id', 'slug')->all();

        $changes = 0;
        $missing = 0;

        foreach ($wanted as $item) {
            $r = $item['row'];
            $label = "{$r['state']} {$r['document_type_slug']} {$r['claimant_type']} {$r['trigger_event']} [{$r['effective_scope']}]";
            $typeId = $types[$r['document_type_slug']] ?? null;

            if ($typeId === null) {
                $this->line("  MISSING TYPE {$r['document_type_slug']} for {$label}");
                $missing++;

                continue;
            }

            $matches = DB::table('lien_deadline_rules')
                ->where('state', $r['state'])
                ->where('document_type_id', $typeId)
                ->where('claimant_type', $r['claimant_type'])
                ->where('trigger_event', $r['trigger_event'])
                ->where('effective_scope', $r['effective_scope'])
                ->where('calc_method', 'days_after_date')
                ->where('offset_days', 0)
                ->get();

            if ($matches->count() !== 1) {
                $this->line("  MISSING ROW ({$matches->count()} matches) {$label}");
                $missing++;

                continue;
            }

            $existing = $matches->first();
            $current = is_string($existing->conditions_json)
                ? (json_decode($existing->conditions_json, true) ?: [])
                : (array) $existing->conditions_json;

            if (($current['display'] ?? null) === $item['display']) {
                $this->line("  ok        #{$existing->id} {$label}");

                continue;
            }

            $new = $current;
            $new['display'] = $item['display'];

            $this->line('  '.($apply ? 'UPDATE    ' : 'would set ')."#{$existing->id} {$label}");
            $this->line('     from: '.json_encode($current, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('     to:   '.json_encode($new, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            if ($apply) {
                DB::table('lien_deadline_rules')->where('id', $existing->id)->update([
                    'conditions_json' => json_encode($new, JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
            }

            $changes++;
        }

        $this->line('  '.count($wanted).' rows in the JSON with display text; '.$changes.($apply ? ' updated' : ' would change').", {$missing} not found.");

        return [$changes, $missing];
    }

    /**
     * Compares a JSON seed value with what MySQL hands back: booleans come
     * back as 0/1, decimals as "12.000", and JSON columns may be reformatted.
     */
    private function sameValue(mixed $json, mixed $db): bool
    {
        $jsonBlank = $json === null || $json === '';
        $dbBlank = $db === null || $db === '';

        if ($jsonBlank || $dbBlank) {
            return $jsonBlank && $dbBlank;
        }

        if (is_bool($json)) {
            return $json === (bool) $db;
        }

        if (is_int($json) || is_float($json)) {
            return is_numeric($db) && abs((float) $json - (float) $db) < 0.0005;
        }

        if (is_array($json)) {
            return is_string($db) && json_decode($db, true) == $json;
        }

        if ((string) $json === (string) $db) {
            return true;
        }

        // A JSON column: compare the decoded values.
        $decodedJson = json_decode((string) $json, true);
        $decodedDb = json_decode((string) $db, true);

        return is_array($decodedJson) && is_array($decodedDb) && $decodedJson == $decodedDb;
    }

    private function describe(string $column, mixed $value): string
    {
        if ($value === null || $value === '') {
            return $column === 'public_notes' ? 'none' : 'null';
        }

        if ($column === 'public_notes') {
            return count(preg_split('/\s+/u', trim((string) $value))).' words';
        }

        if (in_array($column, ['efile_allowed', 'notarization_required'], true)) {
            return ((bool) $value) ? 'true' : 'false';
        }

        if (in_array($column, ['penalty_details', 'statute_references'], true)) {
            $text = (string) $value;

            return '"'.(mb_strlen($text) > 60 ? mb_substr($text, 0, 57).'...' : $text).'"';
        }

        return is_numeric($value) ? (string) ($value + 0) : (string) $value;
    }
}

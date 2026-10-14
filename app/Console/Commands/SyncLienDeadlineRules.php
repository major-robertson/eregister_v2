<?php

namespace App\Console\Commands;

use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Applies corrected deadline rules for the named states on a live site and
 * recomputes those states' project deadlines, printing every due date that
 * moves. Built for EREG-113 (Virginia) and the other statute corrections on
 * EREG-91.
 *
 * - lien_state_rules: the state's row is updated column by column from
 *   lien_state_rules.json.
 * - lien_deadline_rules: each JSON row is matched to its live row by state,
 *   document type, claimant type and scope, and only that row's date columns
 *   change, so lien_project_deadlines keep their deadline_rule_id. Rows the
 *   JSON adds or drops are reported, never inserted or deleted.
 * - Every project in the state is run through DeadlineCalculator again.
 *
 * Dry run by default: everything runs in a transaction that is rolled back,
 * so the printed dates are exactly what --apply would store.
 */
class SyncLienDeadlineRules extends Command
{
    protected $signature = 'lien:sync-deadline-rules
        {states* : Two-letter state codes, e.g. VA}
        {--apply : Write the changes (without it, only print the plan)}';

    protected $description = 'Apply corrected lien deadline rules for some states and recompute their project deadlines';

    /** Deadline rule columns that decide a due date. */
    private const RULE_FIELDS = ['trigger_event', 'calc_method', 'offset_days', 'offset_months', 'day_of_month', 'is_required', 'conditions_json'];

    public function handle(DeadlineCalculator $calculator): int
    {
        $states = array_values(array_unique(array_map('strtoupper', $this->argument('states'))));
        $apply = (bool) $this->option('apply');
        $problems = 0;

        DB::beginTransaction();

        try {
            foreach ($states as $state) {
                $this->info("{$state} rules");
                $problems += $this->syncStateRow($state);
                $problems += $this->syncDeadlineRows($state);
            }

            $this->newLine();
            $this->info('Project deadlines');
            $moved = $this->recalculate($states, $calculator);
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }

        $this->newLine();

        if ($apply && $problems === 0) {
            DB::commit();
            // Same reasoning as lien:sync-public-rules: the store holds only
            // versioned SEO page caches, rate limits and framework items.
            Cache::flush();
            $this->info("Done: {$moved} deadlines moved. Cache flushed so the state pages rebuild.");

            return self::SUCCESS;
        }

        DB::rollBack();

        if ($apply) {
            $this->error("Nothing was saved: {$problems} problems above.");

            return 2;
        }

        $this->warn("Dry run: {$moved} deadlines would move, {$problems} problems. Nothing was saved.");
        $this->line('Re-run with --apply to write.');

        return $problems > 0 ? 2 : self::SUCCESS;
    }

    private function syncStateRow(string $state): int
    {
        $json = collect($this->json('lien_state_rules.json'))->firstWhere('state', $state);
        $current = DB::table('lien_state_rules')->where('state', $state)->first();

        if (! $json || ! $current) {
            $this->line('  state row: '.($json ? 'not in the database' : 'not in the JSON'));

            return 1;
        }

        $changes = [];
        foreach ($json as $column => $value) {
            if (! $this->same($value, $current->{$column} ?? null)) {
                $changes[$column] = is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $value;
            }
        }

        if ($changes) {
            DB::table('lien_state_rules')->where('state', $state)->update($changes + ['updated_at' => now()]);
        }

        $this->line('  state row: '.($changes ? implode(', ', array_keys($changes)) : 'in sync'));

        return 0;
    }

    private function syncDeadlineRows(string $state): int
    {
        $types = DB::table('lien_document_types')->pluck('id', 'slug')->all();
        $problems = 0;
        $changed = 0;
        $seen = [];

        foreach (collect($this->json('lien_deadline_rules.json'))->where('state', $state) as $row) {
            $label = "{$row['document_type_slug']} {$row['claimant_type']} [{$row['effective_scope']}]";
            $matches = DB::table('lien_deadline_rules')
                ->where('state', $state)
                ->where('document_type_id', $types[$row['document_type_slug']] ?? 0)
                ->where('claimant_type', $row['claimant_type'])
                ->where('effective_scope', $row['effective_scope'])
                ->get();

            if ($matches->count() !== 1) {
                $this->line("  MISSING ROW ({$matches->count()} matches) {$label}");
                $problems++;

                continue;
            }

            $existing = $matches->first();
            $seen[] = $existing->id;
            $changes = [];
            foreach (self::RULE_FIELDS as $column) {
                if (! $this->same($row[$column] ?? null, $existing->{$column})) {
                    $changes[$column] = $row[$column] ?? null;
                    $this->line("  #{$existing->id} {$label} {$column}: ".$this->show($existing->{$column}).' -> '.$this->show($row[$column] ?? null));
                }
            }

            if ($changes) {
                DB::table('lien_deadline_rules')->where('id', $existing->id)->update($changes + ['updated_at' => now()]);
                $changed++;
            }
        }

        foreach (DB::table('lien_deadline_rules')->where('state', $state)->whereNotIn('id', $seen)->pluck('id') as $id) {
            $this->line("  #{$id} is in the database but not the JSON (left as is)");
            $problems++;
        }

        $this->line("  {$changed} deadline rows changed.");

        return $problems;
    }

    /** @param  array<int, string>  $states */
    private function recalculate(array $states, DeadlineCalculator $calculator): int
    {
        $moved = 0;
        $projects = LienProject::withoutGlobalScope('business')->whereIn('jobsite_state', $states)->with('business')->orderBy('id')->get();

        foreach ($projects as $project) {
            $before = $this->dueDates($project);
            $calculator->calculateForProject($project);
            $after = $this->dueDates($project);

            foreach ($after as $slug => $due) {
                $old = $before[$slug] ?? null;
                if ($old === $due) {
                    continue;
                }

                $moved++;
                $passed = $due !== null && $due < now()->toDateString() ? '  PASSED' : '';
                $paid = $project->filings()->withoutGlobalScope('business')->whereNotNull('paid_at')->whereNull('completed_at')->pluck('id')->implode(', ');

                $this->line(sprintf(
                    '  project %d (%s, %s) last furnish %s, completion %s: %s %s -> %s%s%s',
                    $project->id,
                    $project->business?->legal_name ?? 'business '.$project->business_id,
                    $project->claimant_type?->value ?? 'no role',
                    $project->last_furnish_date?->toDateString() ?? 'none',
                    $project->completion_date?->toDateString() ?? 'none',
                    $slug,
                    $old ?? 'none',
                    $due ?? 'none',
                    $passed,
                    $paid !== '' ? "  open paid filing {$paid}" : '',
                ));
            }
        }

        $this->line("  {$projects->count()} projects recomputed, {$moved} deadlines moved.");

        return $moved;
    }

    /** @return array<string, ?string> document slug => due date */
    private function dueDates(LienProject $project): array
    {
        return $project->deadlines()->withoutGlobalScope('business')
            ->whereNotNull('deadline_rule_id')
            ->with('documentType')
            ->get()
            ->mapWithKeys(fn ($d) => [$d->documentType->slug => $d->due_date?->toDateString()])
            ->all();
    }

    private function json(string $file): array
    {
        return json_decode(file_get_contents(database_path('seeders/data/'.$file)), true);
    }

    /** JSON seed value vs MySQL value: 0/1 booleans, "12.000" decimals, reformatted JSON. */
    private function same(mixed $json, mixed $db): bool
    {
        if (($json === null || $json === '') && ($db === null || $db === '')) {
            return true;
        }

        if (is_bool($json)) {
            return $db !== null && (bool) $db === $json;
        }

        if (is_numeric($json) && is_numeric($db)) {
            return $json + 0 == $db + 0;
        }

        if (is_string($json) && is_string($db) && $json !== $db) {
            $a = json_decode($json, true);
            $b = json_decode($db, true);

            return is_array($a) && is_array($b) && $a == $b;
        }

        return $json === $db;
    }

    private function show(mixed $value): string
    {
        return $value === null ? 'null' : (string) $value;
    }
}

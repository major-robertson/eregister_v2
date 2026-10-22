<?php

namespace App\Domains\Lien\Admin\Actions\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Captures a comparable scalar snapshot of model fields and diffs two snapshots
 * into the `{field: {from, to}}` shape used by the activity-log audit events.
 * Enums collapse to their backing value and dates to 'Y-m-d' so the stored diff
 * is JSON-stable; the Blade layer formats these for display. Strings that differ
 * only in line endings or surrounding whitespace count as unchanged, so a
 * textarea round-trip (CRLF vs LF, a trimmed trailing newline) isn't logged.
 */
trait TracksFieldChanges
{
    /**
     * @param  list<string>  $fields
     * @return array<string, scalar|null>
     */
    protected function fieldSnapshot(Model $model, array $fields): array
    {
        $out = [];

        foreach ($fields as $field) {
            $value = $model->getAttribute($field);

            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            } elseif ($value instanceof CarbonInterface) {
                $value = $value->toDateString();
            }

            $out[$field] = $value;
        }

        return $out;
    }

    /**
     * @param  array<string, scalar|null>  $before
     * @param  array<string, scalar|null>  $after
     * @return array<string, array{from: scalar|null, to: scalar|null}>
     */
    protected function diffSnapshots(array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $field => $value) {
            $from = $before[$field] ?? null;

            if (! $this->sameSnapshotValue($from, $value)) {
                $changes[$field] = ['from' => $from, 'to' => $value];
            }
        }

        return $changes;
    }

    /**
     * Strict comparison, except two strings match when they're equal after
     * normalising line endings to LF and trimming. Internal whitespace still counts.
     */
    protected function sameSnapshotValue(mixed $from, mixed $to): bool
    {
        if (is_string($from) && is_string($to)) {
            return $this->normalizeForComparison($from) === $this->normalizeForComparison($to);
        }

        return $from === $to;
    }

    private function normalizeForComparison(string $value): string
    {
        return trim(str_replace(["\r\n", "\r"], "\n", $value));
    }
}

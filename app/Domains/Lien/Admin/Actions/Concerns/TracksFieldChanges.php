<?php

namespace App\Domains\Lien\Admin\Actions\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * Captures a comparable scalar snapshot of model fields and diffs two snapshots
 * into the `{field: {from, to}}` shape used by the activity-log audit events.
 * Enums collapse to their backing value and dates to 'Y-m-d' so the stored diff
 * is JSON-stable; the Blade layer formats these for display.
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
            if (($before[$field] ?? null) !== $value) {
                $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $value];
            }
        }

        return $changes;
    }
}

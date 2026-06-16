<?php

namespace App\Domains\Forms\Engine;

/**
 * Walks a merged form definition and sets `drives_conditional => true`
 * on every field that's referenced by a `when`, `badge_when[*].condition`,
 * or `help_when[*].condition` somewhere in the definition.
 *
 * Without this, definition authors have to remember to mark every field
 * that drives a conditional somewhere — and they forget. The
 * `drives_conditional` flag tells the rendered field to use
 * `wire:model.live` instead of plain `wire:model` so dependent fields
 * actually appear/disappear in real time. A missed flag means a field
 * gates content but only updates after a full step navigation, which
 * looks like a UI bug.
 *
 * Reference resolution is prefix-aware so a `$state.x` condition inside
 * a core step doesn't mistakenly mark a same-named core field, and vice
 * versa:
 *
 *   - `$root.fieldKey`  → marks fieldKey within `core_steps` (any step).
 *   - `$state.fieldKey` → marks fieldKey within `state_steps` (any step).
 *   - `$row.fieldKey`   → marks fieldKey within the parent repeater's
 *                         schema (same row context).
 *   - bare `fieldKey`   → marks fieldKey within the same step it was
 *                         referenced from. ConditionEvaluator's runtime
 *                         fallback chain (row → state → core) is more
 *                         permissive but in practice authors only use
 *                         bare keys to mean "sibling field in the same
 *                         step", which is the safe target to mark.
 *
 * Existing explicit `drives_conditional => true` continues to work —
 * setting `true` over `true` is a no-op.
 */
class DrivesConditionalDetector
{
    /**
     * @param  array<string, mixed>  $merged
     * @return array<string, mixed>
     */
    public function detect(array $merged): array
    {
        // Walk core_steps first so $state references inside core steps
        // can find their target in state_steps below; both passes are
        // independent so order doesn't matter for correctness.
        foreach (['core_steps', 'state_steps'] as $bucket) {
            $steps = $merged[$bucket] ?? [];
            foreach ($steps as $stepKey => $step) {
                $fields = $step['fields'] ?? [];
                $refs = $this->collectReferencesFromFields($fields);

                foreach ($refs['local'] as $key) {
                    if (isset($fields[$key])) {
                        $fields[$key]['drives_conditional'] = true;
                    }
                }

                foreach ($refs['root'] as $key) {
                    $merged['core_steps'] = $this->markIn($merged['core_steps'] ?? [], $key);
                }

                foreach ($refs['state'] as $key) {
                    $merged['state_steps'] = $this->markIn($merged['state_steps'] ?? [], $key);
                }

                foreach ($refs['row'] as [$repeaterKey, $rowFieldKey]) {
                    if (isset($fields[$repeaterKey]['schema'][$rowFieldKey])) {
                        $fields[$repeaterKey]['schema'][$rowFieldKey]['drives_conditional'] = true;
                    }
                }

                $merged[$bucket][$stepKey]['fields'] = $fields;
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, array<string, mixed>>  $fields
     * @return array{
     *     local: array<int, string>,
     *     root: array<int, string>,
     *     state: array<int, string>,
     *     row: array<int, array{0: string, 1: string}>
     * }
     */
    private function collectReferencesFromFields(array $fields): array
    {
        $refs = ['local' => [], 'root' => [], 'state' => [], 'row' => []];

        foreach ($fields as $field) {
            // Top-level conditions for the field itself.
            $this->mergeRefs($refs, $this->extractFromCondition($field['when'] ?? null));
            foreach (($field['badge_when'] ?? []) as $candidate) {
                $this->mergeRefs($refs, $this->extractFromCondition($candidate['condition'] ?? null));
            }
            foreach (($field['help_when'] ?? []) as $candidate) {
                $this->mergeRefs($refs, $this->extractFromCondition($candidate['condition'] ?? null));
            }

            // Repeater schemas: $row.* references inside a sub-field's
            // condition target a sibling within the same row, so they
            // bind to the parent repeater key.
            if (($field['type'] ?? null) === 'repeater' && ! empty($field['schema'])) {
                $repeaterKey = array_search($field, $fields, true);
                $rowRefs = $this->collectRowRefsFromSchema($field['schema']);
                foreach ($rowRefs as $rowFieldKey) {
                    if ($repeaterKey !== false) {
                        $refs['row'][] = [(string) $repeaterKey, $rowFieldKey];
                    }
                }

                // $root and $state references inside repeater sub-fields
                // bubble up to the appropriate bucket. Local refs inside
                // a repeater schema mean "sibling sub-field in the same
                // row" and are handled via the $row bucket above (so we
                // skip non-row local refs here to avoid double-marking).
                foreach ($field['schema'] as $subField) {
                    $sub = $this->extractFromCondition($subField['when'] ?? null);
                    foreach ($sub['root'] as $k) {
                        $refs['root'][] = $k;
                    }
                    foreach ($sub['state'] as $k) {
                        $refs['state'][] = $k;
                    }
                    foreach (($subField['badge_when'] ?? []) as $cand) {
                        $cs = $this->extractFromCondition($cand['condition'] ?? null);
                        foreach ($cs['root'] as $k) {
                            $refs['root'][] = $k;
                        }
                        foreach ($cs['state'] as $k) {
                            $refs['state'][] = $k;
                        }
                    }
                    foreach (($subField['help_when'] ?? []) as $cand) {
                        $cs = $this->extractFromCondition($cand['condition'] ?? null);
                        foreach ($cs['root'] as $k) {
                            $refs['root'][] = $k;
                        }
                        foreach ($cs['state'] as $k) {
                            $refs['state'][] = $k;
                        }
                    }
                }
            }
        }

        // Dedupe each bucket.
        $refs['local'] = array_values(array_unique($refs['local']));
        $refs['root'] = array_values(array_unique($refs['root']));
        $refs['state'] = array_values(array_unique($refs['state']));
        $refs['row'] = array_values(array_unique($refs['row'], SORT_REGULAR));

        return $refs;
    }

    /**
     * Pull every {var: '...'} path out of a condition (recursively for
     * and/or/not) and bucket each by its prefix.
     *
     * @return array{local: array<int, string>, root: array<int, string>, state: array<int, string>, row: array<int, string>}
     */
    private function extractFromCondition(mixed $condition): array
    {
        $refs = ['local' => [], 'root' => [], 'state' => [], 'row' => []];

        if (! is_array($condition) || empty($condition)) {
            return $refs;
        }

        $this->walk($condition, $refs);

        return $refs;
    }

    /**
     * @param  array<string, mixed>  $node
     * @param  array{local: array<int, string>, root: array<int, string>, state: array<int, string>, row: array<int, string>}  $refs
     */
    private function walk(array $node, array &$refs): void
    {
        // {var: 'path'} leaf reference.
        if (array_key_exists('var', $node) && is_string($node['var'])) {
            $this->bucket($node['var'], $refs);

            return;
        }

        // Operator node: recurse into operands.
        foreach ($node as $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    foreach ($value as $sub) {
                        if (is_array($sub)) {
                            $this->walk($sub, $refs);
                        }
                    }
                } else {
                    $this->walk($value, $refs);
                }
            }
        }
    }

    /**
     * @param  array{local: array<int, string>, root: array<int, string>, state: array<int, string>, row: array<int, string>}  $refs
     */
    private function bucket(string $path, array &$refs): void
    {
        // `.` segments: only the leading segment is the field key on the
        // owning model; nested `.field.subkey` accesses still drive that
        // top-level field, so we mark the head segment.
        if (str_starts_with($path, '$root.')) {
            $head = $this->headSegment(substr($path, 6));
            if ($head !== '') {
                $refs['root'][] = $head;
            }
        } elseif (str_starts_with($path, '$state.')) {
            $head = $this->headSegment(substr($path, 7));
            if ($head !== '') {
                $refs['state'][] = $head;
            }
        } elseif (str_starts_with($path, '$row.')) {
            $head = $this->headSegment(substr($path, 5));
            if ($head !== '') {
                $refs['row'][] = $head;
            }
        } elseif (str_starts_with($path, '$')) {
            // Special vars like $state.code — already handled above for
            // known prefixes. Others (e.g. $root.selected_states) don't
            // map to a field in any step, so skip them.
            return;
        } else {
            $head = $this->headSegment($path);
            if ($head !== '') {
                $refs['local'][] = $head;
            }
        }
    }

    private function headSegment(string $path): string
    {
        $parts = explode('.', $path, 2);

        return $parts[0] ?? '';
    }

    /**
     * @param  array<string, array<string, mixed>>  $schema
     * @return array<int, string>
     */
    private function collectRowRefsFromSchema(array $schema): array
    {
        $rowKeys = [];

        foreach ($schema as $subField) {
            $candidates = [];
            if (! empty($subField['when'])) {
                $candidates[] = $subField['when'];
            }
            foreach (($subField['badge_when'] ?? []) as $c) {
                if (! empty($c['condition'])) {
                    $candidates[] = $c['condition'];
                }
            }
            foreach (($subField['help_when'] ?? []) as $c) {
                if (! empty($c['condition'])) {
                    $candidates[] = $c['condition'];
                }
            }

            foreach ($candidates as $condition) {
                $extracted = $this->extractFromCondition($condition);
                foreach ($extracted['row'] as $k) {
                    $rowKeys[] = $k;
                }
                // Bare references inside a repeater schema also mean
                // "sibling field in same row".
                foreach ($extracted['local'] as $k) {
                    if (isset($schema[$k])) {
                        $rowKeys[] = $k;
                    }
                }
            }
        }

        return array_values(array_unique($rowKeys));
    }

    /**
     * Mark a field `drives_conditional` in every step where it appears.
     *
     * @param  array<string, array<string, mixed>>  $steps
     * @return array<string, array<string, mixed>>
     */
    private function markIn(array $steps, string $fieldKey): array
    {
        foreach ($steps as $stepKey => $step) {
            if (isset($step['fields'][$fieldKey])) {
                $steps[$stepKey]['fields'][$fieldKey]['drives_conditional'] = true;
            }
        }

        return $steps;
    }

    /**
     * @param  array{local: array<int, string>, root: array<int, string>, state: array<int, string>, row: array<int, mixed>}  $into
     * @param  array{local: array<int, string>, root: array<int, string>, state: array<int, string>, row: array<int, mixed>}  $from
     */
    private function mergeRefs(array &$into, array $from): void
    {
        foreach ($from['local'] as $k) {
            $into['local'][] = $k;
        }
        foreach ($from['root'] as $k) {
            $into['root'][] = $k;
        }
        foreach ($from['state'] as $k) {
            $into['state'][] = $k;
        }
        foreach ($from['row'] as $k) {
            $into['row'][] = $k;
        }
    }
}

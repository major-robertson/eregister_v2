<?php

namespace App\Domains\Forms\Engine;

/**
 * Display text for a stored answer, driven by its field definition:
 * option values show their labels ("1" → "January" or "Checking",
 * "llc_single" → "LLC (Single-Member)"), checkboxes and booleans show
 * Yes/No, and percents get a "%". Anything else prints as stored, so a
 * text answer of "1" stays "1".
 *
 * Shared by the answer-summary partial (customer review page, admin
 * detail cards) and the admin All Application Data dump so every screen
 * reads answers the same way.
 */
class AnswerFormatter
{
    /**
     * Every field declared in a definition's steps, keyed by field key.
     *
     * @param  array<string, array<string, mixed>>  $steps
     * @return array<string, array<string, mixed>>
     */
    public function fieldsIn(array $steps): array
    {
        $fields = [];

        foreach ($steps as $step) {
            $fields += $step['fields'] ?? [];
        }

        return $fields;
    }

    /**
     * @param  scalar|null  $value  One stored answer (arrays are the caller's job).
     * @param  array<string, mixed>  $field
     */
    public function format(mixed $value, array $field): string
    {
        $type = $field['type'] ?? null;

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($type === 'checkbox' && in_array($value, [1, '1', 'true', 'on', 'yes'], true)) {
            return 'Yes';
        }

        if ($type === 'checkbox' && in_array($value, [0, '0', 'false', 'off', 'no'], true)) {
            return 'No';
        }

        $options = $field['options'] ?? null;

        if ($options === '<<selected_states>>' && is_string($value)) {
            return (string) config('states.'.$value, $value);
        }

        if (is_array($options)) {
            $flat = $this->flattenOptions($options);

            if (array_key_exists((string) $value, $flat)) {
                return (string) $flat[(string) $value];
            }
        }

        $text = trim((string) $value);

        return $type === 'percent' && is_numeric($text) ? "{$text}%" : $text;
    }

    /**
     * @param  array<int|string, mixed>  $options
     * @return array<int|string, mixed>
     */
    private function flattenOptions(array $options): array
    {
        $flat = [];

        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $flat += $option; // optgroup
            } else {
                $flat[$key] = $option;
            }
        }

        return $flat;
    }
}

<?php

namespace App\Domains\Forms\Engine;

/**
 * Resolves the §1.5 applicability rule for matrix / anywhere_states
 * fields: a field declares `applicable_states` as either `'*'` (every
 * selected state) or an explicit list of state codes. The effective
 * set is always the intersection with the application's selected
 * states; an empty intersection hides the field entirely (and skips
 * its validation).
 *
 * Plain core fields without `applicable_states` are unaffected.
 */
class Applicability
{
    /**
     * Effective states for a field: applicable ∩ selected, preserving
     * the selected-states order (which is the order the user picked
     * and the order every other state-keyed UI uses).
     *
     * @param  array<string, mixed>  $field
     * @param  array<int, string>  $selectedStates
     * @return array<int, string>
     */
    public static function statesFor(array $field, array $selectedStates): array
    {
        $applicable = $field['applicable_states'] ?? '*';

        if ($applicable === '*') {
            return array_values($selectedStates);
        }

        return array_values(array_intersect($selectedStates, (array) $applicable));
    }

    /**
     * Whether the field should render at all. Fields that don't declare
     * `applicable_states` are always applicable; declared fields require
     * a non-empty intersection with the selected states.
     *
     * @param  array<string, mixed>  $field
     * @param  array<int, string>  $selectedStates
     */
    public static function isApplicable(array $field, array $selectedStates): bool
    {
        if (! array_key_exists('applicable_states', $field)) {
            return true;
        }

        return self::statesFor($field, $selectedStates) !== [];
    }
}

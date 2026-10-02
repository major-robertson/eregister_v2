<?php

namespace App\Domains\Lien\Waivers;

/**
 * The FAQ on a lien waiver state page, built only from that state's
 * WaiverStateRegistry rules: the forms it uses, its notary and witness
 * rules, how its kinds split conditional/unconditional and progress/final,
 * and its advance-waiver rule. A question the data cannot answer is left
 * out rather than filled with generic copy, so no state gains a fact its
 * data file does not carry.
 */
final class WaiverStateFaq
{
    private const NUMBERS = [1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four'];

    /**
     * @param  array<string, mixed>  $rules  WaiverStateRegistry::for($code)
     * @param  array<string, array{title: string}>  $blankForms  WaiverBlankForms::forState($code)
     * @return list<array{q: string, a: string}>
     */
    public static function for(array $rules, array $blankForms): array
    {
        $name = $rules['state_name'];

        return array_values(array_filter([
            self::forms($name, $rules, $blankForms),
            self::notarization($name, $rules),
            self::conditional($name, $rules),
            self::progress($name, $rules),
            self::advance($name, $rules),
        ]));
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, array{title: string}>  $blankForms
     * @return array{q: string, a: string}
     */
    private static function forms(string $name, array $rules, array $blankForms): array
    {
        $q = "Which lien waiver forms does {$name} use?";
        $statute = $rules['statute'] ?? null;
        $titles = array_values(array_unique(array_column($blankForms, 'title')));

        if ($titles !== [] && $statute) {
            $count = count($titles);
            $titled = self::join(array_map(fn ($t) => 'the '.$t, $titles));

            // Florida's forms are a safe harbor, and its conditional kinds add
            // the lienor-elected condition, so they are not counted as statutory.
            if (($rules['family'] ?? null) === 'safe_harbor') {
                $lead = "{$name} sets out safe-harbor lien waiver forms in {$statute}.";
                $list = "We generate {$titled}.";
            } else {
                $lead = "{$name} prescribes ".(self::NUMBERS[$count] ?? $count).' statutory lien waiver '.($count === 1 ? 'form' : 'forms')." in {$statute}.";
                $list = ($count === 1 ? 'It is ' : 'They are ').$titled.'.';
            }
            $others = self::hasGenericKinds($rules)
                ? " Other {$name} waivers have no prescribed form and use our general-purpose forms."
                : '';

            return ['q' => $q, 'a' => "{$lead} {$list}{$others}"];
        }

        $lead = ($rules['family'] ?? null) === 'special'
            ? "{$name} prescribes no statutory lien waiver form for ordinary payments"
            : "{$name} does not prescribe a statutory lien waiver form";

        // State-mandated statements the house forms carry (Colorado's § 38-22-119(2)).
        $clauses = array_map(fn (string $clause) => ' Every '.$name.' form we generate includes this required statement: "'.$clause.'"', $rules['extra_clauses'] ?? []);

        return ['q' => $q, 'a' => "{$lead}, so the format is up to the parties. Our generator uses four general-purpose forms: conditional and unconditional waivers for progress and final payments.".implode('', $clauses)];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{q: string, a: string}|null
     */
    private static function notarization(string $name, array $rules): ?array
    {
        if (! array_key_exists('notarization_required', $rules)) {
            return null;
        }

        $q = "Do {$name} lien waivers need to be notarized?";
        $reason = $rules['esign_disabled_reason'] ?? null;

        if ($rules['notarization_required']) {
            return ['q' => $q, 'a' => trim("Yes. {$name} lien waivers must be signed before a notary. {$reason}")];
        }

        if ($rules['witness_required'] ?? false) {
            return ['q' => $q, 'a' => trim("No notary is needed, but {$name} lien waivers must be signed before a witness. {$reason}")];
        }

        $notes = array_filter($rules['ui_notes'] ?? [], fn (string $note) => preg_match('/notar/i', $note));

        return ['q' => $q, 'a' => trim("No. {$name} does not require lien waivers to be notarized. ".implode(' ', $notes))];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{q: string, a: string}|null
     */
    private static function conditional(string $name, array $rules): ?array
    {
        $q = "What is the difference between a conditional and an unconditional lien waiver in {$name}?";
        $kinds = $rules['kinds'] ?? [];
        $enabled = fn (string $kind) => (bool) ($kinds[$kind]['enabled'] ?? false);

        $hasConditional = $enabled('conditional_progress') || $enabled('conditional_final');
        $hasUnconditional = $enabled('unconditional_progress') || $enabled('unconditional_final');

        if ($hasConditional && $hasUnconditional) {
            return ['q' => $q, 'a' => 'A conditional waiver takes effect only once the payment it describes actually arrives; if the check bounces or never comes, your lien rights survive. An unconditional waiver gives up lien rights as soon as it is signed, whether or not you are ever paid, so sign one only after the money is in hand.'];
        }

        // A state that lacks one side explains why in the disabled kind's reason.
        $missing = $hasConditional ? 'unconditional_progress' : 'conditional_progress';
        $reason = $kinds[$missing]['disabled_reason'] ?? null;

        return $reason ? ['q' => $q, 'a' => $reason] : null;
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{q: string, a: string}
     */
    private static function progress(string $name, array $rules): array
    {
        $answer = 'A progress waiver covers a single progress payment and waives lien rights only for work through a stated date, leaving rights for later work intact. A final waiver goes with the last payment and waives all remaining lien rights on the project.';

        // Name the state's own forms when every one of them is statutory.
        $kinds = array_filter($rules['kinds'] ?? [], fn (array $entry) => $entry['enabled'] ?? false);
        $statutory = $kinds !== [] && ! self::hasGenericKinds($rules);

        if ($statutory) {
            $titles = fn (bool $final) => array_values(array_unique(array_map(
                fn (array $entry) => $entry['title'],
                array_filter($kinds, fn (array $entry, string $kind) => str_ends_with($kind, '_final') === $final, ARRAY_FILTER_USE_BOTH),
            )));
            $progressTitles = $titles(false);
            $finalTitles = $titles(true);

            if ($progressTitles !== [] && $progressTitles === $finalTitles && count($progressTitles) === 1) {
                $answer .= " {$name} uses one form, the {$progressTitles[0]}, for both.";
            } elseif ($progressTitles !== [] && $finalTitles !== []) {
                $answer .= " In {$name}, progress payments use the ".self::join($progressTitles, 'or the')
                    .' and final payments use the '.self::join($finalTitles, 'or the').'.';
            }
        }

        return ['q' => "What is the difference between a progress and a final lien waiver in {$name}?", 'a' => $answer];
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array{q: string, a: string}|null
     */
    private static function advance(string $name, array $rules): ?array
    {
        $note = $rules['advance_waiver_note'] ?? null;

        return $note ? ['q' => "Is a lien waiver signed in advance enforceable in {$name}?", 'a' => $note] : null;
    }

    /** @param  array<string, mixed>  $rules */
    private static function hasGenericKinds(array $rules): bool
    {
        foreach ($rules['kinds'] ?? [] as $entry) {
            if (($entry['enabled'] ?? false) && str_contains($entry['template'] ?? '', '.generic-')) {
                return true;
            }
        }

        return false;
    }

    /** @param  list<string>  $items */
    private static function join(array $items, string $last = 'and'): string
    {
        if (count($items) <= 1) {
            return $items[0] ?? '';
        }

        $final = array_pop($items);

        return implode(', ', $items).(count($items) > 1 ? ',' : '')." {$last} {$final}";
    }
}

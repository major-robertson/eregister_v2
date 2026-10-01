<?php

/*
 * Guards on the researched lien seed data that the "/liens/{state}" pages
 * read. The JSON files are checked directly, so a bad merge fails here before
 * any page renders.
 */

// States whose zero-offset deadline rows still lack a researched display
// text (the page drops those rows). Empty: all 18 were resolved in EREG-9.
const LIEN_ZERO_OFFSET_KNOWN_UNRESOLVED = [];

function lienSeedRows(string $file): array
{
    return json_decode(file_get_contents(database_path("seeders/data/{$file}.json")), true, 512, JSON_THROW_ON_ERROR);
}

it('gives every state reviewed public notes of 100 to 320 words', function () {
    $rows = lienSeedRows('lien_state_rules');
    expect($rows)->toHaveCount(50);

    foreach ($rows as $row) {
        $notes = (string) ($row['public_notes'] ?? '');
        $words = count(preg_split('/\s+/', trim($notes), -1, PREG_SPLIT_NO_EMPTY));

        expect($words)->toBeGreaterThanOrEqual(100, "{$row['state']}: {$words} words")
            ->toBeLessThanOrEqual(320, "{$row['state']}: {$words} words")
            ->and(preg_match('/this sheet|modeled|proxy|placeholder|encoded|\b[a-z]+_[a-z_]+\b/', $notes, $match))
            ->toBe(0, "{$row['state']}: \"".($match[0] ?? '').'"');
    }
});

it('gives every zero-offset deadline row a display text or lists its state as unresolved', function () {
    $missing = [];
    foreach (lienSeedRows('lien_deadline_rules') as $row) {
        if ($row['calc_method'] !== 'days_after_date' || (int) $row['offset_days'] !== 0) {
            continue;
        }
        $conditions = is_string($row['conditions_json']) ? json_decode($row['conditions_json'], true) : (array) $row['conditions_json'];
        if (empty($conditions['display']) && ! in_array($row['state'], LIEN_ZERO_OFFSET_KNOWN_UNRESOLVED, true)) {
            $missing[] = "{$row['state']} {$row['document_type_slug']}/{$row['claimant_type']}/{$row['trigger_event']}";
        }
    }

    expect($missing)->toBeEmpty("Zero-offset rows without a display:\n".implode("\n", $missing));
});

it('stores enforcement only as a value the page can state', function () {
    foreach (lienSeedRows('lien_state_rules') as $row) {
        $months = $row['enforcement_deadline_months'];
        $days = (int) $row['enforcement_deadline_days'];
        if (! $months && ! $days) {
            continue;
        }

        expect($row['enforcement_deadline_trigger'])
            ->toBeIn(['lien_recorded_date', 'last_furnish_date', 'completion_date', 'contract_date', 'last_day_to_file', 'notice_of_intention_filed'], $row['state'])
            ->and($row['enforcement_calc_method'])->toBe($months ? 'months_after_date' : 'days_after_date', $row['state']);
    }
});

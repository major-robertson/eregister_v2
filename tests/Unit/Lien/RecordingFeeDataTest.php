<?php

uses(Tests\TestCase::class);

/*
 * recording_fee_cents (EREG-143) in the lien data files: null (unknown), a
 * positive number of cents for the lien and the release, or positive cents
 * keyed by those two documents. Every county file says which, so a fee left
 * out is a decision, not an oversight.
 */

/**
 * @param  mixed  $fee
 * @return list<string>
 */
function recordingFeeProblems(string $where, $fee): array
{
    if ($fee === null) {
        return [];
    }

    if (is_int($fee)) {
        return $fee > 0 ? [] : ["{$where}: recording_fee_cents must be positive"];
    }

    if (! is_array($fee) || $fee === []) {
        return ["{$where}: recording_fee_cents must be null, cents, or cents keyed by document"];
    }

    $problems = [];

    foreach ($fee as $kind => $cents) {
        if (! in_array($kind, ['mechanics_lien', 'lien_release'], true)) {
            $problems[] = "{$where}: {$kind} is not a recorded document";
        }

        if (! is_int($cents) || $cents <= 0) {
            $problems[] = "{$where}: {$kind} must be positive cents";
        }
    }

    return $problems;
}

it('gives every county file a valid recording_fee_cents', function () {
    $files = glob(database_path('data/lien_counties/*/*.php'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $data = require $file;
        $where = basename(dirname($file)).'/'.basename($file);

        expect($data)->toHaveKey('recording_fee_cents', message: "{$where} has no recording_fee_cents");
        expect(recordingFeeProblems($where, $data['recording_fee_cents']))->toBe([]);
    }
});

it('keeps every state file\'s recording_fee_cents valid where it has one', function () {
    foreach (glob(database_path('data/lien_documents/*.php')) as $file) {
        $data = require $file;

        expect(recordingFeeProblems(basename($file), $data['recording_fee_cents'] ?? null))->toBe([]);
    }
});

it('reports a fee that is not cents or not for a recorded document', function () {
    expect(recordingFeeProblems('x', 0))->toBe(['x: recording_fee_cents must be positive'])
        ->and(recordingFeeProblems('x', '43.00'))->toBe(['x: recording_fee_cents must be null, cents, or cents keyed by document'])
        ->and(recordingFeeProblems('x', ['noi' => 1000]))->toBe(['x: noi is not a recorded document'])
        ->and(recordingFeeProblems('x', ['mechanics_lien' => 43.5]))->toBe(['x: mechanics_lien must be positive cents'])
        ->and(recordingFeeProblems('x', ['mechanics_lien' => 4300, 'lien_release' => 1000]))->toBe([]);
});

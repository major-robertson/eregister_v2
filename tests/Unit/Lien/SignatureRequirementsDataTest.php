<?php

use App\Domains\Lien\Documents\LienDocumentRegistry;

uses(Tests\TestCase::class);

/*
 * The signature_requirements block (EREG-142) in every lien data file: the
 * same five keys everywhere, each flag true, false or null (unknown), and a
 * source note wherever a flag is known.
 */

/**
 * @param  mixed  $block
 */
function signatureBlockProblems(string $where, $block): array
{
    if (! is_array($block)) {
        return ["{$where}: signature_requirements is not an array"];
    }

    $problems = [];
    $flags = array_diff(array_keys(LienDocumentRegistry::SIGNATURE_REQUIREMENTS), ['source']);

    foreach (array_diff(array_keys($block), array_keys(LienDocumentRegistry::SIGNATURE_REQUIREMENTS)) as $unknown) {
        $problems[] = "{$where}: unknown key {$unknown}";
    }

    foreach ($flags as $flag) {
        if (array_key_exists($flag, $block) && ! is_bool($block[$flag]) && $block[$flag] !== null) {
            $problems[] = "{$where}: {$flag} must be true, false or null";
        }
    }

    $source = $block['source'] ?? null;

    if ($source !== null && (! is_string($source) || trim($source) === '')) {
        $problems[] = "{$where}: source must be a non-empty string or null";
    }

    $known = array_filter($flags, fn (string $flag): bool => ($block[$flag] ?? null) !== null);

    if ($known !== [] && $source === null) {
        $problems[] = "{$where}: ".implode(', ', $known).' set without a source';
    }

    return $problems;
}

it('gives every state file the full signature_requirements block', function () {
    $files = glob(database_path('data/lien_documents/*.php'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $data = require $file;
        $where = basename($file);

        expect($data)->toHaveKey('signature_requirements', message: "{$where} has no signature_requirements");
        expect(array_keys($data['signature_requirements']))->toBe(array_keys(LienDocumentRegistry::SIGNATURE_REQUIREMENTS), "{$where} keys");
        expect(signatureBlockProblems($where, $data['signature_requirements']))->toBe([]);

        // A kind may carry its own (partial) block.
        foreach ($data['kinds'] ?? [] as $kind => $entry) {
            if (array_key_exists('signature_requirements', $entry)) {
                expect(signatureBlockProblems("{$where} {$kind}", $entry['signature_requirements']))->toBe([]);
            }
        }
    }
});

it('gives every county file the full signature_requirements block', function () {
    $files = glob(database_path('data/lien_counties/*/*.php'));

    expect($files)->not->toBeEmpty();

    foreach ($files as $file) {
        $data = require $file;
        $where = basename(dirname($file)).'/'.basename($file);

        expect($data)->toHaveKey('signature_requirements', message: "{$where} has no signature_requirements");
        expect(array_keys($data['signature_requirements']))->toBe(array_keys(LienDocumentRegistry::SIGNATURE_REQUIREMENTS), "{$where} keys");
        expect(signatureBlockProblems($where, $data['signature_requirements']))->toBe([]);
    }
});

it('reports a flag without a source and a flag that is not a boolean', function () {
    expect(signatureBlockProblems('x', ['notary_required' => true, 'source' => null]))
        ->toBe(['x: notary_required set without a source'])
        ->and(signatureBlockProblems('x', ['notary_required' => 'yes', 'source' => 'A note.']))
        ->toBe(['x: notary_required must be true, false or null'])
        ->and(signatureBlockProblems('x', ['wet_ink' => true]))
        ->toBe(['x: unknown key wet_ink']);
});

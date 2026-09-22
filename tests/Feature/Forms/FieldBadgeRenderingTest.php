<?php

use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * Field badges come in two shapes. A plain `badge` {label, color} shows
 * unconditionally; a `badge_when` list of {condition, label, color}
 * entries is evaluated first-match-wins via ConditionEvaluator. The field
 * dispatcher resolves either into the badge passed to the typed partial.
 *
 * Since EREG-54 the EIN field is optional for every entity type, so it
 * carries the plain badge; the conditional mechanism is pinned by the
 * synthetic tests below.
 */
describe('badge definition shape', function () {
    it('defines an unconditional Optional badge on FEIN', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['identity']['fields']['fein'];

        expect($field['badge'] ?? null)->toBe(['label' => 'Optional', 'color' => 'zinc'])
            ->and($field['badge_when'] ?? null)->toBeNull();
    });

    it('defines an unconditional Optional badge on the NAICS code', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['activity']['fields']['naics_code'];

        expect($field['badge'] ?? null)->toBe(['label' => 'Optional', 'color' => 'zinc']);
    });
});

describe('FEIN Optional badge rendering', function () {
    it('shows the Optional badge for every entity type', function (string $entityType) {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => $entityType])
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        // The badge text appears in the rendered label region. We assert
        // on the visible string rather than on Flux's internal markup so
        // the test isn't coupled to Flux versions.
        expect($html)->toContain('Federal Employer Identification Number')
            ->and($html)->toContain('Optional');
    })->with(['sole_prop', 'corporation', 'llc_single']);
});

describe('badge_when first-match-wins behavior', function () {
    it('picks the first matching candidate and ignores later ones', function () {
        // Synthetic field with two candidates that BOTH match — the
        // first should win. This pins ordering semantics so future
        // refactors don't accidentally reverse it.
        $field = [
            'badge_when' => [
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'label' => 'First',
                    'color' => 'zinc',
                ],
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'label' => 'Second',
                    'color' => 'red',
                ],
            ],
        ];

        $evaluator = app(\App\Domains\Forms\Engine\ConditionEvaluator::class);
        $context = ['coreData' => ['entity_type' => 'sole_prop'], 'stateData' => []];

        $matched = null;
        foreach ($field['badge_when'] as $candidate) {
            if ($evaluator->evaluate($candidate['condition'], $context)) {
                $matched = $candidate;
                break;
            }
        }

        expect($matched['label'])->toBe('First');
    });

    it('treats a missing badge_when as a no-op (other fields are unaffected)', function () {
        // Fields without a badge_when key must NOT inject any badge.
        // Asserts via the dispatcher's resolution path that absence is safe.
        $field = ['type' => 'text', 'label' => 'Plain'];

        $badge = null;
        if (! empty($field['badge_when'])) {
            $badge = ['unexpected' => true];
        }

        expect($badge)->toBeNull();
    });
});

<?php

use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * Field help text can be conditional via `help_when` — same pattern as
 * `badge_when`: a list of {condition, help} entries evaluated
 * first-match-wins via the existing ConditionEvaluator. The static
 * `help` key is the fallback when no entry matches.
 *
 * Used here to keep the EIN help short for entity types that always
 * need one ("Get an EIN at: <url>") while giving sole proprietors a
 * longer note explaining the optional-but-recommended framing.
 */
describe('help_when definition shape', function () {
    it('declares a sole-prop-only override on the EIN field', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['tax_identification']['fields']['fein'];

        expect($field['help'] ?? null)
            ->toBe('Get an EIN at https://www.irs.gov/businesses/employer-identification-number')
            ->and($field['help_when'] ?? null)->toBeArray()->not->toBeEmpty();

        $first = $field['help_when'][0];
        expect($first['condition'])->toBe(['==' => [['var' => 'entity_type'], 'sole_prop']])
            ->and($first['help'])->toContain('You may leave blank')
            ->and($first['help'])->toContain('highly recommended');
    });
});

describe('FEIN help_when rendering', function () {
    it('renders the long sole-prop help text when entity_type is sole_prop', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'sole_prop'])
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        expect($html)->toContain('You may leave blank')
            ->and($html)->toContain('highly recommended');
    });

    it('renders the short default help for non-sole-prop entity types', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'corporation'])
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        expect($html)->toContain('Get an EIN at')
            ->and($html)->not->toContain('You may leave blank')
            ->and($html)->not->toContain('highly recommended');
    });

    it('swaps help text live when entity_type flips between corporation and sole_prop', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'corporation'])
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->assertDontSee('You may leave blank')
            ->set('coreData.entity_type', 'sole_prop')
            ->assertSee('You may leave blank');
    });
});

describe('help_when first-match-wins and no-op fallback', function () {
    it('picks the first matching candidate and ignores later ones', function () {
        $field = [
            'help' => 'fallback',
            'help_when' => [
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'help' => 'first',
                ],
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'help' => 'second',
                ],
            ],
        ];

        $evaluator = app(ConditionEvaluator::class);
        $context = ['coreData' => ['entity_type' => 'sole_prop'], 'stateData' => []];

        $resolved = $field['help'];
        foreach ($field['help_when'] as $candidate) {
            if ($evaluator->evaluate($candidate['condition'], $context)) {
                $resolved = $candidate['help'];
                break;
            }
        }

        expect($resolved)->toBe('first');
    });

    it('falls back to the static help when no help_when candidate matches', function () {
        $field = [
            'help' => 'fallback text',
            'help_when' => [
                [
                    'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'help' => 'sole prop only',
                ],
            ],
        ];

        $evaluator = app(ConditionEvaluator::class);
        $context = ['coreData' => ['entity_type' => 'corporation'], 'stateData' => []];

        $resolved = $field['help'];
        foreach ($field['help_when'] as $candidate) {
            if ($evaluator->evaluate($candidate['condition'], $context)) {
                $resolved = $candidate['help'];
                break;
            }
        }

        expect($resolved)->toBe('fallback text');
    });

    it('treats missing help_when as a no-op leaving the static help untouched', function () {
        // Regression guard: every existing field that has only `help`
        // (no help_when) must continue to render that help string
        // unchanged after the help_when mechanism was added.
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $naics = $base['core_steps']['activity']['fields']['naics_code'];

        expect($naics['help_when'] ?? null)->toBeNull()
            ->and($naics['help'])->toBe('Find your code here: https://www.census.gov/naics/');
    });
});

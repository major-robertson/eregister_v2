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
 * Since EREG-54 the EIN and NAICS fields are optional for everyone and
 * carry one static help line each; the conditional mechanism is pinned
 * by the synthetic tests below.
 */
describe('optional-field help text', function () {
    it('tells every applicant they may leave the EIN blank', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['identity']['fields']['fein'];

        expect($field['help'] ?? null)->toContain('Leave this blank')
            ->and($field['help_when'] ?? null)->toBeNull();
    });

    it('renders the EIN help for corporations and sole proprietors alike', function (string $entityType) {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => $entityType])
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        expect($html)->toContain('Leave this blank and keep going');
    })->with(['sole_prop', 'corporation']);
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
            ->and($naics['help'])->toContain('Leave this blank')
            ->and($naics['rules'])->toBe(['nullable', 'digits:6']);
    });
});

<?php

use App\Domains\Forms\Engine\Applicability;
use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\VisibleFieldResolver;

beforeEach(function () {
    $this->resolver = new VisibleFieldResolver(new ConditionEvaluator);
});

describe('Applicability helper', function () {
    it('returns all selected states for "*"', function () {
        $field = ['applicable_states' => '*'];

        expect(Applicability::statesFor($field, ['AL', 'AZ', 'CO']))->toBe(['AL', 'AZ', 'CO']);
    });

    it('intersects explicit lists with selected states, preserving selection order', function () {
        $field = ['applicable_states' => ['FL', 'MD', 'NJ', 'WA']];

        expect(Applicability::statesFor($field, ['CA', 'FL', 'NY']))->toBe(['FL']);
    });

    it('treats fields without applicable_states as always applicable', function () {
        expect(Applicability::isApplicable(['type' => 'text'], []))->toBeTrue();
    });

    it('marks fields with an empty intersection inapplicable', function () {
        $field = ['applicable_states' => ['IL']];

        expect(Applicability::isApplicable($field, ['CA', 'NY']))->toBeFalse();
    });
});

describe('VisibleFieldResolver applicability', function () {
    $step = [
        'fields' => [
            'legal_name' => ['type' => 'text', 'rules' => ['required']],
            'matrix_sales_tax_start_date' => [
                'type' => 'matrix',
                'cell_rules' => ['required', 'date'],
                'applicable_states' => '*',
            ],
            'applies_internet_or_mail_order' => [
                'type' => 'anywhere_states',
                'applicable_states' => ['CA', 'NY', 'IL', 'TX', 'FL'],
            ],
            'applies_cannabis' => [
                'type' => 'anywhere_states',
                'applicable_states' => ['IL'],
            ],
            'applies_fireworks' => [
                'type' => 'anywhere_states',
                'applicable_states' => ['GA', 'TX'],
            ],
        ],
    ];

    it('hides state-specific activity questions for generic-only state selections', function () use ($step) {
        $visible = $this->resolver->resolve($step, [
            'coreData' => [],
            'stateData' => [],
            'selectedStates' => ['AL', 'AZ', 'CO'],
        ]);

        expect($visible)->toHaveKeys(['legal_name', 'matrix_sales_tax_start_date'])
            ->and($visible)->not->toHaveKey('applies_internet_or_mail_order')
            ->and($visible)->not->toHaveKey('applies_cannabis')
            ->and($visible)->not->toHaveKey('applies_fireworks');
    });

    it('shows an activity question when any applicable state is selected', function () use ($step) {
        $visible = $this->resolver->resolve($step, [
            'coreData' => [],
            'stateData' => [],
            'selectedStates' => ['CA', 'NY'],
        ]);

        expect($visible)->toHaveKey('applies_internet_or_mail_order')
            ->and($visible)->not->toHaveKey('applies_cannabis');
    });

    it('shows IL-only cannabis question when IL is selected', function () use ($step) {
        $visible = $this->resolver->resolve($step, [
            'coreData' => [],
            'stateData' => [],
            'selectedStates' => ['IL'],
        ]);

        expect($visible)->toHaveKey('applies_cannabis');
    });

    it('never turns dedupe into a universal questionnaire', function () use ($step) {
        // Every applies_* field hidden; only the plain field and the
        // universal matrix remain.
        $visible = $this->resolver->resolve($step, [
            'coreData' => [],
            'stateData' => [],
            'selectedStates' => ['VT'],
        ]);

        $appliesKeys = array_filter(array_keys($visible), fn ($k) => str_starts_with($k, 'applies_'));

        expect($appliesKeys)->toBe([]);
    });
});

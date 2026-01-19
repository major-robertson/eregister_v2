<?php

use App\Domains\Forms\Engine\ConditionEvaluator;

describe('ConditionEvaluator', function () {
    it('evaluates simple equality conditions', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['==' => [['var' => 'entity_type'], 'llc']];
        $context = [
            'coreData' => ['entity_type' => 'llc'],
            'stateData' => [],
            'rowData' => [],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $context['coreData']['entity_type'] = 'corp';
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('evaluates conditions with $root prefix', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['==' => [['var' => '$root.entity_type'], 'llc']];
        $context = [
            'coreData' => ['entity_type' => 'llc'],
            'stateData' => ['entity_type' => 'corp'],
            'rowData' => [],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();
    });

    it('evaluates conditions with $state prefix', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['==' => [['var' => '$state.local_field'], 'value']];
        $context = [
            'coreData' => [],
            'stateData' => ['local_field' => 'value'],
            'rowData' => [],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();
    });

    it('evaluates conditions with $state.code', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['==' => [['var' => '$state.code'], 'CA']];
        $context = [
            'coreData' => [],
            'stateData' => [],
            'rowData' => [],
            'stateCode' => 'CA',
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $context['stateCode'] = 'TX';
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('evaluates in conditions with $root.selected_states', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['in' => ['CA', ['var' => '$root.selected_states']]];
        $context = [
            'coreData' => [],
            'stateData' => [],
            'rowData' => [],
            'selectedStates' => ['CA', 'TX', 'NY'],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $condition = ['in' => ['FL', ['var' => '$root.selected_states']]];
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('evaluates and conditions', function () {
        $evaluator = new ConditionEvaluator;

        $condition = [
            'and' => [
                ['==' => [['var' => 'field1'], 'a']],
                ['==' => [['var' => 'field2'], 'b']],
            ],
        ];

        $context = [
            'coreData' => ['field1' => 'a', 'field2' => 'b'],
            'stateData' => [],
            'rowData' => [],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $context['coreData']['field2'] = 'c';
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('evaluates or conditions', function () {
        $evaluator = new ConditionEvaluator;

        $condition = [
            'or' => [
                ['==' => [['var' => 'field1'], 'a']],
                ['==' => [['var' => 'field2'], 'b']],
            ],
        ];

        $context = [
            'coreData' => ['field1' => 'a', 'field2' => 'c'],
            'stateData' => [],
            'rowData' => [],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $context['coreData']['field1'] = 'x';
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('evaluates row context in repeater', function () {
        $evaluator = new ConditionEvaluator;

        $condition = ['==' => [['var' => '$row.is_manager'], true]];
        $context = [
            'coreData' => [],
            'stateData' => [],
            'rowData' => ['is_manager' => true],
        ];

        expect($evaluator->evaluate($condition, $context))->toBeTrue();

        $context['rowData']['is_manager'] = false;
        expect($evaluator->evaluate($condition, $context))->toBeFalse();
    });

    it('returns true for empty conditions', function () {
        $evaluator = new ConditionEvaluator;

        expect($evaluator->evaluate([], []))->toBeTrue();
    });
});

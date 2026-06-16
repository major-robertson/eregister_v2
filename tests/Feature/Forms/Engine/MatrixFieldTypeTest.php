<?php

use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\RulesBuilder;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\Engine\VisibleFieldResolver;

beforeEach(function () {
    $this->builder = new RulesBuilder(new VisibleFieldResolver(new ConditionEvaluator));
});

function matrixStep(array $overrides = []): array
{
    return [
        'fields' => [
            'matrix_employee_count' => array_merge([
                'type' => 'matrix',
                'label' => 'Number of {state_name} employees',
                'cell_type' => 'text',
                'cell_rules' => ['required', 'integer', 'min:0'],
                'applicable_states' => ['FL', 'MD', 'NJ', 'WA'],
            ], $overrides),
        ],
    ];
}

describe('matrix field rules', function () {
    it('emits one prefixed rule per applicable∩selected state for Livewire', function () {
        $built = $this->builder->buildForLivewire(
            matrixStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['CA', 'FL', 'NY', 'WA'],
        );

        expect($built['rules'])->toHaveKey('coreData.matrix_employee_count.FL')
            ->and($built['rules'])->toHaveKey('coreData.matrix_employee_count.WA')
            ->and($built['rules'])->not->toHaveKey('coreData.matrix_employee_count.CA')
            ->and($built['rules'])->not->toHaveKey('coreData.matrix_employee_count.NY')
            ->and($built['rules']['coreData.matrix_employee_count.FL'])->toBe(['required', 'integer', 'min:0']);
    });

    it('substitutes the per-row state name into validation attributes', function () {
        $built = $this->builder->buildForLivewire(
            matrixStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['FL', 'MD'],
        );

        expect($built['attributes']['coreData.matrix_employee_count.FL'])
            ->toBe('Number of Florida employees')
            ->and($built['attributes']['coreData.matrix_employee_count.MD'])
            ->toBe('Number of Maryland employees');
    });

    it('emits unprefixed rules for the final-submit array context', function () {
        $built = $this->builder->buildForArray(
            matrixStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            selectedStates: ['FL'],
        );

        expect($built['rules'])->toHaveKey('matrix_employee_count.FL')
            ->and($built['rules'])->not->toHaveKey('matrix_employee_count.MD');
    });

    it('treats applicable_states "*" as every selected state', function () {
        $built = $this->builder->buildForLivewire(
            matrixStep(['applicable_states' => '*']),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['AL', 'AZ'],
        );

        expect($built['rules'])->toHaveKey('coreData.matrix_employee_count.AL')
            ->and($built['rules'])->toHaveKey('coreData.matrix_employee_count.AZ');
    });

    it('emits no rules when no selected state is applicable', function () {
        $built = $this->builder->buildForLivewire(
            matrixStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['AL', 'AZ', 'CO'],
        );

        expect($built['rules'])->toBe([]);
    });
});

describe('matrix sensitive cells', function () {
    it('encrypts every cell of a sensitive matrix field', function () {
        $protector = app(SensitiveDataProtector::class);

        $definition = [
            'core_steps' => [
                'step' => [
                    'fields' => [
                        'matrix_secret' => [
                            'type' => 'matrix',
                            'sensitive' => true,
                            'cell_rules' => ['required'],
                        ],
                    ],
                ],
            ],
        ];

        $data = ['matrix_secret' => ['CA' => 'top-secret', 'TX' => 'also-secret']];

        $encrypted = $protector->encryptCoreData($data, $definition);

        expect($encrypted['matrix_secret']['CA'])->not->toBe('top-secret')
            ->and($encrypted['matrix_secret']['TX'])->not->toBe('also-secret');

        $decrypted = $protector->decryptCoreData($encrypted, $definition);

        expect($decrypted['matrix_secret']['CA'])->toBe('top-secret')
            ->and($decrypted['matrix_secret']['TX'])->toBe('also-secret');
    });
});

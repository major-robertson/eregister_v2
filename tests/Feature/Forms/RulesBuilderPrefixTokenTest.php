<?php

use App\Domains\Forms\Engine\RulesBuilder;
use App\Domains\Forms\Engine\VisibleFieldResolver;

beforeEach(function () {
    $this->builder = new RulesBuilder(new VisibleFieldResolver(new \App\Domains\Forms\Engine\ConditionEvaluator));
});

/**
 * The {prefix} token in field rule strings exists so a single field
 * definition can target sibling fields under both validation contexts:
 *   - Per-step Livewire validation, where rules are prefixed with
 *     `coreData.` / `stateData.` because the validator sees the full
 *     component property tree.
 *   - Final submit validation, where each step is validated against
 *     the raw $coreData / $stateData array directly (no prefix).
 *
 * Without this, cross-field validators like `required_unless` would
 * have to be written twice — once with prefix and once without.
 */
describe('RulesBuilder {prefix} token', function () {
    $step = [
        'fields' => [
            'entity_type' => [
                'type' => 'select',
                'rules' => ['required'],
                'options' => ['sole_prop' => 'Sole Prop', 'corporation' => 'Corporation'],
            ],
            'fein' => [
                'type' => 'text',
                'rules' => ['nullable', 'required_unless:{prefix}entity_type,sole_prop'],
            ],
        ],
    ];

    it('rewrites {prefix} to "coreData." for the Livewire validation context', function () use ($step) {
        $built = $this->builder->buildForLivewire(
            $step,
            coreData: ['entity_type' => 'corporation'],
            stateData: [],
            stateCode: null,
            phase: 'core',
        );

        expect($built['rules']['coreData.fein'])
            ->toContain('required_unless:coreData.entity_type,sole_prop')
            ->and($built['rules']['coreData.fein'])
            ->not->toContain('{prefix}');
    });

    it('rewrites {prefix} to "stateData." in the states phase', function () use ($step) {
        $built = $this->builder->buildForLivewire(
            $step,
            coreData: [],
            stateData: ['entity_type' => 'corporation'],
            stateCode: 'CA',
            phase: 'states',
        );

        expect($built['rules']['stateData.fein'])
            ->toContain('required_unless:stateData.entity_type,sole_prop');
    });

    it('strips {prefix} entirely for the unprefixed final-submit context', function () use ($step) {
        $built = $this->builder->buildForArray(
            $step,
            coreData: ['entity_type' => 'corporation'],
            stateData: [],
            stateCode: null,
        );

        expect($built['rules']['fein'])
            ->toContain('required_unless:entity_type,sole_prop')
            ->and($built['rules']['fein'])
            ->not->toContain('{prefix}');
    });

    it('leaves rules without the token untouched', function () {
        $step = [
            'fields' => [
                'naics_code' => [
                    'type' => 'text',
                    'rules' => ['required', 'digits:6'],
                ],
            ],
        ];

        $built = $this->builder->buildForArray($step, [], [], null);

        expect($built['rules']['naics_code'])->toBe(['required', 'digits:6']);
    });
});

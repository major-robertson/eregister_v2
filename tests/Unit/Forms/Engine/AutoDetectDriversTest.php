<?php

use App\Domains\Forms\Engine\DrivesConditionalDetector;

/**
 * Auto-detect coverage tests.
 *
 * The detector is the safety net for `drives_conditional` — without it,
 * an author writing a `when` clause has to remember to also flag the
 * referenced field, otherwise the dependent UI updates only after a step
 * navigation. Each test below pins one resolution path so a regression
 * in the detector surfaces as a specific named case rather than a vague
 * "things stopped updating live."
 */
describe('DrivesConditionalDetector', function () {
    it('marks a same-step bare reference', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                'identity' => [
                    'fields' => [
                        'driver' => ['type' => 'select'],
                        'dependent' => [
                            'type' => 'text',
                            'when' => ['==' => [['var' => 'driver'], '1']],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['identity']['fields']['driver']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('marks a $root reference inside a state step on the corresponding core field', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                'identity' => [
                    'fields' => [
                        'entity_type' => ['type' => 'select'],
                    ],
                ],
            ],
            'state_steps' => [
                'state_details' => [
                    'fields' => [
                        'state_field' => [
                            'type' => 'text',
                            'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['identity']['fields']['entity_type']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('marks a $state reference inside a core step on the corresponding state field', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                'review' => [
                    'fields' => [
                        'core_dependent' => [
                            'type' => 'text',
                            'when' => ['==' => [['var' => '$state.flag'], '1']],
                        ],
                    ],
                ],
            ],
            'state_steps' => [
                'state_details' => [
                    'fields' => [
                        'flag' => ['type' => 'select'],
                    ],
                ],
            ],
        ]);

        expect($merged['state_steps']['state_details']['fields']['flag']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('marks a $row reference inside a repeater on the sibling row field', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                'people' => [
                    'fields' => [
                        'people' => [
                            'type' => 'repeater',
                            'schema' => [
                                'is_owner' => ['type' => 'checkbox'],
                                'ownership_pct' => [
                                    'type' => 'percent',
                                    'when' => ['==' => [['var' => '$row.is_owner'], '1']],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['people']['fields']['people']['schema']['is_owner']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('walks nested and/or/not conditions', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                's' => [
                    'fields' => [
                        'a' => ['type' => 'select'],
                        'b' => ['type' => 'select'],
                        'c' => ['type' => 'select'],
                        'dependent' => [
                            'type' => 'text',
                            'when' => [
                                'and' => [
                                    ['==' => [['var' => 'a'], '1']],
                                    ['or' => [
                                        ['==' => [['var' => 'b'], 'x']],
                                        ['not' => [['==' => [['var' => 'c'], '0']]]],
                                    ]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['s']['fields']['a']['drives_conditional'] ?? false)->toBeTrue()
            ->and($merged['core_steps']['s']['fields']['b']['drives_conditional'] ?? false)->toBeTrue()
            ->and($merged['core_steps']['s']['fields']['c']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('walks badge_when and help_when conditions, not just when', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                's' => [
                    'fields' => [
                        'driver_badge' => ['type' => 'select'],
                        'driver_help' => ['type' => 'select'],
                        'dependent' => [
                            'type' => 'text',
                            'badge_when' => [[
                                'condition' => ['==' => [['var' => 'driver_badge'], 'x']],
                                'label' => 'Optional',
                                'color' => 'zinc',
                            ]],
                            'help_when' => [[
                                'condition' => ['==' => [['var' => 'driver_help'], 'x']],
                                'help' => 'long help',
                            ]],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['s']['fields']['driver_badge']['drives_conditional'] ?? false)->toBeTrue()
            ->and($merged['core_steps']['s']['fields']['driver_help']['drives_conditional'] ?? false)->toBeTrue();
    });

    it('preserves explicit drives_conditional => true (idempotent)', function () {
        $detector = new DrivesConditionalDetector;

        $merged = $detector->detect([
            'core_steps' => [
                's' => [
                    'fields' => [
                        'already_marked' => [
                            'type' => 'select',
                            'drives_conditional' => true,
                        ],
                        'dependent' => [
                            'type' => 'text',
                            'when' => ['==' => [['var' => 'already_marked'], '1']],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['s']['fields']['already_marked']['drives_conditional'])->toBeTrue();
    });

    it('does nothing when a referenced key does not exist (no errors thrown)', function () {
        $detector = new DrivesConditionalDetector;

        // Intentional dangling reference. The detector should silently
        // skip it rather than throw or invent a phantom field — runtime
        // ConditionEvaluator already handles missing data gracefully.
        $merged = $detector->detect([
            'core_steps' => [
                's' => [
                    'fields' => [
                        'orphan' => [
                            'type' => 'text',
                            'when' => ['==' => [['var' => 'never_declared'], '1']],
                        ],
                    ],
                ],
            ],
        ]);

        expect($merged['core_steps']['s']['fields'])->not->toHaveKey('never_declared');
    });
});

<?php

use App\Domains\Forms\Engine\DefinitionMerger;

describe('DefinitionMerger', function () {
    it('merges base and override definitions', function () {
        $merger = new DefinitionMerger;

        $base = [
            'key' => 'test_form',
            'core_steps' => [
                'step1' => [
                    'title' => 'Step 1',
                    'fields' => [
                        'field1' => ['type' => 'text', 'label' => 'Field 1'],
                    ],
                ],
            ],
            'state_steps' => [
                'state_step1' => [
                    'title' => 'State Step 1',
                    'fields' => [
                        'state_field1' => ['type' => 'text', 'label' => 'State Field 1'],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'state_step1' => [
                    'fields' => [
                        'append' => [
                            'state_field2' => ['type' => 'text', 'label' => 'State Field 2'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['core_steps']['step1']['fields'])->toHaveKey('field1');
        expect($result['state_steps']['state_step1']['fields'])->toHaveKey('state_field1');
        expect($result['state_steps']['state_step1']['fields'])->toHaveKey('state_field2');
    });

    it('removes fields when remove operation is specified', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'field1' => ['type' => 'text'],
                        'field2' => ['type' => 'text'],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'remove' => ['field1'],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['fields'])->not->toHaveKey('field1');
        expect($result['state_steps']['step1']['fields'])->toHaveKey('field2');
    });

    it('replaces fields when replace operation is specified', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'field1' => ['type' => 'text', 'label' => 'Original Label'],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'replace' => [
                            'field1' => ['type' => 'select', 'label' => 'New Label'],
                        ],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['fields']['field1']['type'])->toBe('select');
        expect($result['state_steps']['step1']['fields']['field1']['label'])->toBe('New Label');
    });
});

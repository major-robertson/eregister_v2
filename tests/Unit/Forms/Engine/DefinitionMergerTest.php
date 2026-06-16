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

/**
 * Step-level `groups` follow the same append-or-replace semantics as
 * `fields`. These tests pin those semantics so a regression doesn't
 * silently drop a state's per-section grouping back to the orphan-fallback
 * card.
 */
describe('DefinitionMerger groups handling', function () {
    it('replaces base step groups when override provides groups as a list', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        ['title' => 'Original', 'fields' => ['a', 'b']],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        ['title' => 'Replacement', 'fields' => ['c']],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['groups'])->toHaveCount(1)
            ->and($result['state_steps']['step1']['groups'][0]['title'])->toBe('Replacement');
    });

    it('appends step groups when override provides groups.append', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        ['title' => 'Base Section', 'fields' => ['a']],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        'append' => [
                            ['title' => 'Extra Section', 'fields' => ['b']],
                        ],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['groups'])->toHaveCount(2)
            ->and($result['state_steps']['step1']['groups'][0]['title'])->toBe('Base Section')
            ->and($result['state_steps']['step1']['groups'][1]['title'])->toBe('Extra Section');
    });

    it('leaves base step groups intact when override omits groups', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        ['title' => 'Untouched', 'fields' => ['a']],
                    ],
                    'fields' => ['a' => ['type' => 'text']],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'fields' => [
                        'append' => ['b' => ['type' => 'text']],
                    ],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['groups'])->toBe([
            ['title' => 'Untouched', 'fields' => ['a']],
        ]);
    });

    it('treats groups => [append => []] (empty list) as a no-op append', function () {
        $merger = new DefinitionMerger;

        $base = [
            'state_steps' => [
                'step1' => [
                    'groups' => [
                        ['title' => 'A', 'fields' => ['x']],
                    ],
                ],
            ],
        ];

        $override = [
            'state_steps' => [
                'step1' => [
                    'groups' => ['append' => []],
                ],
            ],
        ];

        $result = $merger->merge($base, $override);

        expect($result['state_steps']['step1']['groups'])->toHaveCount(1)
            ->and($result['state_steps']['step1']['groups'][0]['title'])->toBe('A');
    });
});

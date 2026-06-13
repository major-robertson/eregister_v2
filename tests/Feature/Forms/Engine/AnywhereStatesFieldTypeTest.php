<?php

use App\Domains\Forms\Engine\ConditionEvaluator;
use App\Domains\Forms\Engine\RulesBuilder;
use App\Domains\Forms\Engine\VisibleFieldResolver;
use App\Domains\Forms\Livewire\Concerns\WithFormDataIO;

beforeEach(function () {
    $this->builder = new RulesBuilder(new VisibleFieldResolver(new ConditionEvaluator));
});

function anywhereStep(array $overrides = []): array
{
    return [
        'fields' => [
            'applies_alcohol' => array_merge([
                'type' => 'anywhere_states',
                'label' => 'Will you sell alcoholic beverages in any state in this application?',
                'applicable_states' => ['CA', 'IL', 'NY', 'OK', 'TX'],
            ], $overrides),
        ],
    ];
}

/**
 * Harness exposing the normalizeAnywhereStatesFields trait method
 * without booting the full Livewire runner.
 */
function anywhereNormalizationHarness(array $selectedStates, array $coreStepFields): object
{
    $harness = new class
    {
        use WithFormDataIO;

        public object $application;

        public object $business;

        public array $coreData = [];

        public array $stateData = [];

        public string $currentPhase = 'core';

        public ?string $currentStepKey = null;

        public int $currentStateIndex = 0;

        private array $definition = [];

        public function setDefinition(array $definition): void
        {
            $this->definition = $definition;
        }

        public function currentStateCode(): ?string
        {
            return null;
        }

        public function normalize(array $data): array
        {
            return $this->normalizeAnywhereStatesFields($data);
        }
    };

    $harness->application = (object) ['selected_states' => $selectedStates];
    $harness->setDefinition(['base' => ['core_steps' => ['step' => ['fields' => $coreStepFields]]]]);

    return $harness;
}

describe('anywhere_states rules', function () {
    it('always requires the yes/no answer', function () {
        $built = $this->builder->buildForLivewire(
            anywhereStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['CA', 'TX'],
        );

        expect($built['rules']['coreData.applies_alcohol.anywhere'])->toBe(['required', 'in:0,1']);
    });

    it('requires a non-empty state checklist when yes with multiple applicable states', function () {
        $built = $this->builder->buildForLivewire(
            anywhereStep(),
            coreData: ['applies_alcohol' => ['anywhere' => '1', 'states' => []]],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['CA', 'TX'],
        );

        expect($built['rules']['coreData.applies_alcohol.states'])->toBe(['required', 'array', 'min:1'])
            ->and($built['rules']['coreData.applies_alcohol.states.*'])->toBe(['in:CA,TX']);
    });

    it('skips the checklist requirement when answered no', function () {
        $built = $this->builder->buildForLivewire(
            anywhereStep(),
            coreData: ['applies_alcohol' => ['anywhere' => '0', 'states' => []]],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['CA', 'TX'],
        );

        expect($built['rules'])->not->toHaveKey('coreData.applies_alcohol.states');
    });

    it('skips the checklist requirement with a single applicable state (auto-applied)', function () {
        $built = $this->builder->buildForLivewire(
            anywhereStep(),
            coreData: ['applies_alcohol' => ['anywhere' => '1', 'states' => []]],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['IL', 'AL'],
        );

        expect($built['rules'])->toHaveKey('coreData.applies_alcohol.anywhere')
            ->and($built['rules'])->not->toHaveKey('coreData.applies_alcohol.states');
    });

    it('emits no rules at all when no applicable state is selected', function () {
        $built = $this->builder->buildForLivewire(
            anywhereStep(),
            coreData: [],
            stateData: [],
            stateCode: null,
            phase: 'core',
            selectedStates: ['AL', 'AZ', 'CO'],
        );

        expect($built['rules'])->toBe([]);
    });
});

describe('anywhere_states save-time normalization', function () {
    $field = [
        'type' => 'anywhere_states',
        'label' => 'Alcohol?',
        'applicable_states' => ['CA', 'IL', 'TX'],
    ];

    it('clears states when anywhere is no', function () use ($field) {
        $harness = anywhereNormalizationHarness(['CA', 'TX'], ['applies_alcohol' => $field]);

        $result = $harness->normalize([
            'applies_alcohol' => ['anywhere' => '0', 'states' => ['CA', 'TX']],
        ]);

        expect($result['applies_alcohol'])->toBe(['anywhere' => '0', 'states' => []]);
    });

    it('auto-fills the single applicable state when yes', function () use ($field) {
        $harness = anywhereNormalizationHarness(['IL', 'AL'], ['applies_alcohol' => $field]);

        $result = $harness->normalize([
            'applies_alcohol' => ['anywhere' => '1', 'states' => []],
        ]);

        expect($result['applies_alcohol'])->toBe(['anywhere' => '1', 'states' => ['IL']]);
    });

    it('drops stale state codes outside applicable ∩ selected', function () use ($field) {
        $harness = anywhereNormalizationHarness(['CA', 'TX'], ['applies_alcohol' => $field]);

        $result = $harness->normalize([
            // NY is applicable-but-not-selected; AL is selected-but-not-applicable.
            'applies_alcohol' => ['anywhere' => '1', 'states' => ['CA', 'NY', 'AL']],
        ]);

        expect($result['applies_alcohol'])->toBe(['anywhere' => '1', 'states' => ['CA']]);
    });

    it('survives a yes → checked → no → yes toggle without resurrecting stale checks', function () use ($field) {
        $harness = anywhereNormalizationHarness(['CA', 'TX'], ['applies_alcohol' => $field]);

        $afterNo = $harness->normalize([
            'applies_alcohol' => ['anywhere' => '0', 'states' => ['CA', 'TX']],
        ]);

        $afterYesAgain = $harness->normalize([
            'applies_alcohol' => ['anywhere' => '1', 'states' => $afterNo['applies_alcohol']['states']],
        ]);

        expect($afterYesAgain['applies_alcohol']['states'])->toBe([]);
    });

    it('leaves non-anywhere fields untouched', function () use ($field) {
        $harness = anywhereNormalizationHarness(['CA'], ['applies_alcohol' => $field]);

        $result = $harness->normalize([
            'legal_name' => 'Acme LLC',
            'applies_alcohol' => ['anywhere' => '1', 'states' => []],
        ]);

        expect($result['legal_name'])->toBe('Acme LLC');
    });
});

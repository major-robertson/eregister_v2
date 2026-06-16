<?php

use App\Domains\Forms\Engine\Validation\CrossFieldValidatorRegistry;
use App\Domains\Forms\Livewire\Concerns\WithFormDataIO;

/**
 * Harness exposing syncPrincipalLocation without booting the Livewire
 * runner. Mirrors the anywhere-states normalization harness.
 */
function principalSyncHarness(): object
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

        public function sync(array $data): array
        {
            return $this->syncPrincipalLocation($data);
        }
    };

    $harness->application = (object) ['selected_states' => ['CA']];
    $harness->setDefinition(['base' => ['core_steps' => ['locations' => ['fields' => [
        'locations' => ['type' => 'repeater', 'schema' => []],
    ]]]]]);

    return $harness;
}

function locationRow(array $overrides = []): array
{
    return array_merge([
        '_id' => fake()->uuid(),
        'is_principal' => false,
        'type' => 'office',
        'address' => [
            'line1' => '500 Branch Ave',
            'city' => 'Springfield',
            'state' => 'TX',
            'zip' => '77001',
        ],
    ], $overrides);
}

function principalRow(array $addressOverrides = []): array
{
    return locationRow([
        'is_principal' => true,
        'address' => array_merge([
            'line1' => '1 Main St',
            'city' => 'Austin',
            'state' => 'TX',
            'zip' => '78701',
        ], $addressOverrides),
    ]);
}

beforeEach(function () {
    $this->registry = app(CrossFieldValidatorRegistry::class);
    $this->businessAddress = [
        'line1' => '1 Main St',
        'city' => 'Austin',
        'state' => 'TX',
        'zip' => '78701',
    ];
});

describe('locations principal cross-validation', function () {
    it('passes with exactly one principal matching the business address', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            ['business_address' => $this->businessAddress, 'locations' => [principalRow(), locationRow()]],
            'locations'
        );

        expect($errors)->toBe([]);
    });

    it('fails with zero principal rows', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            ['business_address' => $this->businessAddress, 'locations' => [locationRow(), locationRow()]],
            'locations'
        );

        expect($errors)->toHaveKey('locations')
            ->and($errors['locations'][0])->toContain('Exactly one location');
    });

    it('fails with two principal rows', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            ['business_address' => $this->businessAddress, 'locations' => [principalRow(), principalRow()]],
            'locations'
        );

        expect($errors)->toHaveKey('locations');
    });

    it('fails when the principal address differs from the business address', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            ['business_address' => $this->businessAddress, 'locations' => [principalRow(['line1' => '2 Other St'])]],
            'locations'
        );

        expect($errors)->toHaveKey('locations')
            ->and($errors['locations'][0])->toContain('must match the Principal Business Address');
    });

    it('matches addresses case-insensitively with surrounding whitespace', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            [
                'business_address' => $this->businessAddress,
                'locations' => [principalRow(['line1' => ' 1 MAIN ST ', 'city' => 'AUSTIN '])],
            ],
            'locations'
        );

        expect($errors)->toBe([]);
    });

    it('skips validation entirely when no locations exist yet', function () {
        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            ['business_address' => $this->businessAddress, 'locations' => []],
            'locations'
        );

        expect($errors)->toBe([]);
    });
});

describe('principal location sync', function () {
    it('auto-creates the principal row from the business address', function () {
        $synced = principalSyncHarness()->sync([
            'business_address' => $this->businessAddress,
            'locations' => [],
        ]);

        expect($synced['locations'])->toHaveCount(1)
            ->and($synced['locations'][0]['is_principal'])->toBeTrue()
            ->and($synced['locations'][0]['address'])->toBe($this->businessAddress)
            ->and($synced['locations'][0]['_id'])->not->toBeEmpty();
    });

    it('mirrors business address changes onto the existing principal row', function () {
        $existing = principalRow(['line1' => '999 Old St']);

        $synced = principalSyncHarness()->sync([
            'business_address' => $this->businessAddress,
            'locations' => [$existing, locationRow()],
        ]);

        expect($synced['locations'][0]['address'])->toBe($this->businessAddress)
            ->and($synced['locations'])->toHaveCount(2);
    });

    it('passes the cross-validator after syncing (unfailable through normal flows)', function () {
        $synced = principalSyncHarness()->sync([
            'business_address' => $this->businessAddress,
            'locations' => [locationRow()],
        ]);

        $errors = $this->registry->validate(
            'locations_principal_unique_and_matches_business_address',
            $synced,
            'locations'
        );

        expect($errors)->toBe([]);
    });

    it('does nothing until a business address exists', function () {
        $synced = principalSyncHarness()->sync(['locations' => []]);

        expect($synced['locations'] ?? [])->toBe([]);
    });
});

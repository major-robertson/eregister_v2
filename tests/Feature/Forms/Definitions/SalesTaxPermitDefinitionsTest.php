<?php

use App\Domains\Forms\Engine\FormRegistry;

/**
 * Smoke test for every Sales Tax Permit state definition.
 *
 * Validates that every state file (or the base fallback) loads through
 * FormRegistry without exception, produces a structurally sound array,
 * and uses only field types the engine knows how to render.
 *
 * This test exists because the TaxResaleCertificate import added 15 new
 * state definition files at once and we want a single canary that catches
 * typos / bad merges before they hit the form runner.
 */

const SUPPORTED_FIELD_TYPES = [
    'text', 'email', 'select', 'radio', 'checkbox', 'date',
    'percent', 'address', 'repeater', 'person_state_extra',
    'textarea', // falls back to text in the runner; permitted in definitions
];

/**
 * Recursively assert every field uses a supported type and that
 * select/radio fields have non-empty options.
 */
function assertFieldsValid(array $fields, string $context): void
{
    foreach ($fields as $key => $field) {
        if (! is_array($field) || ! isset($field['type'])) {
            // Skip stray non-field entries.
            continue;
        }

        $type = $field['type'];
        if (! in_array($type, SUPPORTED_FIELD_TYPES, true)) {
            throw new \PHPUnit\Framework\AssertionFailedError(
                "{$context}.{$key} has unknown type '{$type}'"
            );
        }

        if (in_array($type, ['select', 'radio'], true)) {
            $options = $field['options'] ?? null;
            if (! is_array($options) || $options === []) {
                throw new \PHPUnit\Framework\AssertionFailedError(
                    "{$context}.{$key} ({$type}) must have a non-empty options array"
                );
            }
        }

        if ($type === 'repeater' || $type === 'person_state_extra') {
            assertFieldsValid($field['schema'] ?? [], "{$context}.{$key}.schema");
        }
    }
}

describe('SalesTaxPermit definitions', function () {
    it('loads base.php with required top-level keys', function () {
        $registry = app(FormRegistry::class);
        $base = $registry->getBase('sales_tax_permit');

        expect($base)->toBeArray();
        expect($base)->toHaveKey('key');
        expect($base['key'])->toBe('sales_tax_permit');
        expect($base)->toHaveKey('version');
        expect($base)->toHaveKey('core_steps');
        expect($base)->toHaveKey('state_steps');
        expect($base)->toHaveKey('available_states');
        expect($base)->toHaveKey('excluded_states');
    });

    it('base core_steps contain the expected canonical step keys', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect($base['core_steps'])->toHaveKey('identity');
        expect($base['core_steps'])->toHaveKey('activity');
        expect($base['core_steps'])->toHaveKey('contact_and_address');
        expect($base['core_steps'])->toHaveKey('responsible_people');
    });

    it('base state_steps contain the expected canonical step keys', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect($base['state_steps'])->toHaveKey('state_details');
        expect($base['state_steps'])->toHaveKey('state_responsible_people');
    });

    it('every available state loads through FormRegistry without exception', function (string $stateCode) {
        $registry = app(FormRegistry::class);
        $merged = $registry->get('sales_tax_permit', $stateCode);

        expect($merged)->toBeArray();
        expect($merged)->toHaveKey('core_steps');
        expect($merged)->toHaveKey('state_steps');

        // Validate every field in every step uses a supported type.
        foreach (['core_steps', 'state_steps'] as $stepType) {
            foreach ($merged[$stepType] ?? [] as $stepKey => $step) {
                assertFieldsValid(
                    $step['fields'] ?? [],
                    "{$stateCode}.{$stepType}.{$stepKey}"
                );
            }
        }
    })->with([
        // 46 selectable states (50 - DE/MT/NH/OR per excluded_states in base.php).
        // Hard-coded because Pest data providers run before app bootstrap and
        // can't call app_path()/config().
        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'FL', 'GA', 'HI',
        'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA',
        'MI', 'MN', 'MS', 'MO', 'NE', 'NV', 'NJ', 'NM', 'NY', 'NC',
        'ND', 'OH', 'OK', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT',
        'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
    ]);

    it('each authored state file produces a different definition than base', function (string $stateCode) {
        $registry = app(FormRegistry::class);
        $base = $registry->getBase('sales_tax_permit');
        $merged = $registry->get('sales_tax_permit', $stateCode);

        // Compare state_steps -- override files always change at least one field there.
        $baseStateFields = collect($base['state_steps'] ?? [])->flatMap(fn ($s) => array_keys($s['fields'] ?? []))->all();
        $mergedStateFields = collect($merged['state_steps'] ?? [])->flatMap(fn ($s) => array_keys($s['fields'] ?? []))->all();

        expect(count($mergedStateFields))->toBeGreaterThan(
            count($baseStateFields),
            "{$stateCode} override file should add at least one state-specific field"
        );
    })->with([
        // Authored state files (TX, CA already shipped pre-import; rest added by import)
        'TX', 'CA', 'TN', 'NY', 'NJ', 'MD', 'GA',
        'FL', 'IL', 'CT', 'WA', 'WI', 'MO',
        'PA', 'OK', 'OH', 'MI',
    ]);

    it('excludes DE, MT, NH, OR from available_states', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        foreach (['DE', 'MT', 'NH', 'OR'] as $excluded) {
            expect($base['available_states'])->not->toContain($excluded);
            expect($base['excluded_states'])->toHaveKey($excluded);
        }
    });

    it('responsible_people repeater schema includes ssn (sensitive)', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        $schema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'] ?? [];
        expect($schema)->toHaveKey('ssn');
        expect($schema['ssn']['sensitive'] ?? false)->toBeTrue();
    });

    it('TX merged definition includes tx_driver_license inside per-person schema', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'TX');

        $personSchema = $merged['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];
        expect($personSchema)->toHaveKey('tx_driver_license');
        expect($personSchema['tx_driver_license']['sensitive'] ?? false)->toBeTrue();
    });

    it('CA merged definition includes ca_driver_license_exp inside per-person schema', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'CA');

        $personSchema = $merged['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];
        expect($personSchema)->toHaveKey('ca_driver_license_exp');
        expect($personSchema['ca_driver_license_exp']['type'])->toBe('date');
    });

    it('PA merged definition includes the 21 business categories and 68 counties', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'PA');

        $detailFields = $merged['state_steps']['state_details']['fields'] ?? [];

        $categoryKeys = array_filter(array_keys($detailFields), fn ($k) => str_starts_with($k, 'pa_business_category_'));
        expect($categoryKeys)->toHaveCount(21, 'PA must declare 21 business category checkboxes');

        $countyKeys = array_filter(array_keys($detailFields), fn ($k) => str_starts_with($k, 'pa_county_'));
        expect($countyKeys)->toHaveCount(68, 'PA must declare 68 county checkboxes (Out of State + 67 PA counties)');
    });

    it('falls back to base for states without an override file (e.g. AL)', function () {
        $registry = app(FormRegistry::class);
        $base = $registry->getBase('sales_tax_permit');
        $alabama = $registry->get('sales_tax_permit', 'AL');

        // No AL.php exists, so merged should equal base.
        expect($alabama)->toEqual($base);
    });
});

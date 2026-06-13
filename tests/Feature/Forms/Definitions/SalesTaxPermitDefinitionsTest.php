<?php

use App\Domains\Forms\Engine\FormRegistry;

/**
 * Structural canary for every Sales Tax Permit definition (v3 clean
 * rebuild). Validates that every state file loads through FormRegistry,
 * uses only renderable field types, keeps the clean-answer-shape
 * contracts (applies and matrix fields in core only, applicable_states
 * present, no shared_ prefix, no per-state duplicates of cross-state
 * questions), and pins the §3A.2 legacy-fidelity fixes.
 */
const SUPPORTED_FIELD_TYPES = [
    'text', 'email', 'select', 'radio', 'checkbox', 'date',
    'percent', 'address', 'repeater', 'person_state_extra',
    'matrix', 'anywhere_states',
    'textarea', // falls back to text in the runner; permitted in definitions
];

// State files that exist on disk (authored overrides).
const AUTHORED_STATES = [
    'TX', 'CA', 'TN', 'NY', 'NJ', 'MD', 'GA',
    'FL', 'IL', 'CT', 'WA', 'WI', 'MO',
    'PA', 'OK', 'OH', 'MI', 'NC',
];

/**
 * Recursively assert every field uses a supported type and that
 * select/radio fields have non-empty options (token strings allowed).
 */
function assertFieldsValid(array $fields, string $context): void
{
    foreach ($fields as $key => $field) {
        if (! is_array($field) || ! isset($field['type'])) {
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
            $isToken = is_string($options) && str_starts_with($options, '<<');
            if (! $isToken && (! is_array($options) || $options === [])) {
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

/**
 * Flatten every field key declared across all state_steps of a merged
 * definition (including repeater/person schemas).
 */
function allStateStepFieldKeys(array $merged): array
{
    $keys = [];
    foreach ($merged['state_steps'] ?? [] as $step) {
        foreach ($step['fields'] ?? [] as $key => $field) {
            $keys[] = $key;
            foreach ($field['schema'] ?? [] as $subKey => $subField) {
                $keys[] = $subKey;
            }
        }
    }

    return $keys;
}

describe('SalesTaxPermit definitions (v3)', function () {
    it('loads base.php with required top-level keys at version 3', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect($base)->toBeArray()
            ->and($base['key'])->toBe('sales_tax_permit')
            ->and($base['version'])->toBe(3)
            ->and($base)->toHaveKeys(['core_steps', 'state_steps', 'available_states', 'excluded_states']);
    });

    it('base core_steps contain the v3 step keys in flow order', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect(array_keys($base['core_steps']))->toBe([
            'identity',
            'contact_and_address',
            'activity',
            'responsible_people',
            'locations',
            'state_dates_and_estimates',
            'sales_channels_and_activities',
            'products_and_services',
            'employees_and_payroll',
            'acquisition_and_history',
            'bank',
            'payment_processor',
            'entity_extras',
        ]);
    });

    it('keeps base state_steps minimal (no generic per-state questions remain)', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect(array_keys($base['state_steps']))->toBe(['state_details', 'state_responsible_people'])
            ->and($base['state_steps']['state_details']['fields'])->toBe([]);
    });

    it('every available state loads through FormRegistry with valid field types', function (string $stateCode) {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);

        expect($merged)->toBeArray()->toHaveKeys(['core_steps', 'state_steps']);

        foreach (['core_steps', 'state_steps'] as $stepType) {
            foreach ($merged[$stepType] ?? [] as $stepKey => $step) {
                assertFieldsValid($step['fields'] ?? [], "{$stateCode}.{$stepType}.{$stepKey}");
            }
        }
    })->with([
        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'FL', 'GA', 'HI',
        'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA',
        'MI', 'MN', 'MS', 'MO', 'NE', 'NV', 'NJ', 'NM', 'NY', 'NC',
        'ND', 'OH', 'OK', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT',
        'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
    ]);

    it('each authored state file adds state-specific fields beyond base', function (string $stateCode) {
        $registry = app(FormRegistry::class);
        $base = $registry->getBase('sales_tax_permit');
        $merged = $registry->get('sales_tax_permit', $stateCode);

        $baseStateFields = collect($base['state_steps'])->flatMap(fn ($s) => array_keys($s['fields'] ?? []))->all();
        $mergedStateFields = collect($merged['state_steps'])->flatMap(fn ($s) => array_keys($s['fields'] ?? []))->all();

        expect(count($mergedStateFields))->toBeGreaterThan(
            count($baseStateFields),
            "{$stateCode} override file should add at least one state-specific field"
        );
    })->with(AUTHORED_STATES);

    it('excludes DE, MT, NH, OR from available_states', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        foreach (['DE', 'MT', 'NH', 'OR'] as $excluded) {
            expect($base['available_states'])->not->toContain($excluded)
                ->and($base['excluded_states'])->toHaveKey($excluded);
        }
    });

    /*
    |----------------------------------------------------------------------
    | Clean answer shape contracts
    |----------------------------------------------------------------------
    */
    it('every applies_* field is anywhere_states, lives in core_steps, and declares applicable_states', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        $appliesFields = [];
        foreach ($base['core_steps'] as $step) {
            foreach ($step['fields'] ?? [] as $key => $field) {
                if (str_starts_with($key, 'applies_')) {
                    $appliesFields[$key] = $field;
                }
            }
        }

        expect($appliesFields)->not->toBeEmpty();

        foreach ($appliesFields as $key => $field) {
            expect($field['type'])->toBe('anywhere_states', "{$key} must be anywhere_states");

            $applicable = $field['applicable_states'] ?? null;
            expect($applicable === '*' || (is_array($applicable) && $applicable !== []))->toBeTrue(
                "{$key} must declare non-empty applicable_states"
            );

            if (is_array($applicable)) {
                $unavailable = array_diff($applicable, $base['available_states']);
                expect(array_values($unavailable))->toBe([], "{$key} lists unavailable states");
            }
        }
    });

    it('every matrix_* field is a matrix in core_steps with cell_rules and applicable_states', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        $matrixFields = [];
        foreach ($base['core_steps'] as $step) {
            foreach ($step['fields'] ?? [] as $key => $field) {
                if (str_starts_with($key, 'matrix_')) {
                    $matrixFields[$key] = $field;
                }
            }
        }

        expect($matrixFields)->not->toBeEmpty();

        foreach ($matrixFields as $key => $field) {
            expect($field['type'])->toBe('matrix', "{$key} must be matrix");
            expect($field['cell_rules'] ?? [])->not->toBeEmpty("{$key} must declare cell_rules");

            $applicable = $field['applicable_states'] ?? null;
            expect($applicable === '*' || (is_array($applicable) && $applicable !== []))->toBeTrue(
                "{$key} must declare non-empty applicable_states"
            );
        }
    });

    it('no applies_* or matrix_* field exists in any state_steps', function (string $stateCode) {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);

        $offenders = collect(allStateStepFieldKeys($merged))
            ->filter(fn ($k) => str_starts_with($k, 'applies_') || str_starts_with($k, 'matrix_'))
            ->values()
            ->all();

        expect($offenders)->toBe([], "{$stateCode} declares applies_/matrix_ fields in state_steps: ".implode(', ', $offenders));
    })->with(AUTHORED_STATES);

    it('no field key uses the banned shared_ prefix anywhere', function (string $stateCode) {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);

        $allKeys = allStateStepFieldKeys($merged);
        foreach ($merged['core_steps'] as $step) {
            foreach ($step['fields'] ?? [] as $key => $field) {
                $allKeys[] = $key;
            }
        }

        $offenders = collect($allKeys)->filter(fn ($k) => str_starts_with($k, 'shared_'))->values()->all();

        expect($offenders)->toBe([]);
    })->with(['CA', 'NY', 'AL']);

    it('no state file re-declares a cross-state collapsed question family', function (string $stateCode) {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);

        // Exact "<2-letter-prefix>_<collapsed name>" keys — matches the
        // v2-era per-state duplicates without false-positives on legit
        // state-only fields (e.g. ca_third_party_internet_sales,
        // fl_rt_account_number, pa_predecessor_uc_account_number).
        $forbiddenNames = [
            'internet_sales', 'website_address', 'home_based_business',
            'purchase_existing_business', 'bank_name', 'routing_number',
            'account_number', 'checking_number', 'accept_credit_cards',
            'publicly_traded', 'ticker_symbol', 'involved_in_merger',
            'date_of_incorporation', 'fiscal_year_end', 'business_fax_number',
        ];

        $offenders = [];
        foreach (allStateStepFieldKeys($merged) as $key) {
            foreach ($forbiddenNames as $name) {
                if (preg_match('/^[a-z]{2}_'.preg_quote($name, '/').'$/', $key)) {
                    $offenders[] = $key;
                }
            }
        }

        expect($offenders)->toBe(
            [],
            "{$stateCode} re-declares collapsed cross-state questions: ".implode(', ', $offenders)
        );
    })->with(AUTHORED_STATES);

    /*
    |----------------------------------------------------------------------
    | §3A.2 legacy-fidelity fixes
    |----------------------------------------------------------------------
    */
    it('PA has the 68 county checkboxes and NO dead business-category checkboxes', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'PA');

        $allKeys = allStateStepFieldKeys($merged);

        $countyKeys = array_filter($allKeys, fn ($k) => str_starts_with($k, 'pa_county_') && $k !== 'pa_county');
        expect(count($countyKeys))->toBe(68, 'PA must declare 68 county checkboxes (Out of State + 67 PA counties)');

        $categoryKeys = array_filter($allKeys, fn ($k) => str_starts_with($k, 'pa_business_category_'));
        expect($categoryKeys)->toBe([], 'The 21 PA business categories are dead code in legacy and must not exist');
    });

    it('OH does not declare the invented company-contact SSN', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'OH');

        expect(allStateStepFieldKeys($merged))->not->toContain('oh_company_contact_ssn');
    });

    it('TN gates the $1,200 services question on exceed4800 == 1 (legacy chain, v2 had it inverted)', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'TN');

        $field = $merged['state_steps']['state_details']['fields']['tn_exceed_1200_taxable_services'];

        expect($field['when'])->toBe(['==' => [['var' => 'tn_exceed_4800_annual'], '1']]);
    });

    it('CT taxesServicesRequested options match the legacy list', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'CT');

        $fields = $merged['state_steps']['ct_state_ids_and_tax_services']['fields'];

        expect($fields['ct_taxes_requested_retailer']['label'])->toBe('Retailer')
            ->and($fields['ct_taxes_requested_wholesaler']['label'])->toBe('Wholesaler')
            ->and($fields['ct_taxes_requested_manufacturer']['label'])->toBe('Manufacturer')
            ->and($fields['ct_taxes_requested_service_provider']['label'])->toBe('Service Provider')
            ->and($fields['ct_taxes_requested_other']['label'])->toBe('Other');
    });

    it('MD reasonsForApplying options match the legacy list', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'MD');

        $fields = $merged['state_steps']['state_details']['fields'];
        $reasonValues = collect($fields)
            ->filter(fn ($f, $k) => str_starts_with($k, 'md_reason_'))
            ->pluck('source_value')
            ->all();

        expect($reasonValues)->toBe([
            'New Business', 'Reorganization', 'Employs Domestic Help', 'Merger',
            'Agricultural Operation', 'Change of Entity', 'Purchased Going Business',
            'Professional Employer Organization', 'Reopen/Reactivate',
        ]);
    });

    it('IL liquor place options match the legacy list', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'IL');

        $fields = $merged['state_steps']['il_utilities_and_other_taxes']['fields'];
        $placeValues = collect($fields)
            ->filter(fn ($f, $k) => str_starts_with($k, 'il_liquor_place_'))
            ->pluck('source_value')
            ->all();

        expect($placeValues)->toBe(['Eating Place', 'Drinking Place', 'Liquor Store']);
    });

    it('NC loads with its county select and SOS number', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'NC');

        $fields = $merged['state_steps']['state_details']['fields'];

        expect($fields)->toHaveKeys(['nc_business_county', 'nc_secretary_of_state_number'])
            ->and(count($fields['nc_business_county']['options']))->toBe(100);
    });

    /*
    |----------------------------------------------------------------------
    | Group integrity (every grouped field exists; no duplicates; every
    | appended field grouped)
    |----------------------------------------------------------------------
    */
    it('every grouped field exists in its step and appears in only one group', function (string $stateCode) {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);

        $problems = [];
        foreach ($merged['state_steps'] as $stepKey => $step) {
            $fields = $step['fields'] ?? [];
            $seen = [];
            foreach ($step['groups'] ?? [] as $group) {
                foreach ($group['fields'] ?? [] as $entry) {
                    $keys = is_array($entry) ? $entry : [$entry];
                    foreach ($keys as $key) {
                        if (! array_key_exists($key, $fields)) {
                            $problems[] = "{$stepKey}: unknown {$key}";
                        }
                        if (isset($seen[$key])) {
                            $problems[] = "{$stepKey}: duplicate {$key}";
                        }
                        $seen[$key] = true;
                    }
                }
            }
        }

        expect($problems)->toBe([], "{$stateCode} group integrity issues: ".implode('; ', $problems));
    })->with(AUTHORED_STATES);

    it('responsible_people repeater schema includes ssn (sensitive)', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        $schema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];
        expect($schema['ssn']['sensitive'] ?? false)->toBeTrue();
    });

    it('falls back to base for states without an override file (e.g. AL)', function () {
        $registry = app(FormRegistry::class);

        expect($registry->get('sales_tax_permit', 'AL'))->toEqual($registry->getBase('sales_tax_permit'));
    });
});

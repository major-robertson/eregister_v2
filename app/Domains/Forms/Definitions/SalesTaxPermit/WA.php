<?php

/**
 * Washington — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/standard/application/washington.blade.php`
 * + matching `public/js/states/standard/washington.js`.
 *
 * Standard organization/business/primary blades supply the base equivalents;
 * Washington adds an environmental / fire-safety / nexus questionnaire.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    'wa_unified_business_identifier' => [
                        'type' => 'text',
                        'label' => 'WA Unified Business Identifier (UBI)',
                        'rules' => ['nullable', 'digits:9'],
                        'help' => '9-digit UBI assigned by the Washington Department of Revenue.',
                        'when' => ['!=' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'source_name' => 'unifiedBusinessIdentifier',
                    ],
                    'wa_estimated_annual_income' => [
                        'type' => 'text',
                        'label' => 'Estimated Annual Gross Income (USD)',
                        'rules' => ['required', 'numeric', 'min:0'],
                        'source_name' => 'washingtonEstimatedAnnualIncome',
                    ],
                    'wa_estimated_employees' => [
                        'type' => 'text',
                        'label' => 'Estimated Number of WA Employees',
                        'rules' => ['required', 'integer', 'min:0'],
                        'source_name' => 'washingtonEstimatedEmployees',
                    ],
                    'wa_business_address_in_residence' => [
                        'type' => 'radio',
                        'label' => 'Is the WA business location in a residence?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonBusinessAddressInResidence',
                    ],
                    'wa_square_footage_used' => [
                        'type' => 'text',
                        'label' => 'Square Footage Used for Business',
                        'rules' => ['required', 'integer', 'min:0'],
                        'source_name' => 'washingtonSquareFootageUsed',
                    ],
                    'wa_exterior_modifications' => [
                        'type' => 'radio',
                        'label' => 'Will you make exterior modifications (signs, etc.)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonExteriorModifications',
                    ],
                    'wa_compressed_gases' => [
                        'type' => 'radio',
                        'label' => 'Will you store or use compressed gases?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonCompressedGasses',
                    ],
                    'wa_smoke_detection' => [
                        'type' => 'radio',
                        'label' => 'Does the location have smoke detection / sprinkler systems?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonSmokeDetection',
                    ],
                    'wa_discharge_to_sewer' => [
                        'type' => 'radio',
                        'label' => 'Will the business discharge to a sewer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonDischargeToSewerFromBusiness',
                    ],
                    'wa_any_toxic_materials' => [
                        'type' => 'radio',
                        'label' => 'Will you store flammable, hazardous, or toxic materials?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonAnyToxicMaterials',
                    ],
                    'wa_any_floor_drains' => [
                        'type' => 'radio',
                        'label' => 'Are there floor drains in the business location?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonAnyFloorDrains',
                    ],
                    'wa_alarm_monitoring_service' => [
                        'type' => 'radio',
                        'label' => 'Do you use an emergency alarm monitoring service?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'washingtonAlarmMonitoringService',
                    ],
                ],
            ],
        ],
    ],
];

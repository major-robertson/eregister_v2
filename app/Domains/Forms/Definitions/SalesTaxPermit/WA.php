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
            'groups' => ['append' => [
                ['title' => 'WA Identifiers & Income', 'fields' => [
                    'wa_unified_business_identifier', 'wa_estimated_annual_income',
                    'wa_estimated_employees',
                ]],
                ['title' => 'Location & Modifications', 'fields' => [
                    'wa_business_address_in_residence', 'wa_square_footage_used',
                    'wa_exterior_modifications',
                ]],
                ['title' => 'Environmental & Fire Safety', 'fields' => [
                    'wa_compressed_gases', 'wa_smoke_detection', 'wa_discharge_to_sewer',
                    'wa_any_toxic_materials', 'wa_any_floor_drains', 'wa_alarm_monitoring_service',
                ]],
            ]],
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
                    'wa_business_address_in_residence' => yesNoField('Is the WA business location in a residence?', 'washingtonBusinessAddressInResidence'),
                    'wa_square_footage_used' => [
                        'type' => 'text',
                        'label' => 'Square Footage Used for Business',
                        'rules' => ['required', 'integer', 'min:0'],
                        'source_name' => 'washingtonSquareFootageUsed',
                    ],
                    'wa_exterior_modifications' => yesNoField('Will you make exterior modifications (signs, etc.)?', 'washingtonExteriorModifications'),
                    'wa_compressed_gases' => yesNoField('Will you store or use compressed gases?', 'washingtonCompressedGasses'),
                    'wa_smoke_detection' => yesNoField('Does the location have smoke detection / sprinkler systems?', 'washingtonSmokeDetection'),
                    'wa_discharge_to_sewer' => yesNoField('Will the business discharge to a sewer?', 'washingtonDischargeToSewerFromBusiness'),
                    'wa_any_toxic_materials' => yesNoField('Will you store flammable, hazardous, or toxic materials?', 'washingtonAnyToxicMaterials'),
                    'wa_any_floor_drains' => yesNoField('Are there floor drains in the business location?', 'washingtonAnyFloorDrains'),
                    'wa_alarm_monitoring_service' => yesNoField('Do you use an emergency alarm monitoring service?', 'washingtonAlarmMonitoringService'),
                ],
            ],
        ],
    ],
];

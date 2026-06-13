<?php

/**
 * Washington — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/standard/application/washington.blade.php` +
 * UBI on the standard businessInformation blade.
 *
 * Collapsed into core: annual gross income + employee count (matrix),
 * residence location (applies_home_or_residence_based + locations[] row
 * type), square footage (locations[] row), compressed gases + toxic
 * materials gates (applies_hazardous_materials), banking (core bank_*).
 */
$waHazmatGate = ['contains' => [['var' => '$root.applies_hazardous_materials.states'], 'WA']];

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Washington Sales Tax Permit Details',
            'description' => 'Washington facility and safety questionnaire.',
            'groups' => [
                ['title' => 'WA Identifiers', 'fields' => ['wa_unified_business_identifier']],
                ['title' => 'Facilities & Safety', 'fields' => [
                    'wa_exterior_modifications', 'wa_smoke_detection', 'wa_discharge_to_sewer',
                    'wa_any_floor_drains', 'wa_alarm_monitoring_service',
                ]],
                ['title' => 'Hazardous Materials (WA detail)', 'fields' => [
                    'wa_compressed_gases', 'wa_any_toxic_materials',
                ]],
            ],
            'fields' => [
                'wa_unified_business_identifier' => [
                    'type' => 'text',
                    'label' => 'WA Unified Business Identifier (UBI)',
                    'rules' => ['nullable', 'digits:9'],
                    'help' => '9-digit UBI assigned by the Washington Department of Revenue.',
                    'when' => ['!=' => [['var' => '$root.entity_type'], 'sole_prop']],
                    'source_name' => 'unifiedBusinessIdentifier',
                ],
                'wa_exterior_modifications' => yesNoField('Will any exterior or interior modifications, including signs, be made to this business?', 'washingtonExteriorModifications'),
                'wa_smoke_detection' => yesNoField('Does the location have an automatic smoke detection or fire sprinkler system?', 'washingtonSmokeDetection'),
                'wa_discharge_to_sewer' => yesNoField('Will the business discharge to a sewer (other than domestic sanitary discharges)?', 'washingtonDischargeToSewerFromBusiness'),
                'wa_any_floor_drains' => yesNoField('Are there floor drains other than in restroom/shower facilities?', 'washingtonAnyFloorDrains'),
                'wa_alarm_monitoring_service' => yesNoField('Do you use an emergency alarm monitoring service?', 'washingtonAlarmMonitoringService'),
                'wa_compressed_gases' => nullableYesNoField('Will you store or use compressed gases (oxygen, helium, acetylene, propane, nitrous oxide, etc.)?', 'washingtonCompressedGasses', [
                    'when' => $waHazmatGate,
                ]),
                'wa_any_toxic_materials' => nullableYesNoField('Will you store flammable, hazardous, or toxic materials?', 'washingtonAnyToxicMaterials', [
                    'when' => $waHazmatGate,
                ]),
            ],
        ],
    ],
];

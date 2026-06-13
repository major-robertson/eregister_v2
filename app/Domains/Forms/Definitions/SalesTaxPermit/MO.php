<?php

/**
 * Missouri — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/standard/application/missouri.blade.php`.
 *
 * Collapsed into core: retail sales / employees / out-of-state seller /
 * utilities / vehicle leases / aviation fuel gates (applies_*),
 * incorporation date (core), state of incorporation (core formation_state).
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Missouri Sales Tax Permit Details',
            'description' => 'Missouri-specific tax and registration questions.',
            'groups' => [
                ['title' => 'Use & Income Tax', 'fields' => [
                    'mo_purchase_items_from_out_of_state', 'mo_required_corporate_income_tax',
                ]],
                ['title' => 'Vehicle Leasing (MO detail)', 'fields' => ['mo_out_of_state_vehicle_leases']],
                ['title' => 'MO Secretary of State', 'fields' => [
                    'mo_exempt_registering_sos', 'mo_charter_number',
                ]],
            ],
            'fields' => [
                'mo_purchase_items_from_out_of_state' => yesNoField('Will you purchase items from out-of-state retailers who do not charge Missouri tax?', 'missouri_purchase_items_from_out_of_state'),
                'mo_required_corporate_income_tax' => yesNoField('Will you be required to file Missouri Corporate Income Tax?', 'missouri_required_corporate_income_tax'),
                'mo_out_of_state_vehicle_leases' => nullableYesNoField('If you are an out-of-state company, will you lease motor vehicles to Missouri residents?', 'missouri_out_of_state_vehicle_leases', [
                    'when' => ['contains' => [['var' => '$root.applies_vehicle_rentals.states'], 'MO']],
                ]),
                'mo_exempt_registering_sos' => yesNoField('Are you exempt from registering with the Missouri Secretary of State?', 'missouri_exempt_registering_sos'),
                'mo_charter_number' => [
                    'type' => 'text',
                    'label' => 'Missouri Charter Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'help' => 'Required for entities registered with the Missouri Secretary of State.',
                    'source_name' => 'missouri_charter_number',
                ],
            ],
        ],
    ],
];

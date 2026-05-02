<?php

/**
 * Missouri — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/standard/application/missouri.blade.php`
 * + matching `public/js/states/standard/missouri.js`.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Sales & Use', 'fields' => [
                    'mo_make_retail_sales_in_state', 'mo_have_employees',
                    'mo_out_of_state_making_retail_sales', 'mo_purchase_items_from_out_of_state',
                    'mo_retail_sales_of_qualifying_utilities',
                ]],
                ['title' => 'Income & Vehicle', 'fields' => [
                    'mo_required_corporate_income_tax', 'mo_lease_motor_vehicles',
                    'mo_out_of_state_vehicle_leases', 'mo_aviation_fuel',
                ]],
                ['title' => 'MO SOS Registration', 'fields' => [
                    'mo_exempt_registering_sos', 'mo_date_of_incorporation',
                    'mo_state_of_incorporation', 'mo_charter_number',
                ]],
            ]],
            'fields' => [
                'append' => [
                    'mo_make_retail_sales_in_state' => yesNoField('Will you make retail sales to Missouri customers?', 'missouri_make_retail_sales_in_state'),
                    'mo_have_employees' => yesNoField('Do you have or plan to have Missouri employees?', 'missouri_have_employees'),
                    'mo_out_of_state_making_retail_sales' => yesNoField('Are you out-of-state but selling to MO customers?', 'missouri_out_of_state_making_retail_sales'),
                    'mo_purchase_items_from_out_of_state' => yesNoField('Will you purchase items from retailers who do not charge MO sales tax?', 'missouri_purchase_items_from_out_of_state'),
                    'mo_retail_sales_of_qualifying_utilities' => yesNoField('Will you make retail sales of qualifying utilities?', 'missouri_retail_sales_of_qualifying_utilities'),
                    'mo_required_corporate_income_tax' => yesNoField('Are you required to file Missouri Corporate Income Tax?', 'missouri_required_corporate_income_tax'),
                    'mo_lease_motor_vehicles' => yesNoField('Will you lease motor vehicles to MO customers?', 'missouri_lease_motor_vehicles'),
                    'mo_out_of_state_vehicle_leases' => yesNoField('Are you an out-of-state company leasing vehicles into MO?', 'missouri_out_of_state_vehicle_leases'),
                    'mo_aviation_fuel' => yesNoField('Will you sell or consume aviation fuel?', 'missouri_aviation_fuel'),
                    'mo_exempt_registering_sos' => yesNoField('Are you exempt from registering with the Missouri Secretary of State?', 'missouri_exempt_registering_sos'),
                    'mo_date_of_incorporation' => [
                        'type' => 'date',
                        'label' => 'Date of Incorporation',
                        'rules' => ['nullable', 'date', 'before_or_equal:today'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit', 'llc_single', 'llc_multi']]],
                        'source_name' => 'missouri_date_of_incorporation',
                    ],
                    'mo_state_of_incorporation' => [
                        'type' => 'select',
                        'label' => 'State of Incorporation',
                        'options' => array_combine(
                            array_keys(config('states')),
                            array_values(config('states'))
                        ),
                        'rules' => ['nullable', 'size:2'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit', 'llc_single', 'llc_multi']]],
                        'source_name' => 'missouri_state_of_incorporation',
                    ],
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
    ],
];

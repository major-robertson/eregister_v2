<?php

/**
 * Georgia — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/georgia/application/`.
 *
 * Collapsed into core: incorporation date/country + secondary NAICS +
 * fiscal year end (core), motor fuel retailer/wholesaler (applies_fuel),
 * contractor (applies_contractor), marketplace facilitator
 * (applies_marketplace), first GA sales date (matrix_first_sales_date),
 * 4+ locations (derived from locations[]), county selects (locations[]).
 */
$gaAccounts = [
    'ga_account_sales_and_use_tax' => ['Sales & Use Tax', 'Sales_And_Use_Tax'],
    'ga_account_withholding_tax' => ['Withholding Tax', 'Withholding_Tax'],
    'ga_account_corporate_income_tax' => ['Corporate Income Tax', 'Corporate_Income_Tax'],
    'ga_account_composite_tax' => ['Composite Tax', 'Composite_Tax'],
    'ga_account_state_hotel_motel_fee' => ['State Hotel-Motel Fee', 'State_Hotel_Motel_Fee'],
    'ga_account_alcohol_license' => ['Alcohol License', 'Alcohol_License'],
    'ga_account_tobacco_license' => ['Tobacco License', 'Tobacco_License'],
    'ga_account_motor_fuel_distributor_tax' => ['Motor Fuel Distributor Tax', 'Motor_Fuel_Distributor_Tax'],
    'ga_account_international_fuel_tax' => ['International Fuel Tax (IFTA)', 'International_Fuel_Tax'],
    'ga_account_firework_excise_tax' => ['Fireworks Excise Tax', 'Firework_Excise_Tax'],
    'ga_account_adult_entertainment_tax' => ['Adult Entertainment Tax', 'Adult_Entertainment_Tax'],
    'ga_account_non_prepaid_911' => ['Non-Prepaid 911 Charge', 'Non_Prepaid_911_Charge'],
    'ga_account_prepaid_wireless_911' => ['Prepaid Wireless 911 Charge', 'Prepaid_Wireless_911_Charge'],
    'ga_account_public_service_commission' => ['Public Service Commission', 'Public_Service_Commision'],
    'ga_account_public_utilities_and_airlines' => ['Public Utilities and Airlines', 'Public_Utilities_And_Airlines'],
    'ga_account_qualified_timberland_property' => ['Qualified Timberland Property', 'Qualified_Timberland_Property'],
    'ga_account_railroad_equipment' => ['Railroad Equipment', 'Railroad_Equipment'],
    'ga_account_withholding_misc_film' => ['Withholding Misc/Film', 'Withholding_Misc_Film'],
];

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Georgia Sales Tax Permit Details',
            'description' => 'Georgia DOR registration questions.',
            'groups' => [
                ['title' => 'Georgia Classification', 'fields' => [
                    'ga_type_of_llc', 'ga_accounting_method',
                ]],
                ['title' => 'DOR Notice', 'fields' => [
                    'ga_registering_as_result_from_dor', 'ga_letter_id_on_notice',
                ]],
                ['title' => 'Tax Account Selections', 'fields' => array_keys($gaAccounts)],
            ],
            'fields' => array_merge(
                [
                    'ga_type_of_llc' => [
                        'type' => 'select',
                        'label' => 'Type of LLC',
                        'options' => [
                            'multiple' => 'Multiple',
                            'single' => 'Single',
                            'partnership' => 'Partnership',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                        'source_name' => 'typeOfLLC',
                    ],
                    'ga_accounting_method' => [
                        'type' => 'radio',
                        'label' => 'Accounting Method',
                        'options' => ['accrual' => 'Accrual', 'cash' => 'Cash Basis'],
                        'rules' => ['required', 'in:accrual,cash'],
                        'source_name' => 'accountingMethod',
                    ],
                    'ga_registering_as_result_from_dor' => yesNoField('Are you registering as a result of a notice from the Georgia Department of Revenue?', 'registeringAsResultFromDOR', ['drives_conditional' => true]),
                    'ga_letter_id_on_notice' => [
                        'type' => 'text',
                        'label' => 'Letter ID on the Notice',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ga_registering_as_result_from_dor'], '1']],
                        'source_name' => 'letterIdOnNotice',
                    ],
                ],
                collect($gaAccounts)->map(fn ($def) => yesNoField($def[0], $def[1]))->all(),
            ),
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ga_is_officer' => [
                                'type' => 'radio',
                                'label' => 'Is this person an Officer?',
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => 'primaryContactOfficer',
                            ],
                            'ga_owner_type' => [
                                'type' => 'select',
                                'label' => 'Owner Type (Georgia)',
                                'options' => ['officer' => 'Officer', 'successor' => 'Successor'],
                                'rules' => ['nullable'],
                                'source_name' => 'ownerType',
                            ],
                            'ga_responsible_for_sales_tax' => [
                                'type' => 'radio',
                                'label' => 'Is this person a Responsible Person for sales tax?',
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => 'primaryContactResponsiblePerson',
                            ],
                            'ga_effective_date' => [
                                'type' => 'date',
                                'label' => 'Effective Date of Ownership',
                                'rules' => ['required', 'date'],
                                'source_name' => 'primaryContactEffectiveDate',
                            ],
                            'ga_cease_date' => [
                                'type' => 'date',
                                'label' => 'Cease Date (if applicable)',
                                'rules' => ['nullable', 'date'],
                                'source_name' => 'primaryContactCeaseDate',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

<?php

/**
 * Georgia — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/georgia/application/`
 * (business/generalInformation, business/address, salesAndUseTax/selectAccounts,
 * salesAndUseTax/salesAndUseTaxQuestions, additionalInformation/additionalInfo,
 * additionalInformation/primary) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Georgia Identifiers', 'fields' => [
                    'ga_type_of_llc', 'ga_date_of_incorporation', 'ga_country_of_incorporation',
                    'ga_secondary_naics',
                ]],
                ['title' => 'Sales & Use Tax Registration', 'fields' => [
                    'ga_date_of_first_georgia_sales', 'ga_fiscal_year_end',
                    'ga_accounting_method', 'ga_registering_as_result_from_dor',
                    'ga_letter_id_on_notice', 'ga_motor_fuel_retailer',
                    'ga_motor_fuel_wholesaler', 'ga_four_or_more_locations',
                    'ga_contractor', 'ga_reporting_sales_as_marketplace',
                ]],
                ['title' => 'Tax Account Selections', 'fields' => [
                    'ga_account_sales_and_use_tax', 'ga_account_withholding_tax',
                    'ga_account_corporate_income_tax', 'ga_account_composite_tax',
                    'ga_account_state_hotel_motel_fee', 'ga_account_alcohol_license',
                    'ga_account_tobacco_license', 'ga_account_motor_fuel_distributor_tax',
                    'ga_account_international_fuel_tax', 'ga_account_firework_excise_tax',
                    'ga_account_adult_entertainment_tax', 'ga_account_non_prepaid_911',
                    'ga_account_prepaid_wireless_911', 'ga_account_public_service_commission',
                    'ga_account_public_utilities_and_airlines',
                    'ga_account_qualified_timberland_property', 'ga_account_railroad_equipment',
                    'ga_account_withholding_misc_film',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── GA-specific identifiers ─────────
                    'ga_type_of_llc' => [
                        'type' => 'select',
                        'label' => 'Type of LLC',
                        'options' => [
                            'single_member_disregarded' => 'LLC - Single Member (Disregarded)',
                            'single_member_corp' => 'LLC - Single Member (treated as Corp)',
                            'multi_member_partnership' => 'LLC - Multi-Member (treated as Partnership)',
                            'multi_member_corp' => 'LLC - Multi-Member (treated as Corp)',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                        'source_name' => 'typeOfLLC',
                    ],
                    'ga_date_of_incorporation' => [
                        'type' => 'date',
                        'label' => 'Date of Incorporation',
                        'rules' => ['required', 'date', 'before_or_equal:today'],
                        'source_name' => 'dateOfIncorporation',
                    ],
                    'ga_country_of_incorporation' => [
                        'type' => 'text',
                        'label' => 'Country of Incorporation',
                        'rules' => ['required', 'string', 'max:60'],
                        'placeholder' => 'United States',
                        'source_name' => 'countryOfIncorporation',
                    ],
                    'ga_secondary_naics' => [
                        'type' => 'text',
                        'label' => 'Secondary NAICS Code (if applicable)',
                        'rules' => ['nullable', 'digits:6'],
                        'placeholder' => '123456',
                        'mask' => '999999',
                        'source_name' => 'secondaryNaics',
                    ],

                    // ───────── Sales & Use Tax registration questions ─────────
                    'ga_date_of_first_georgia_sales' => [
                        'type' => 'date',
                        'label' => 'Date of First Georgia Sales',
                        'rules' => ['required', 'date'],
                        'source_name' => 'dateOfFirstGeorgiaSales',
                    ],
                    'ga_fiscal_year_end' => [
                        'type' => 'select',
                        'label' => 'Fiscal Year Ending Month',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'fiscalYearEnd',
                    ],
                    'ga_accounting_method' => [
                        'type' => 'radio',
                        'label' => 'Accounting Method',
                        'options' => ['accrual' => 'Accrual', 'cash' => 'Cash Basis'],
                        'rules' => ['required', 'in:accrual,cash'],
                        'source_name' => 'accountingMethod',
                    ],
                    'ga_registering_as_result_from_dor' => yesNoField('Are you registering as a result of a Georgia DOR notice?', 'registeringAsResultFromDOR', ['drives_conditional' => true]),
                    'ga_letter_id_on_notice' => [
                        'type' => 'text',
                        'label' => 'Letter ID on the Notice',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ga_registering_as_result_from_dor'], '1']],
                        'source_name' => 'letterIdOnNotice',
                    ],
                    'ga_motor_fuel_retailer' => yesNoField('Are you a motor fuel retailer?', 'motorFuelRetailer'),
                    'ga_motor_fuel_wholesaler' => yesNoField('Are you a motor fuel wholesaler?', 'motorFuelWholesaler'),
                    'ga_four_or_more_locations' => yesNoField('Do you operate four or more Georgia locations?', 'fourOrMoreLocationsGeorgia'),
                    'ga_contractor' => yesNoField('Are you a contractor?', 'contractor'),
                    'ga_reporting_sales_as_marketplace' => yesNoField('Will you report sales as a marketplace facilitator?', 'reportingSalesAsMarketplace'),

                    // ───────── Tax account selections (selectAccounts blade) ─────────
                    // Each Yes/No flips on a separate Georgia DOR account registration.
                    'ga_account_sales_and_use_tax' => yesNoField('Sales and Use Tax', 'Sales_And_Use_Tax'),
                    'ga_account_withholding_tax' => yesNoField('Withholding Tax', 'Withholding_Tax'),
                    'ga_account_corporate_income_tax' => yesNoField('Corporate Income Tax', 'Corporate_Income_Tax'),
                    'ga_account_composite_tax' => yesNoField('Composite Tax', 'Composite_Tax'),
                    'ga_account_state_hotel_motel_fee' => yesNoField('State Hotel/Motel Fee', 'State_Hotel_Motel_Fee'),
                    'ga_account_alcohol_license' => yesNoField('Alcohol License', 'Alcohol_License'),
                    'ga_account_tobacco_license' => yesNoField('Tobacco License', 'Tobacco_License'),
                    'ga_account_motor_fuel_distributor_tax' => yesNoField('Motor Fuel Distributor Tax', 'Motor_Fuel_Distributor_Tax'),
                    'ga_account_international_fuel_tax' => yesNoField('International Fuel Tax (IFTA)', 'International_Fuel_Tax'),
                    'ga_account_firework_excise_tax' => yesNoField('Firework Excise Tax', 'Firework_Excise_Tax'),
                    'ga_account_adult_entertainment_tax' => yesNoField('Adult Entertainment Tax', 'Adult_Entertainment_Tax'),
                    'ga_account_non_prepaid_911' => yesNoField('Non-Prepaid 911 Charge', 'Non_Prepaid_911_Charge'),
                    'ga_account_prepaid_wireless_911' => yesNoField('Prepaid Wireless 911 Charge', 'Prepaid_Wireless_911_Charge'),
                    'ga_account_public_service_commission' => yesNoField('Public Service Commission', 'Public_Service_Commision'),
                    'ga_account_public_utilities_and_airlines' => yesNoField('Public Utilities and Airlines', 'Public_Utilities_And_Airlines'),
                    'ga_account_qualified_timberland_property' => yesNoField('Qualified Timberland Property', 'Qualified_Timberland_Property'),
                    'ga_account_railroad_equipment' => yesNoField('Railroad Equipment', 'Railroad_Equipment'),
                    'ga_account_withholding_misc_film' => yesNoField('Withholding Misc/Film', 'Withholding_Misc_Film'),
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ga_owner_type' => [
                                'type' => 'select',
                                'label' => 'Owner Type (Georgia)',
                                'options' => [
                                    'officer' => 'Officer',
                                    'successor' => 'Successor',
                                ],
                                'rules' => ['nullable'],
                                'source_name' => 'ownerType',
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
                            'ga_responsible_for_sales_tax' => [
                                'type' => 'checkbox',
                                'label' => 'Responsible for sales tax compliance',
                                'source_name' => 'primaryContactResponsiblePerson',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

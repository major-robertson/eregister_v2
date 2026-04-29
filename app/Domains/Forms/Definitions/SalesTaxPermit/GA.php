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
                    'ga_registering_as_result_from_dor' => [
                        'type' => 'radio',
                        'label' => 'Are you registering as a result of a Georgia DOR notice?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'registeringAsResultFromDOR',
                    ],
                    'ga_letter_id_on_notice' => [
                        'type' => 'text',
                        'label' => 'Letter ID on the Notice',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ga_registering_as_result_from_dor'], '1']],
                        'source_name' => 'letterIdOnNotice',
                    ],
                    'ga_motor_fuel_retailer' => [
                        'type' => 'radio',
                        'label' => 'Are you a motor fuel retailer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'motorFuelRetailer',
                    ],
                    'ga_motor_fuel_wholesaler' => [
                        'type' => 'radio',
                        'label' => 'Are you a motor fuel wholesaler?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'motorFuelWholesaler',
                    ],
                    'ga_four_or_more_locations' => [
                        'type' => 'radio',
                        'label' => 'Do you operate four or more Georgia locations?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'fourOrMoreLocationsGeorgia',
                    ],
                    'ga_contractor' => [
                        'type' => 'radio',
                        'label' => 'Are you a contractor?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'contractor',
                    ],
                    'ga_reporting_sales_as_marketplace' => [
                        'type' => 'radio',
                        'label' => 'Will you report sales as a marketplace facilitator?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'reportingSalesAsMarketplace',
                    ],

                    // ───────── Tax account selections (selectAccounts blade) ─────────
                    // Each Yes/No flips on a separate Georgia DOR account registration.
                    'ga_account_sales_and_use_tax' => [
                        'type' => 'radio', 'label' => 'Sales and Use Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Sales_And_Use_Tax',
                    ],
                    'ga_account_withholding_tax' => [
                        'type' => 'radio', 'label' => 'Withholding Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Withholding_Tax',
                    ],
                    'ga_account_corporate_income_tax' => [
                        'type' => 'radio', 'label' => 'Corporate Income Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Corporate_Income_Tax',
                    ],
                    'ga_account_composite_tax' => [
                        'type' => 'radio', 'label' => 'Composite Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Composite_Tax',
                    ],
                    'ga_account_state_hotel_motel_fee' => [
                        'type' => 'radio', 'label' => 'State Hotel/Motel Fee',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'State_Hotel_Motel_Fee',
                    ],
                    'ga_account_alcohol_license' => [
                        'type' => 'radio', 'label' => 'Alcohol License',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Alcohol_License',
                    ],
                    'ga_account_tobacco_license' => [
                        'type' => 'radio', 'label' => 'Tobacco License',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Tobacco_License',
                    ],
                    'ga_account_motor_fuel_distributor_tax' => [
                        'type' => 'radio', 'label' => 'Motor Fuel Distributor Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Motor_Fuel_Distributor_Tax',
                    ],
                    'ga_account_international_fuel_tax' => [
                        'type' => 'radio', 'label' => 'International Fuel Tax (IFTA)',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'International_Fuel_Tax',
                    ],
                    'ga_account_firework_excise_tax' => [
                        'type' => 'radio', 'label' => 'Firework Excise Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Firework_Excise_Tax',
                    ],
                    'ga_account_adult_entertainment_tax' => [
                        'type' => 'radio', 'label' => 'Adult Entertainment Tax',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Adult_Entertainment_Tax',
                    ],
                    'ga_account_non_prepaid_911' => [
                        'type' => 'radio', 'label' => 'Non-Prepaid 911 Charge',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Non_Prepaid_911_Charge',
                    ],
                    'ga_account_prepaid_wireless_911' => [
                        'type' => 'radio', 'label' => 'Prepaid Wireless 911 Charge',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Prepaid_Wireless_911_Charge',
                    ],
                    'ga_account_public_service_commission' => [
                        'type' => 'radio', 'label' => 'Public Service Commission',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Public_Service_Commision',
                    ],
                    'ga_account_public_utilities_and_airlines' => [
                        'type' => 'radio', 'label' => 'Public Utilities and Airlines',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Public_Utilities_And_Airlines',
                    ],
                    'ga_account_qualified_timberland_property' => [
                        'type' => 'radio', 'label' => 'Qualified Timberland Property',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Qualified_Timberland_Property',
                    ],
                    'ga_account_railroad_equipment' => [
                        'type' => 'radio', 'label' => 'Railroad Equipment',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Railroad_Equipment',
                    ],
                    'ga_account_withholding_misc_film' => [
                        'type' => 'radio', 'label' => 'Withholding Misc/Film',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'Withholding_Misc_Film',
                    ],
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

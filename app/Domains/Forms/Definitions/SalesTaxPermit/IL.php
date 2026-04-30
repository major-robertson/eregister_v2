<?php

/**
 * Illinois — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/illinois/application/`
 * (primary, organizationInformation, businessInformation, entityQuestions,
 * salesActivity) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── Illinois identifiers ─────────
                    'il_secretary_of_state_number' => [
                        'type' => 'text',
                        'label' => 'IL Secretary of State Number',
                        'rules' => ['nullable', 'regex:/^[Aa]\d{8}$/'],
                        'help' => 'Format: A12345678 (letter A followed by 8 digits).',
                        'source_name' => 'illinoisSecretaryOfStateNumber',
                    ],
                    'il_ssn_or_itin' => [
                        'type' => 'select',
                        'label' => 'For Sole Proprietors: SSN or ITIN?',
                        'options' => ['ssn' => 'SSN', 'itin' => 'ITIN'],
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'drives_conditional' => true,
                        'source_name' => 'ssnOrItin',
                    ],
                    'il_individual_itin' => [
                        'type' => 'text',
                        'label' => 'Individual Taxpayer Identification Number (ITIN)',
                        'rules' => ['nullable', 'regex:/^9\d{2}-?\d{2}-?\d{4}$/'],
                        'when' => ['==' => [['var' => 'il_ssn_or_itin'], 'itin']],
                        'sensitive' => true,
                        'source_name' => 'individualITIN',
                    ],

                    // ───────── Entity-specific ─────────
                    'il_unitary_filing_group' => [
                        'type' => 'radio',
                        'label' => 'Are you part of a unitary filing group?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'drives_conditional' => true,
                        'source_name' => 'unitaryFilingGroup',
                    ],
                    'il_unitary_filing_group_fein' => [
                        'type' => 'text',
                        'label' => 'Unitary Filing Agent FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'il_unitary_filing_group'], '1']],
                        'sensitive' => true,
                        'source_name' => 'unitaryFilingGroupFEIN',
                    ],
                    'il_business_disregarded' => [
                        'type' => 'radio',
                        'label' => 'Is the business a disregarded entity?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'businessDisregarded',
                    ],
                    'il_business_disregarded_fein' => [
                        'type' => 'text',
                        'label' => 'Tax-Reporting Entity FEIN (disregarded entity owner)',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'il_business_disregarded'], '1']],
                        'sensitive' => true,
                        'source_name' => 'businessDisregardedFEIN',
                    ],
                    'il_publicly_traded' => [
                        'type' => 'radio',
                        'label' => 'Is the entity publicly traded?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'drives_conditional' => true,
                        'source_name' => 'publiclyTraded',
                    ],
                    'il_ticker_symbol' => [
                        'type' => 'text',
                        'label' => 'Stock Ticker Symbol',
                        'rules' => ['nullable', 'string', 'max:10'],
                        'when' => ['==' => [['var' => 'il_publicly_traded'], '1']],
                        'source_name' => 'tickerSymbol',
                    ],
                    'il_married_couple' => [
                        'type' => 'radio',
                        'label' => 'Is this business owned by a married couple?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'source_name' => 'marriedCouple',
                    ],

                    // ───────── Income / withholding ─────────
                    'il_liable_for_business_income' => [
                        'type' => 'radio',
                        'label' => 'Are you liable for IL business income tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'liableForBusinessIncome',
                    ],
                    'il_supplier_not_charge_tax_merchandise' => [
                        'type' => 'radio',
                        'label' => 'Do suppliers fail to charge IL tax on merchandise?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'supplierNotChargeTaxMerchandise',
                    ],
                    'il_supplier_not_charge_tax_aviation_fuel' => [
                        'type' => 'radio',
                        'label' => 'Do suppliers fail to charge IL tax on aviation fuel?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                        'source_name' => 'supplierNotChargeTaxAviationFuel',
                    ],
                    'il_not_charge_tax_activities_begin_date' => [
                        'type' => 'date',
                        'label' => 'Date Untaxed Purchase Activities Began',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                        'source_name' => 'notChargeTaxActivitiesBeginDate',
                    ],
                    'il_employees_withholding' => [
                        'type' => 'radio',
                        'label' => 'Will you have IL employees subject to withholding?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'illinoisEmployeesWithholding',
                    ],
                    'il_payroll_begin_date' => [
                        'type' => 'date',
                        'label' => 'IL Payroll Begin Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_employees_withholding'], '1']],
                        'source_name' => 'illinoisPayrollBeginDate',
                    ],

                    // ───────── Sales activity / industry ─────────
                    'il_general_merchandise' => [
                        'type' => 'radio',
                        'label' => 'Sell general merchandise?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'generalMerchandise',
                    ],
                    'il_chicago_soft_drink_tax' => [
                        'type' => 'radio',
                        'label' => 'Subject to Chicago Soft Drink Tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'chicagoSoftDrinkTax',
                    ],
                    'il_cigarettes' => [
                        'type' => 'radio', 'label' => 'Sell cigarettes?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'cigarettes',
                    ],
                    'il_tobacco_products' => [
                        'type' => 'radio', 'label' => 'Sell other tobacco products?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'tobaccoProducts',
                    ],
                    'il_motor_fuel' => [
                        'type' => 'radio', 'label' => 'Sell motor fuel?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'motorFuel',
                    ],
                    'il_aviation_fuel' => [
                        'type' => 'radio', 'label' => 'Sell aviation fuel?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'aviationFuel',
                    ],
                    'il_sell_tires' => [
                        'type' => 'radio', 'label' => 'Sell tires?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellTires',
                    ],
                    'il_from_vending_machines' => [
                        'type' => 'radio', 'label' => 'Sell from vending machines?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'fromVendingMachines',
                    ],
                    'il_how_many_vending_machines' => [
                        'type' => 'text', 'label' => 'How many vending machines?',
                        'rules' => ['nullable', 'integer', 'min:1'],
                        'when' => ['==' => [['var' => 'il_from_vending_machines'], '1']],
                        'source_name' => 'howManyVendingMachines',
                    ],
                    'il_rent_hotel_less_than_30_days' => [
                        'type' => 'radio', 'label' => 'Rent hotel rooms for under 30 days?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'rentHotelLessThan30Days',
                    ],
                    'il_lease_vehicles_more_than_one_year' => [
                        'type' => 'radio', 'label' => 'Lease vehicles for more than one year?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'leaseVehiclesMoreThanOneYear',
                    ],
                    'il_rent_vehicles_less_than_one_year' => [
                        'type' => 'radio', 'label' => 'Rent vehicles for less than one year?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'rentVehiclesLessThanOneYear',
                    ],
                    'il_utility_provider' => [
                        'type' => 'radio', 'label' => 'Are you a utility provider?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'utilityProvider',
                    ],
                    'il_medical_cannabis_cultivator' => [
                        'type' => 'radio', 'label' => 'Medical cannabis cultivator?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'medicalCannabisCultivator',
                    ],
                    'il_medical_cannabis_dispensary' => [
                        'type' => 'radio', 'label' => 'Medical cannabis dispensary?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'medicalCannabisDispensary',
                    ],
                    'il_medical_cannabis_dispensary_begin_date' => [
                        'type' => 'date', 'label' => 'Cannabis Dispensary Begin Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_medical_cannabis_dispensary'], '1']],
                        'source_name' => 'medicalCannabisDispensaryBeginDate',
                    ],

                    // ───────── Remote seller / nexus ─────────
                    'il_sales_from_out_of_state' => [
                        'type' => 'radio', 'label' => 'Sales originate from out of state?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'salesFromOutOfState',
                    ],
                    'il_illinois_presence' => [
                        'type' => 'radio', 'label' => 'Do you have physical presence in Illinois?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'illinoisPresence',
                    ],
                    'il_over_100k' => [
                        'type' => 'radio', 'label' => 'Sales to IL exceed $100,000 per year?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'over100000',
                    ],
                    'il_separate_transactions_over_200' => [
                        'type' => 'radio', 'label' => '200 or more separate IL transactions per year?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'seperateTransactionsOver200',
                    ],

                    // ───────── Liquor at retail (checkbox grid) ─────────
                    'il_liquor_at_retail' => [
                        'type' => 'radio',
                        'label' => 'Sell liquor at retail?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'liquorAtRetail',
                    ],
                    // Liquor place categories — each option becomes its own checkbox
                    'il_liquor_place_restaurant' => ['type' => 'checkbox', 'label' => 'Restaurant', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Restaurant'],
                    'il_liquor_place_bar_or_tavern' => ['type' => 'checkbox', 'label' => 'Bar or Tavern', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Bar/Tavern'],
                    'il_liquor_place_grocery_store' => ['type' => 'checkbox', 'label' => 'Grocery Store', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Grocery Store'],
                    'il_liquor_place_convenience_store' => ['type' => 'checkbox', 'label' => 'Convenience Store', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Convenience Store'],
                    'il_liquor_place_liquor_store' => ['type' => 'checkbox', 'label' => 'Liquor Store', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Liquor Store'],
                    'il_liquor_place_other' => ['type' => 'checkbox', 'label' => 'Other', 'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']], 'source_name' => 'liquorAtRetailPlace[]', 'source_value' => 'Other'],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            // IL enforces ownership total = 100% across all responsible people on submit.
                            // The base ownership_percent field still applies; this note is documentation.
                        ],
                    ],
                ],
            ],
        ],
    ],
];

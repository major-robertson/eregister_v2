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
            'groups' => ['append' => [
                ['title' => 'Illinois Identifiers', 'fields' => [
                    'il_secretary_of_state_number', 'il_ssn_or_itin', 'il_individual_itin',
                ]],
                ['title' => 'Entity-Specific', 'fields' => [
                    'il_unitary_filing_group', 'il_unitary_filing_group_fein',
                    'il_business_disregarded', 'il_business_disregarded_fein',
                    'il_publicly_traded', 'il_ticker_symbol', 'il_married_couple',
                ]],
                ['title' => 'Income & Withholding', 'fields' => [
                    'il_liable_for_business_income',
                    'il_supplier_not_charge_tax_merchandise',
                    'il_supplier_not_charge_tax_aviation_fuel',
                    'il_not_charge_tax_activities_begin_date',
                    'il_employees_withholding', 'il_payroll_begin_date',
                ]],
                ['title' => 'Sales Activity & Industry', 'fields' => [
                    'il_general_merchandise', 'il_chicago_soft_drink_tax', 'il_cigarettes',
                    'il_tobacco_products', 'il_motor_fuel', 'il_aviation_fuel', 'il_sell_tires',
                    'il_from_vending_machines', 'il_how_many_vending_machines',
                    'il_rent_hotel_less_than_30_days', 'il_lease_vehicles_more_than_one_year',
                    'il_rent_vehicles_less_than_one_year', 'il_utility_provider',
                    'il_medical_cannabis_cultivator', 'il_medical_cannabis_dispensary',
                    'il_medical_cannabis_dispensary_begin_date',
                ]],
                ['title' => 'Remote Seller / Nexus', 'fields' => [
                    'il_sales_from_out_of_state', 'il_illinois_presence',
                    'il_over_100k', 'il_separate_transactions_over_200',
                ]],
                ['title' => 'Liquor at Retail', 'fields' => [
                    'il_liquor_at_retail',
                    'il_liquor_place_restaurant', 'il_liquor_place_bar_or_tavern',
                    'il_liquor_place_grocery_store', 'il_liquor_place_convenience_store',
                    'il_liquor_place_liquor_store', 'il_liquor_place_other',
                ]],
            ]],
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
                    'il_unitary_filing_group' => nullableYesNoField('Are you part of a unitary filing group?', 'unitaryFilingGroup', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'drives_conditional' => true,
                    ]),
                    'il_unitary_filing_group_fein' => [
                        'type' => 'text',
                        'label' => 'Unitary Filing Agent FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'il_unitary_filing_group'], '1']],
                        'sensitive' => true,
                        'source_name' => 'unitaryFilingGroupFEIN',
                    ],
                    'il_business_disregarded' => nullableYesNoField('Is the business a disregarded entity?', 'businessDisregarded', ['drives_conditional' => true]),
                    'il_business_disregarded_fein' => [
                        'type' => 'text',
                        'label' => 'Tax-Reporting Entity FEIN (disregarded entity owner)',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'il_business_disregarded'], '1']],
                        'sensitive' => true,
                        'source_name' => 'businessDisregardedFEIN',
                    ],
                    'il_publicly_traded' => nullableYesNoField('Is the entity publicly traded?', 'publiclyTraded', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'drives_conditional' => true,
                    ]),
                    'il_ticker_symbol' => [
                        'type' => 'text',
                        'label' => 'Stock Ticker Symbol',
                        'rules' => ['nullable', 'string', 'max:10'],
                        'when' => ['==' => [['var' => 'il_publicly_traded'], '1']],
                        'source_name' => 'tickerSymbol',
                    ],
                    'il_married_couple' => nullableYesNoField('Is this business owned by a married couple?', 'marriedCouple', [
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                    ]),

                    // ───────── Income / withholding ─────────
                    'il_liable_for_business_income' => yesNoField('Are you liable for IL business income tax?', 'liableForBusinessIncome'),
                    'il_supplier_not_charge_tax_merchandise' => yesNoField('Do suppliers fail to charge IL tax on merchandise?', 'supplierNotChargeTaxMerchandise', ['drives_conditional' => true]),
                    'il_supplier_not_charge_tax_aviation_fuel' => nullableYesNoField('Do suppliers fail to charge IL tax on aviation fuel?', 'supplierNotChargeTaxAviationFuel', [
                        'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                    ]),
                    'il_not_charge_tax_activities_begin_date' => [
                        'type' => 'date',
                        'label' => 'Date Untaxed Purchase Activities Began',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                        'source_name' => 'notChargeTaxActivitiesBeginDate',
                    ],
                    'il_employees_withholding' => yesNoField('Will you have IL employees subject to withholding?', 'illinoisEmployeesWithholding', ['drives_conditional' => true]),
                    'il_payroll_begin_date' => [
                        'type' => 'date',
                        'label' => 'IL Payroll Begin Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_employees_withholding'], '1']],
                        'source_name' => 'illinoisPayrollBeginDate',
                    ],

                    // ───────── Sales activity / industry ─────────
                    'il_general_merchandise' => yesNoField('Sell general merchandise?', 'generalMerchandise'),
                    'il_chicago_soft_drink_tax' => yesNoField('Subject to Chicago Soft Drink Tax?', 'chicagoSoftDrinkTax'),
                    'il_cigarettes' => yesNoField('Sell cigarettes?', 'cigarettes'),
                    'il_tobacco_products' => yesNoField('Sell other tobacco products?', 'tobaccoProducts'),
                    'il_motor_fuel' => yesNoField('Sell motor fuel?', 'motorFuel'),
                    'il_aviation_fuel' => yesNoField('Sell aviation fuel?', 'aviationFuel'),
                    'il_sell_tires' => yesNoField('Sell tires?', 'sellTires'),
                    'il_from_vending_machines' => yesNoField('Sell from vending machines?', 'fromVendingMachines', ['drives_conditional' => true]),
                    'il_how_many_vending_machines' => [
                        'type' => 'text', 'label' => 'How many vending machines?',
                        'rules' => ['nullable', 'integer', 'min:1'],
                        'when' => ['==' => [['var' => 'il_from_vending_machines'], '1']],
                        'source_name' => 'howManyVendingMachines',
                    ],
                    'il_rent_hotel_less_than_30_days' => yesNoField('Rent hotel rooms for under 30 days?', 'rentHotelLessThan30Days'),
                    'il_lease_vehicles_more_than_one_year' => yesNoField('Lease vehicles for more than one year?', 'leaseVehiclesMoreThanOneYear'),
                    'il_rent_vehicles_less_than_one_year' => yesNoField('Rent vehicles for less than one year?', 'rentVehiclesLessThanOneYear'),
                    'il_utility_provider' => yesNoField('Are you a utility provider?', 'utilityProvider'),
                    'il_medical_cannabis_cultivator' => yesNoField('Medical cannabis cultivator?', 'medicalCannabisCultivator'),
                    'il_medical_cannabis_dispensary' => yesNoField('Medical cannabis dispensary?', 'medicalCannabisDispensary', ['drives_conditional' => true]),
                    'il_medical_cannabis_dispensary_begin_date' => [
                        'type' => 'date', 'label' => 'Cannabis Dispensary Begin Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'il_medical_cannabis_dispensary'], '1']],
                        'source_name' => 'medicalCannabisDispensaryBeginDate',
                    ],

                    // ───────── Remote seller / nexus ─────────
                    'il_sales_from_out_of_state' => yesNoField('Sales originate from out of state?', 'salesFromOutOfState'),
                    'il_illinois_presence' => yesNoField('Do you have physical presence in Illinois?', 'illinoisPresence'),
                    'il_over_100k' => yesNoField('Sales to IL exceed $100,000 per year?', 'over100000'),
                    'il_separate_transactions_over_200' => yesNoField('200 or more separate IL transactions per year?', 'seperateTransactionsOver200'),

                    // ───────── Liquor at retail (checkbox grid) ─────────
                    'il_liquor_at_retail' => yesNoField('Sell liquor at retail?', 'liquorAtRetail', ['drives_conditional' => true]),
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

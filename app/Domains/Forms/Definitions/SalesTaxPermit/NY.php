<?php

/**
 * New York — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/newYork/application/`
 * (primary, organizationInformation, businessInformation, entityQuestions,
 * generalQuestions) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Corporation-Specific (S/C corp)', 'fields' => [
                    'ny_shareholder_owns_more_than_50',
                    'ny_more_than_50_first_name', 'ny_more_than_50_last_name',
                    'ny_shareholder_other_corp_tax_owed', 'ny_shareholder_tax_crime',
                    'ny_publicly_traded',
                ]],
                ['title' => 'LLC-Specific', 'fields' => [
                    'ny_llc_member_responsible_for_tax', 'ny_llc_member_owns_more_than_50',
                ]],
                ['title' => 'Banking', 'fields' => [
                    'ny_bank_name', 'ny_routing_number', 'ny_account_number',
                ]],
                ['title' => 'Sales Projections', 'fields' => [
                    'ny_describe_your_business', 'ny_expected_annual_sales',
                    'ny_expected_annual_sales_tax', 'ny_last_date_of_taxable_sales',
                ]],
                ['title' => 'Multi-Location', 'fields' => [
                    'ny_more_than_one_location', 'ny_file_separate_return',
                ]],
                ['title' => 'Tax Assessment / Preparer', 'fields' => [
                    'ny_have_tax_preparer', 'ny_other_tax_id_numbers',
                    'ny_other_tax_id_numbers_value',
                ]],
                ['title' => 'Industry Sales', 'fields' => [
                    'ny_sell_fuel_retail', 'ny_sell_heating_fuels',
                    'ny_passenger_car_rentals', 'ny_sell_diesel_retail',
                    'ny_accept_credit_cards',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── Corporation-specific (S/C corp) ─────────
                    'ny_shareholder_owns_more_than_50' => nullableYesNoField('Does any shareholder own more than 50% of this corporation?', 'shareholderOwnMoreThan50', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['s_corp', 'corporation']]],
                        'drives_conditional' => true,
                    ]),
                    'ny_more_than_50_first_name' => [
                        'type' => 'text',
                        'label' => 'First Name of >50% Shareholder',
                        'rules' => ['nullable', 'string', 'max:60'],
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                        'source_name' => 'moreThan50FullName',
                        'source_note' => 'Legacy form had a single "Full Name" input; split here to match the canonical convention.',
                    ],
                    'ny_more_than_50_last_name' => [
                        'type' => 'text',
                        'label' => 'Last Name of >50% Shareholder',
                        'rules' => ['nullable', 'string', 'max:60'],
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                        'source_name' => 'moreThan50FullName',
                        'source_note' => 'Legacy form had a single "Full Name" input; split here to match the canonical convention.',
                    ],
                    'ny_shareholder_other_corp_tax_owed' => nullableYesNoField('Does the >50% shareholder own a different corporation that owes NY sales tax?', 'shareholderOwnMoreThan50DifferentCorporation', [
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                    ]),
                    'ny_shareholder_tax_crime' => nullableYesNoField('Has the >50% shareholder been convicted of a tax crime in the past year?', 'shareholderTaxCrime', [
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                    ]),
                    'ny_publicly_traded' => nullableYesNoField('Is the entity publicly traded?', 'publiclyTraded', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['s_corp', 'corporation']]],
                    ]),

                    // ───────── LLC-specific ─────────
                    'ny_llc_member_responsible_for_tax' => nullableYesNoField('Is there an LLC member responsible for the tax matters?', 'llcMemberResponsibleForTax', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi', 'llp']]],
                    ]),
                    'ny_llc_member_owns_more_than_50' => nullableYesNoField('Does any LLC member own more than 50%?', 'llcMemberOwnMoreThan50', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi', 'llp']]],
                    ]),

                    // ───────── Banking ─────────
                    'ny_bank_name' => [
                        'type' => 'text',
                        'label' => 'Bank Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'source_name' => 'bankName',
                    ],
                    'ny_routing_number' => [
                        'type' => 'text',
                        'label' => 'Bank Routing Number',
                        'rules' => ['nullable', 'digits:9'],
                        'sensitive' => true,
                        'source_name' => 'routingNumber',
                    ],
                    'ny_account_number' => [
                        'type' => 'text',
                        'label' => 'Bank Account Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'sensitive' => true,
                        'source_name' => 'accountNumber',
                    ],

                    // ───────── Sales projections ─────────
                    'ny_describe_your_business' => [
                        'type' => 'text',
                        'label' => 'Describe Your Business (NY-specific narrative)',
                        'rules' => ['required', 'string', 'min:20', 'max:500'],
                        'source_name' => 'describeYourBusiness',
                    ],
                    'ny_expected_annual_sales' => [
                        'type' => 'text',
                        'label' => 'Expected Annual Sales (USD)',
                        'rules' => ['required', 'numeric', 'min:0'],
                        'source_name' => 'expectedAnnualSales',
                    ],
                    'ny_expected_annual_sales_tax' => [
                        'type' => 'select',
                        'label' => 'Expected Annual Sales Tax',
                        'options' => [
                            'under_3000' => 'Under $3,000',
                            '3000_to_300000' => '$3,000 to $300,000',
                            'over_300000' => 'Over $300,000',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'expectedAnnualSalesTax',
                    ],
                    'ny_last_date_of_taxable_sales' => [
                        'type' => 'date',
                        'label' => 'Last Date of Taxable Sales (if temporary vendor)',
                        'rules' => ['nullable', 'date'],
                        'source_name' => 'lastDateOfTaxableSales',
                    ],

                    // ───────── Multi-location ─────────
                    'ny_more_than_one_location' => yesNoField('Do you have more than one business location in NY?', 'moreThanOneLocation', ['drives_conditional' => true]),
                    'ny_file_separate_return' => nullableYesNoField('Will each location file a separate return?', 'fileSeparateReturn', [
                        'when' => ['==' => [['var' => 'ny_more_than_one_location'], '1']],
                    ]),

                    // ───────── Tax assessment / preparer ─────────
                    'ny_have_tax_preparer' => yesNoField('Do you use a tax preparer?', 'haveTaxPreparer'),
                    'ny_other_tax_id_numbers' => yesNoField('Do you have any other NY tax ID numbers (e.g., from another business)?', 'otherTaxIDNumbers', ['drives_conditional' => true]),
                    'ny_other_tax_id_numbers_value' => [
                        'type' => 'text',
                        'label' => 'Other NY Tax ID Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ny_other_tax_id_numbers'], '1']],
                        'sensitive' => true,
                        'source_name' => 'otherTaxIDNumbersNumber',
                    ],

                    // ───────── Industry sales ─────────
                    'ny_sell_fuel_retail' => yesNoField('Will you sell motor fuel at retail?', 'sellFuelRetail'),
                    'ny_sell_heating_fuels' => yesNoField('Will you sell heating fuels?', 'sellHeatingFuels'),
                    'ny_passenger_car_rentals' => yesNoField('Will you rent passenger cars?', 'passengerCarRentals'),
                    'ny_sell_diesel_retail' => yesNoField('Will you sell diesel fuel at retail?', 'sellDieselRetail'),
                    'ny_accept_credit_cards' => yesNoField('Will you accept credit cards?', 'acceptCreditCards'),
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ny_profit_distribution_percentage' => [
                                'type' => 'percent',
                                'label' => 'Profit Distribution % (NY)',
                                'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                                'help' => 'Distinct from ownership %; NY collects this for each responsible person.',
                                'source_name' => 'primaryContactProfitDistributionPercentage',
                            ],
                            'ny_responsible_person' => [
                                'type' => 'text',
                                'label' => 'Person responsible for sales tax compliance? (yes/no)',
                                'rules' => ['required', 'in:yes,no,Yes,No'],
                                'source_name' => 'primaryContactResponsiblePerson',
                            ],
                            'ny_actively_operating' => [
                                'type' => 'text',
                                'label' => 'Actively operating the business? (yes/no)',
                                'rules' => ['required', 'in:yes,no,Yes,No'],
                                'source_name' => 'primaryContactActivelyOperatingBusiness',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

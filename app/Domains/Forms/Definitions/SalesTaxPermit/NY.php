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
            'fields' => [
                'append' => [
                    // ───────── Corporation-specific (S/C corp) ─────────
                    'ny_shareholder_owns_more_than_50' => [
                        'type' => 'radio',
                        'label' => 'Does any shareholder own more than 50% of this corporation?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['s_corp', 'c_corp']]],
                        'drives_conditional' => true,
                        'source_name' => 'shareholderOwnMoreThan50',
                    ],
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
                    'ny_shareholder_other_corp_tax_owed' => [
                        'type' => 'radio',
                        'label' => 'Does the >50% shareholder own a different corporation that owes NY sales tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                        'source_name' => 'shareholderOwnMoreThan50DifferentCorporation',
                    ],
                    'ny_shareholder_tax_crime' => [
                        'type' => 'radio',
                        'label' => 'Has the >50% shareholder been convicted of a tax crime in the past year?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'ny_shareholder_owns_more_than_50'], '1']],
                        'source_name' => 'shareholderTaxCrime',
                    ],
                    'ny_publicly_traded' => [
                        'type' => 'radio',
                        'label' => 'Is the entity publicly traded?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['s_corp', 'c_corp']]],
                        'source_name' => 'publiclyTraded',
                    ],

                    // ───────── LLC-specific ─────────
                    'ny_llc_member_responsible_for_tax' => [
                        'type' => 'radio',
                        'label' => 'Is there an LLC member responsible for the tax matters?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi', 'llp']]],
                        'source_name' => 'llcMemberResponsibleForTax',
                    ],
                    'ny_llc_member_owns_more_than_50' => [
                        'type' => 'radio',
                        'label' => 'Does any LLC member own more than 50%?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi', 'llp']]],
                        'source_name' => 'llcMemberOwnMoreThan50',
                    ],

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
                    'ny_more_than_one_location' => [
                        'type' => 'radio',
                        'label' => 'Do you have more than one business location in NY?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'moreThanOneLocation',
                    ],
                    'ny_file_separate_return' => [
                        'type' => 'radio',
                        'label' => 'Will each location file a separate return?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'ny_more_than_one_location'], '1']],
                        'source_name' => 'fileSeparateReturn',
                    ],

                    // ───────── Tax assessment / preparer ─────────
                    'ny_have_tax_preparer' => [
                        'type' => 'radio',
                        'label' => 'Do you use a tax preparer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'haveTaxPreparer',
                    ],
                    'ny_other_tax_id_numbers' => [
                        'type' => 'radio',
                        'label' => 'Do you have any other NY tax ID numbers (e.g., from another business)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'otherTaxIDNumbers',
                    ],
                    'ny_other_tax_id_numbers_value' => [
                        'type' => 'text',
                        'label' => 'Other NY Tax ID Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ny_other_tax_id_numbers'], '1']],
                        'sensitive' => true,
                        'source_name' => 'otherTaxIDNumbersNumber',
                    ],

                    // ───────── Industry sales ─────────
                    'ny_sell_fuel_retail' => [
                        'type' => 'radio',
                        'label' => 'Will you sell motor fuel at retail?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellFuelRetail',
                    ],
                    'ny_sell_heating_fuels' => [
                        'type' => 'radio',
                        'label' => 'Will you sell heating fuels?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellHeatingFuels',
                    ],
                    'ny_passenger_car_rentals' => [
                        'type' => 'radio',
                        'label' => 'Will you rent passenger cars?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'passengerCarRentals',
                    ],
                    'ny_sell_diesel_retail' => [
                        'type' => 'radio',
                        'label' => 'Will you sell diesel fuel at retail?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellDieselRetail',
                    ],
                    'ny_accept_credit_cards' => [
                        'type' => 'radio',
                        'label' => 'Will you accept credit cards?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'acceptCreditCards',
                    ],
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

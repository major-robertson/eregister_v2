<?php

/**
 * Oklahoma — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/oklahoma/application/`
 * (primary, organizationInformation, businessInformation, generalQuestions)
 * plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── OK-specific gates ─────────
                    'ok_remote_seller' => [
                        'type' => 'radio',
                        'label' => 'Are you registering as a remote seller?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'help' => 'Remote sellers may be subject to special OK rules.',
                        'source_name' => 'remoteSeller',
                    ],
                    'ok_ship_wine_directly' => [
                        'type' => 'radio',
                        'label' => 'Will you ship wine directly to OK consumers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'shipWineDirectly',
                    ],
                    'ok_ownership_type' => [
                        'type' => 'select',
                        'label' => 'OK Ownership Type',
                        'options' => [
                            'individual' => 'Individual',
                            'married_couple' => 'Married Couple',
                            'partnership' => 'Partnership',
                            'corporation' => 'Corporation',
                            'llc' => 'LLC',
                            'other' => 'Other',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'ownershipType',
                    ],
                    'ok_contractor' => [
                        'type' => 'radio',
                        'label' => 'Are you a contractor?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'contractor',
                    ],
                    'ok_secretary_of_state_number' => [
                        'type' => 'text',
                        'label' => 'OK Secretary of State Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'source_name' => 'secretaryOfStateNumber',
                    ],

                    // ───────── Sales tax account ─────────
                    'ok_make_retail_sales' => [
                        'type' => 'radio',
                        'label' => 'Will you make retail sales in Oklahoma?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'makeRetailSales',
                    ],
                    'ok_date_of_first_sales_for_new_account' => [
                        'type' => 'date',
                        'label' => 'Date of First OK Retail Sales',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'ok_make_retail_sales'], '1']],
                        'source_name' => 'dateOfFirstSalesForNewAccount',
                    ],

                    // ───────── Vendor use / franchise / vending ─────────
                    'ok_need_vendor_use_account' => [
                        'type' => 'radio', 'label' => 'Do you need a Vendor Use account?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'needVendorUseAccount',
                    ],
                    'ok_vendor_use_tax_start_date' => [
                        'type' => 'date', 'label' => 'Vendor Use Tax Start Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'ok_need_vendor_use_account'], '1']],
                        'source_name' => 'vendorUseTaxStartDate',
                    ],
                    'ok_franchise_tax_account' => [
                        'type' => 'radio', 'label' => 'Need a Franchise Tax account?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'franchiseTaxAccount',
                    ],
                    'ok_vending_machine' => [
                        'type' => 'radio', 'label' => 'Operate vending machines?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'vendingMachine',
                    ],

                    // ───────── Withholding ─────────
                    'ok_oklahoma_income_tax_withheld' => [
                        'type' => 'radio',
                        'label' => 'Will you withhold OK income tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'oklahomaIncomeTaxWithheld',
                    ],

                    // ───────── Alcohol ─────────
                    'ok_alcohol_retail' => [
                        'type' => 'radio', 'label' => 'Sell alcohol at retail?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'alcoholRetail',
                    ],
                    'ok_alcohol_wholesale' => [
                        'type' => 'radio', 'label' => 'Sell alcohol at wholesale?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'alcoholWholesale',
                    ],

                    // ───────── Tobacco retailer / agreements ─────────
                    'ok_tobacco_or_cigarette_retail' => [
                        'type' => 'radio',
                        'label' => 'Sell tobacco or cigarettes at retail?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'tobaccoOrCigaretteRetail',
                    ],
                    'ok_tobacco_agreement_one' => [
                        'type' => 'checkbox',
                        'label' => 'I will not sell tobacco products to anyone under 21.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementOne',
                    ],
                    'ok_tobacco_agreement_two' => [
                        'type' => 'checkbox',
                        'label' => 'I will check government-issued ID for all tobacco purchasers under 30.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementTwo',
                    ],
                    'ok_tobacco_agreement_three' => [
                        'type' => 'checkbox',
                        'label' => 'I will display required tobacco signage at the point of sale.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementThree',
                    ],
                    'ok_tobacco_agreement_four' => [
                        'type' => 'checkbox',
                        'label' => 'I will not sell single cigarettes (loose).',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementFour',
                    ],
                    'ok_tobacco_agreement_five' => [
                        'type' => 'checkbox',
                        'label' => 'I will only purchase from licensed wholesalers.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementFive',
                    ],
                    'ok_tobacco_agreement_six' => [
                        'type' => 'checkbox',
                        'label' => 'I will keep tobacco purchase records for the required period.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementSix',
                    ],
                    'ok_tobacco_agreement_seven' => [
                        'type' => 'checkbox',
                        'label' => 'I understand penalties for noncompliance with OK tobacco law.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementSeven',
                    ],
                    'ok_tobacco_agreement_eight' => [
                        'type' => 'checkbox',
                        'label' => 'I will train employees on OK tobacco compliance.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementEight',
                    ],
                    'ok_tobacco_agreement_nine' => [
                        'type' => 'checkbox',
                        'label' => 'I will affix the required Oklahoma tax stamp on all packages.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementNine',
                    ],
                    'ok_tobacco_agreement_ten' => [
                        'type' => 'checkbox',
                        'label' => 'I have read and accept all of the above OK tobacco terms.',
                        'when' => ['==' => [['var' => 'ok_tobacco_or_cigarette_retail'], '1']],
                        'source_name' => 'tobaccoAgreementTen',
                    ],

                    // ───────── Lodging ─────────
                    'ok_lodging_information_city_county' => [
                        'type' => 'text',
                        'label' => 'Lodging City/County',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'source_name' => 'lodgingInformationCityCounty',
                    ],

                    // ───────── Credit card processing ─────────
                    'ok_accepting_credit_or_debit_cards' => [
                        'type' => 'radio',
                        'label' => 'Will you accept credit or debit card payments?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'acceptingCreditOrDebitCards',
                    ],
                    'ok_ssn_or_fein_credit_card' => [
                        'type' => 'text',
                        'label' => 'SSN or FEIN on file with payment processor',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ok_accepting_credit_or_debit_cards'], '1']],
                        'sensitive' => true,
                        'source_name' => 'ssnOrFeinCreditCard',
                    ],

                    // ───────── Main contact (separate from primary) ─────────
                    'ok_contact_first_name' => [
                        'type' => 'text', 'label' => 'Main Contact First Name',
                        'rules' => ['required', 'string', 'max:60'],
                        'source_name' => 'contactFirstName',
                    ],
                    'ok_contact_last_name' => [
                        'type' => 'text', 'label' => 'Main Contact Last Name',
                        'rules' => ['required', 'string', 'max:60'],
                        'source_name' => 'contactLastName',
                    ],
                    'ok_contact_email' => [
                        'type' => 'email', 'label' => 'Main Contact Email',
                        'rules' => ['required', 'email', 'max:255'],
                        'placeholder' => 'name@example.com',
                        'source_name' => 'contactEmail',
                    ],
                    'ok_contact_phone_number' => [
                        'type' => 'text', 'label' => 'Main Contact Phone',
                        'rules' => ['required', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'contactPhoneNumber',
                    ],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ok_id_number' => [
                                'type' => 'text',
                                'label' => 'OK ID Number (SSN, 9 digits)',
                                'rules' => ['required', 'digits:9'],
                                'sensitive' => true,
                                'source_name' => 'primaryContactIdNumber',
                            ],
                            'ok_commence_date' => [
                                'type' => 'date',
                                'label' => 'Date Person Commenced Responsibility',
                                'rules' => ['required', 'date'],
                                'source_name' => 'primaryContactCommenceDate',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

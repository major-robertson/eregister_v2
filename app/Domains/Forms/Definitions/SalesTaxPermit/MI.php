<?php

/**
 * Michigan — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/michigan/application/`
 * (primary, organizationInformation/*, businessInformation/*, generalQuestions/*)
 * plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Reasons for Applying', 'fields' => [
                    'mi_reason_started_new_business', 'mi_reason_hired_employee',
                    'mi_reason_incorporated_existing', 'mi_reason_acquired_transferred',
                    'mi_reason_added_locations', 'mi_reason_peo_client_level',
                    'mi_reason_report_after_total_transfer',
                ]],
                ['title' => 'MI Identifiers', 'fields' => [
                    'mi_business_ownership_type', 'mi_number_of_business_locations_mi',
                    'mi_acquired_employee_units', 'mi_lara_id',
                    'mi_applied_for_corporate_id', 'mi_state_of_incorporation',
                    'mi_date_of_incorporation',
                ]],
                ['title' => 'UIA / Employer', 'fields' => [
                    'mi_registering_for_uia_employer_account',
                    'mi_receive_uia_correspondence_electronically',
                ]],
                ['title' => 'Operations & Fiscal', 'fields' => [
                    'mi_what_products_do_you_sell', 'mi_month_tax_year_ends',
                    'mi_business_opening_month', 'mi_business_closing_month',
                    'mi_employee_leasing_company', 'mi_use_payroll_service',
                    'mi_incorporating_existing_business', 'mi_purchasing_existing_business',
                ]],
                ['title' => 'Addresses', 'fields' => ['mi_legal_address', 'mi_physical_address']],
                ['title' => 'Tax Registrations', 'fields' => [
                    'mi_sales_tax', 'mi_use_tax', 'mi_withholding_tax',
                    'mi_corporate_income_tax', 'mi_flow_through_tax', 'mi_motor_fuel_tax',
                    'mi_ifta_tax', 'mi_tobacco_tax',
                ]],
                ['title' => 'UIA Liability Schedule', 'fields' => [
                    'mi_date_gross_payroll_reaches_1000', 'mi_date_week_20_reached',
                ]],
                ['title' => 'Successorship', 'fields' => [
                    'mi_currently_forming_or_acquiring',
                    'mi_currently_incorporating_existing_business',
                ]],
                ['title' => 'Annual Gross Receipts', 'fields' => ['mi_annual_gross_receipts']],
            ]],
            'fields' => [
                'append' => [
                    // ───────── reasonsForApplying[] (1-7) ─────────
                    'mi_reason_started_new_business' => ['type' => 'checkbox', 'label' => 'Started a New Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '1'],
                    'mi_reason_hired_employee' => ['type' => 'checkbox', 'label' => 'Hired Employee / Hired Michigan Resident', 'source_name' => 'reasonsForApplying[]', 'source_value' => '2'],
                    'mi_reason_incorporated_existing' => ['type' => 'checkbox', 'label' => 'Incorporated / Purchased an Existing Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '3'],
                    'mi_reason_acquired_transferred' => ['type' => 'checkbox', 'label' => 'Acquired / Transferred All / Part of a Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '4'],
                    'mi_reason_added_locations' => ['type' => 'checkbox', 'label' => 'Added a New Location(s)', 'source_name' => 'reasonsForApplying[]', 'source_value' => '5'],
                    'mi_reason_peo_client_level' => ['type' => 'checkbox', 'label' => 'PEO: Client Level Reporting', 'source_name' => 'reasonsForApplying[]', 'source_value' => '6'],
                    'mi_reason_report_after_total_transfer' => ['type' => 'checkbox', 'label' => 'Report Wages After Total Transfer / Sale of Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '7'],

                    // ───────── MI-specific identifiers ─────────
                    'mi_business_ownership_type' => [
                        'type' => 'select',
                        'label' => 'MI Business Ownership Type',
                        'options' => [
                            'individual' => 'Individual / Sole Proprietor',
                            'partnership' => 'Partnership',
                            'corporation' => 'Corporation',
                            'llc' => 'LLC',
                            'fiduciary' => 'Fiduciary / Trust',
                            'government' => 'Government',
                            'other' => 'Other',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'businessOwnershipType',
                    ],
                    'mi_number_of_business_locations_mi' => [
                        'type' => 'text',
                        'label' => 'Number of Business Locations in Michigan',
                        'rules' => ['required', 'integer', 'min:1'],
                        'source_name' => 'numberOfBusinessLocationsMi',
                    ],
                    'mi_acquired_employee_units' => [
                        'type' => 'text',
                        'label' => 'Acquired Employee Units (if applicable)',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'source_name' => 'aquiredEmployeeUnits',
                    ],
                    'mi_lara_id' => [
                        'type' => 'text',
                        'label' => 'LARA ID (Michigan Licensing Authority ID)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi', 'nonprofit']]],
                        'source_name' => 'laraId',
                    ],
                    'mi_applied_for_corporate_id' => nullableYesNoField('Have you applied for a corporate ID?', 'appliedForCorporateId', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi']]],
                    ]),
                    'mi_state_of_incorporation' => [
                        'type' => 'select',
                        'label' => 'State of Incorporation',
                        'options' => array_combine(
                            array_keys(config('states')),
                            array_values(config('states'))
                        ),
                        'rules' => ['nullable', 'size:2'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi', 'nonprofit']]],
                        'source_name' => 'stateOfIncorporation',
                    ],
                    'mi_date_of_incorporation' => [
                        'type' => 'date',
                        'label' => 'Date of Incorporation',
                        'rules' => ['nullable', 'date'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi', 'nonprofit']]],
                        'source_name' => 'dateOfIncorporation',
                    ],

                    // ───────── UIA / employer ─────────
                    'mi_registering_for_uia_employer_account' => yesNoField('Registering for a Michigan UIA Employer Account?', 'registeringForUIAEmployerAccountNumber'),
                    'mi_receive_uia_correspondence_electronically' => yesNoField('Receive UIA correspondence electronically?', 'recieveUIACorrespondenceElectronically'),

                    // ───────── Operations / fiscal ─────────
                    'mi_what_products_do_you_sell' => [
                        'type' => 'text',
                        'label' => 'What products do you sell? (MI-specific)',
                        'rules' => ['required', 'string', 'max:500'],
                        'source_name' => 'whatProductsDoYouSell',
                    ],
                    'mi_month_tax_year_ends' => [
                        'type' => 'select',
                        'label' => 'Month Tax Year Ends',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'monthTaxYearEnds',
                    ],
                    'mi_business_opening_month' => [
                        'type' => 'select',
                        'label' => 'Seasonal Opening Month (if seasonal)',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'source_name' => 'businessOpeningMonth',
                    ],
                    'mi_business_closing_month' => [
                        'type' => 'select',
                        'label' => 'Seasonal Closing Month (if seasonal)',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'source_name' => 'businessClosingMonth',
                    ],
                    'mi_employee_leasing_company' => yesNoField('Are you an employee leasing company?', 'employeeLeasingCompany'),
                    'mi_use_payroll_service' => yesNoField('Will you use a payroll service?', 'usePayrollService'),
                    'mi_incorporating_existing_business' => yesNoField('Are you incorporating an existing business?', 'incorporatingExistingBusiness'),
                    'mi_purchasing_existing_business' => yesNoField('Are you purchasing an existing business?', 'purchasingExistingBusiness'),

                    // ───────── Addresses (MI requires 3) ─────────
                    'mi_legal_address' => [
                        'type' => 'address',
                        'label' => 'MI Legal Address (per LARA registration)',
                        'rules' => ['required'],
                    ],
                    'mi_physical_address' => [
                        'type' => 'address',
                        'label' => 'MI Physical Business Address',
                        'rules' => ['required'],
                    ],

                    // ───────── Tax registrations ─────────
                    'mi_sales_tax' => yesNoField('Register for Sales Tax?', 'salesTax'),
                    'mi_use_tax' => yesNoField('Register for Use Tax?', 'useTax'),
                    'mi_withholding_tax' => yesNoField('Register for Withholding Tax?', 'withholdingTax'),
                    'mi_corporate_income_tax' => yesNoField('Register for Corporate Income Tax?', 'corporateIncomeTax'),
                    'mi_flow_through_tax' => yesNoField('Register for Flow-Through Entity Tax?', 'flowThroughTax'),
                    'mi_motor_fuel_tax' => yesNoField('Register for Motor Fuel Tax?', 'motorFuelTax'),
                    'mi_ifta_tax' => yesNoField('Register for IFTA (International Fuel Tax)?', 'iftaTax'),
                    'mi_tobacco_tax' => yesNoField('Register for Tobacco Tax?', 'tobaccoTax'),

                    // ───────── UIA liability schedule ─────────
                    'mi_date_gross_payroll_reaches_1000' => [
                        'type' => 'date',
                        'label' => 'Date Gross Payroll First Reached $1,000',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'mi_registering_for_uia_employer_account'], '1']],
                        'source_name' => 'dateGrossPayrollReaches1000',
                    ],
                    'mi_date_week_20_reached' => [
                        'type' => 'date',
                        'label' => 'Date 20th Calendar Week of Employment Was Reached',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'mi_registering_for_uia_employer_account'], '1']],
                        'source_name' => 'dateWeek20Reached',
                    ],

                    // ───────── Successorship ─────────
                    'mi_currently_forming_or_acquiring' => yesNoField('Are you currently forming or acquiring a business?', 'currentlyFormingOrAquiringBusiness'),
                    'mi_currently_incorporating_existing_business' => yesNoField('Currently incorporating an existing business?', 'currentlyIncorporatingExistingBusiness'),

                    // ───────── Annual gross receipts ─────────
                    'mi_annual_gross_receipts' => [
                        'type' => 'text',
                        'label' => 'Annual Gross Receipts (USD)',
                        'rules' => ['nullable', 'numeric', 'min:0'],
                        'source_name' => 'annualGrossReceipts',
                    ],
                ],
            ],
        ],
    ],
];

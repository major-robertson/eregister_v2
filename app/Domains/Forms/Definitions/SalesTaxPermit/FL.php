<?php

/**
 * Florida — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/florida/application/`
 * (primary, organizationInformation, businessInformation, entityQuestions,
 * generalQuestions, generalQuestions2) plus matching JS validators.
 *
 * Note: `generalQuestions.blade.php` and `generalQuestions2.blade.php` are
 * effectively the same field set; we deduplicate here.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Florida Identifiers & Fiscal', 'fields' => [
                    'fl_principal_products_or_services', 'fl_number_of_employees',
                    'fl_fiscal_year_ending_month', 'fl_fiscal_year_ending_day', 'fl_services',
                ]],
                ['title' => 'Seasonal Operation', 'fields' => [
                    'fl_only_open_portion_of_year',
                    'fl_first_month_of_open_season', 'fl_last_month_of_open_season',
                ]],
                ['title' => 'DOR History & Tax Warrants', 'fields' => [
                    'fl_business_ever_issued_certificate', 'fl_entity_prior_legal_name',
                    'fl_entity_prior_certificate_number', 'fl_entity_prior_address',
                    'fl_business_tax_warrant', 'fl_owner_tax_warrant',
                    'fl_known_by_another_name', 'fl_previous_name',
                ]],
                ['title' => 'Operations', 'fields' => [
                    'fl_sell_retail', 'fl_sell_nonpermanent_locations', 'fl_repair_equipment',
                    'fl_rent_equipment', 'fl_charge_admission', 'fl_manage_rental',
                    'fl_short_term_rental', 'fl_another_party_manage_property',
                    'fl_improve_property', 'fl_pest_control_nonresidential',
                    'fl_interior_cleaning_nonresidential', 'fl_detective_services',
                    'fl_protection_services', 'fl_alarm_monitoring',
                    'fl_coin_op_machines_other_businesses', 'fl_coin_op_machines_own_business',
                    'fl_food_vending_machines_other_businesses', 'fl_vending_machines_own_business',
                ]],
                ['title' => 'Reemployment Tax (UT)', 'fields' => [
                    'fl_employ_workers_in_fl', 'fl_reactivating_ut_account',
                    'fl_actively_paying_florida_ut', 'fl_domestic_employer',
                    'fl_nonprofit_organization', 'fl_agricultural_employer',
                    'fl_date_first_employ_workers',
                    'fl_disburse_payroll_1500', 'fl_date_disburse_payroll_1500',
                    'fl_employ_workers_for_20_weeks', 'fl_date_employ_workers_for_20_weeks',
                    'fl_rt_account_number',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── Florida-specific identifiers / fiscal ─────────
                    'fl_principal_products_or_services' => [
                        'type' => 'text',
                        'label' => 'Principal Products or Services (FL detail)',
                        'rules' => ['required', 'string', 'max:500'],
                        'help' => 'Florida requires this even though we collected a general business description.',
                        'source_name' => 'principalProductsOrServices',
                    ],
                    'fl_number_of_employees' => [
                        'type' => 'text',
                        'label' => 'Number of Florida Employees',
                        'rules' => ['required', 'integer', 'min:0'],
                        'source_name' => 'numberOfEmployees',
                    ],
                    'fl_fiscal_year_ending_month' => [
                        'type' => 'select',
                        'label' => 'Fiscal Year Ending Month',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'fiscalYearEndingMonth',
                    ],
                    'fl_fiscal_year_ending_day' => [
                        'type' => 'text',
                        'label' => 'Fiscal Year Ending Day (1-31)',
                        'rules' => ['required', 'integer', 'min:1', 'max:31'],
                        'source_name' => 'fiscalYearEndingDay',
                    ],
                    'fl_services' => [
                        'type' => 'select',
                        'label' => 'Type of Services Provided',
                        'options' => [
                            'none' => 'None',
                            'administrative' => 'Administrative',
                            'research' => 'Research',
                            'other' => 'Other',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'services',
                    ],

                    // ───────── Seasonal ─────────
                    'fl_only_open_portion_of_year' => yesNoField('Is this business only open during certain months?', 'onlyOpenPortionOfYear', ['drives_conditional' => true]),
                    'fl_first_month_of_open_season' => [
                        'type' => 'select',
                        'label' => 'First Month of Open Season',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'fl_only_open_portion_of_year'], '1']],
                        'source_name' => 'firstMonthOfOpenSeason',
                    ],
                    'fl_last_month_of_open_season' => [
                        'type' => 'select',
                        'label' => 'Last Month of Open Season',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'fl_only_open_portion_of_year'], '1']],
                        'source_name' => 'lastMonthOfOpenSeason',
                    ],

                    // ───────── DOR history / tax warrants ─────────
                    'fl_business_ever_issued_certificate' => yesNoField('Has this business ever been issued a FL DOR certificate?', 'businessEverIssuedCertificate', ['drives_conditional' => true]),
                    'fl_entity_prior_legal_name' => [
                        'type' => 'text',
                        'label' => 'Prior Legal Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                        'source_name' => 'entityPriorLegalName',
                    ],
                    'fl_entity_prior_certificate_number' => [
                        'type' => 'text',
                        'label' => 'Prior FL Sales Tax Certificate Number',
                        'rules' => ['nullable', 'digits:13'],
                        'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                        'source_name' => 'entityPriorCertificateNumber',
                    ],
                    'fl_entity_prior_address' => [
                        'type' => 'address',
                        'label' => 'Prior Business Address',
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                    ],
                    'fl_business_tax_warrant' => yesNoField('Is there a tax warrant against the business?', 'businessTaxWarrent'),
                    'fl_owner_tax_warrant' => yesNoField('Is there a tax warrant against any owner?', 'ownerTaxWarrent'),
                    'fl_known_by_another_name' => yesNoField('Has the business been known by another name?', 'knownByAnotherName', ['drives_conditional' => true]),
                    'fl_previous_name' => [
                        'type' => 'text',
                        'label' => 'Previous Business Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'fl_known_by_another_name'], '1']],
                        'source_name' => 'previousName',
                    ],

                    // ───────── Operations radios (with matching JS validator coverage) ─────────
                    'fl_sell_retail' => yesNoField('Sell at retail in FL?', 'sellRetail'),
                    'fl_sell_nonpermanent_locations' => yesNoField('Sell at non-permanent locations?', 'sellNonpermanentLocations'),
                    'fl_repair_equipment' => yesNoField('Repair equipment / tangible property?', 'repairEquipment'),
                    'fl_rent_equipment' => yesNoField('Rent equipment / tangible property?', 'rentEquipment'),
                    'fl_charge_admission' => yesNoField('Charge admission to events / venues?', 'chargeAdmission'),
                    'fl_manage_rental' => yesNoField('Manage rental property?', 'manageRental'),
                    'fl_short_term_rental' => yesNoField('Short-term rental (under 6 months)?', 'shortTermRental', ['drives_conditional' => true]),
                    'fl_another_party_manage_property' => nullableYesNoField('Does another party manage the rental property?', 'anotherPartyManageProperty', [
                        'when' => ['==' => [['var' => 'fl_short_term_rental'], '1']],
                    ]),
                    'fl_improve_property' => yesNoField('Improve real property (contractor)?', 'improveProperty'),
                    'fl_pest_control_nonresidential' => yesNoField('Pest control on nonresidential property?', 'pestControlNonresidential'),
                    'fl_interior_cleaning_nonresidential' => yesNoField('Interior cleaning of nonresidential property?', 'interiorCleaningNonresidential'),
                    'fl_detective_services' => yesNoField('Provide detective services?', 'detectiveServices'),
                    'fl_protection_services' => yesNoField('Provide protection services?', 'protectionServices'),
                    'fl_alarm_monitoring' => yesNoField('Provide alarm monitoring services?', 'alarmMonitoringServices'),
                    'fl_coin_op_machines_other_businesses' => yesNoField("Coin-operated machines on others' property?", 'coinOperatedMachinesOtherBusinesses'),
                    'fl_coin_op_machines_own_business' => yesNoField('Coin-operated machines on your own property?', 'coinOperatedMachinesOwnBusinesses'),
                    'fl_food_vending_machines_other_businesses' => yesNoField("Food vending machines on others' property?", 'foodVendingMachinesOtherBusinesses'),
                    'fl_vending_machines_own_business' => yesNoField('Vending machines on your own property?', 'vendingMachinesOwnBusinesses'),

                    // ───────── Reemployment Tax (UT) ─────────
                    'fl_employ_workers_in_fl' => yesNoField('Will you employ workers in Florida?', 'employWorkersInFL', ['drives_conditional' => true]),
                    'fl_reactivating_ut_account' => nullableYesNoField('Reactivating an existing FL UT account?', 'reactivatingUTAccount', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                    ]),
                    'fl_actively_paying_florida_ut' => nullableYesNoField('Actively paying Florida UT?', 'activelyPayingFloridaUT', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                    ]),
                    'fl_domestic_employer' => nullableYesNoField('Are you a domestic employer?', 'domesticEmployer', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                    ]),
                    'fl_nonprofit_organization' => nullableYesNoField('Are you a non-profit organization?', 'nonprofitOrganization', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                    ]),
                    'fl_agricultural_employer' => nullableYesNoField('Are you an agricultural employer?', 'agriculturalEmployer', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                    ]),
                    'fl_date_first_employ_workers' => [
                        'type' => 'date', 'label' => 'Date First Employed Florida Workers',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'dateFirstEmployWorkers',
                    ],
                    'fl_disburse_payroll_1500' => nullableYesNoField('Will you disburse payroll exceeding $1,500 in a calendar quarter?', 'disbursePayroll1500', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'drives_conditional' => true,
                    ]),
                    'fl_date_disburse_payroll_1500' => [
                        'type' => 'date', 'label' => 'Date Payroll Reaches $1,500',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'fl_disburse_payroll_1500'], '1']],
                        'source_name' => 'dateDisbursePayroll1500',
                    ],
                    'fl_employ_workers_for_20_weeks' => nullableYesNoField('Will you employ workers for 20+ weeks in a calendar year?', 'employWorkersFor20Weeks', [
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'drives_conditional' => true,
                    ]),
                    'fl_date_employ_workers_for_20_weeks' => [
                        'type' => 'date',
                        'label' => 'Date 20-Week Employment Threshold Reached',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_for_20_weeks'], '1']],
                        'source_name' => 'dateEmployWorkersFor20Weeks',
                    ],
                    'fl_rt_account_number' => [
                        'type' => 'text',
                        'label' => 'FL Reemployment Tax (RT) Account Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'rtAccountNumber',
                    ],
                ],
            ],
        ],
    ],
];

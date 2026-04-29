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
                    'fl_only_open_portion_of_year' => [
                        'type' => 'radio',
                        'label' => 'Is this business only open during certain months?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'onlyOpenPortionOfYear',
                    ],
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
                    'fl_business_ever_issued_certificate' => [
                        'type' => 'radio',
                        'label' => 'Has this business ever been issued a FL DOR certificate?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'businessEverIssuedCertificate',
                    ],
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
                    'fl_business_tax_warrant' => [
                        'type' => 'radio',
                        'label' => 'Is there a tax warrant against the business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'businessTaxWarrent',
                    ],
                    'fl_owner_tax_warrant' => [
                        'type' => 'radio',
                        'label' => 'Is there a tax warrant against any owner?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'ownerTaxWarrent',
                    ],
                    'fl_known_by_another_name' => [
                        'type' => 'radio',
                        'label' => 'Has the business been known by another name?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'knownByAnotherName',
                    ],
                    'fl_previous_name' => [
                        'type' => 'text',
                        'label' => 'Previous Business Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'fl_known_by_another_name'], '1']],
                        'source_name' => 'previousName',
                    ],

                    // ───────── Operations radios (with matching JS validator coverage) ─────────
                    'fl_sell_retail' => [
                        'type' => 'radio', 'label' => 'Sell at retail in FL?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellRetail',
                    ],
                    'fl_sell_nonpermanent_locations' => [
                        'type' => 'radio', 'label' => 'Sell at non-permanent locations?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellNonpermanentLocations',
                    ],
                    'fl_repair_equipment' => [
                        'type' => 'radio', 'label' => 'Repair equipment / tangible property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'repairEquipment',
                    ],
                    'fl_rent_equipment' => [
                        'type' => 'radio', 'label' => 'Rent equipment / tangible property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'rentEquipment',
                    ],
                    'fl_charge_admission' => [
                        'type' => 'radio', 'label' => 'Charge admission to events / venues?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'chargeAdmission',
                    ],
                    'fl_manage_rental' => [
                        'type' => 'radio', 'label' => 'Manage rental property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'manageRental',
                    ],
                    'fl_short_term_rental' => [
                        'type' => 'radio', 'label' => 'Short-term rental (under 6 months)?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'shortTermRental',
                    ],
                    'fl_another_party_manage_property' => [
                        'type' => 'radio', 'label' => 'Does another party manage the rental property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_short_term_rental'], '1']],
                        'source_name' => 'anotherPartyManageProperty',
                    ],
                    'fl_improve_property' => [
                        'type' => 'radio', 'label' => 'Improve real property (contractor)?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'improveProperty',
                    ],
                    'fl_pest_control_nonresidential' => [
                        'type' => 'radio', 'label' => 'Pest control on nonresidential property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'pestControlNonresidential',
                    ],
                    'fl_interior_cleaning_nonresidential' => [
                        'type' => 'radio', 'label' => 'Interior cleaning of nonresidential property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'interiorCleaningNonresidential',
                    ],
                    'fl_detective_services' => [
                        'type' => 'radio', 'label' => 'Provide detective services?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'detectiveServices',
                    ],
                    'fl_protection_services' => [
                        'type' => 'radio', 'label' => 'Provide protection services?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'protectionServices',
                    ],
                    'fl_alarm_monitoring' => [
                        'type' => 'radio', 'label' => 'Provide alarm monitoring services?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'alarmMonitoringServices',
                    ],
                    'fl_coin_op_machines_other_businesses' => [
                        'type' => 'radio', 'label' => 'Coin-operated machines on others\' property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'coinOperatedMachinesOtherBusinesses',
                    ],
                    'fl_coin_op_machines_own_business' => [
                        'type' => 'radio', 'label' => 'Coin-operated machines on your own property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'coinOperatedMachinesOwnBusinesses',
                    ],
                    'fl_food_vending_machines_other_businesses' => [
                        'type' => 'radio', 'label' => 'Food vending machines on others\' property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'foodVendingMachinesOtherBusinesses',
                    ],
                    'fl_vending_machines_own_business' => [
                        'type' => 'radio', 'label' => 'Vending machines on your own property?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'vendingMachinesOwnBusinesses',
                    ],

                    // ───────── Reemployment Tax (UT) ─────────
                    'fl_employ_workers_in_fl' => [
                        'type' => 'radio',
                        'label' => 'Will you employ workers in Florida?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'employWorkersInFL',
                    ],
                    'fl_reactivating_ut_account' => [
                        'type' => 'radio', 'label' => 'Reactivating an existing FL UT account?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'reactivatingUTAccount',
                    ],
                    'fl_actively_paying_florida_ut' => [
                        'type' => 'radio', 'label' => 'Actively paying Florida UT?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'activelyPayingFloridaUT',
                    ],
                    'fl_domestic_employer' => [
                        'type' => 'radio', 'label' => 'Are you a domestic employer?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'domesticEmployer',
                    ],
                    'fl_nonprofit_organization' => [
                        'type' => 'radio', 'label' => 'Are you a non-profit organization?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'nonprofitOrganization',
                    ],
                    'fl_agricultural_employer' => [
                        'type' => 'radio', 'label' => 'Are you an agricultural employer?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'agriculturalEmployer',
                    ],
                    'fl_date_first_employ_workers' => [
                        'type' => 'date', 'label' => 'Date First Employed Florida Workers',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'source_name' => 'dateFirstEmployWorkers',
                    ],
                    'fl_disburse_payroll_1500' => [
                        'type' => 'radio',
                        'label' => 'Will you disburse payroll exceeding $1,500 in a calendar quarter?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'drives_conditional' => true,
                        'source_name' => 'disbursePayroll1500',
                    ],
                    'fl_date_disburse_payroll_1500' => [
                        'type' => 'date', 'label' => 'Date Payroll Reaches $1,500',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'fl_disburse_payroll_1500'], '1']],
                        'source_name' => 'dateDisbursePayroll1500',
                    ],
                    'fl_employ_workers_for_20_weeks' => [
                        'type' => 'radio',
                        'label' => 'Will you employ workers for 20+ weeks in a calendar year?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'fl_employ_workers_in_fl'], '1']],
                        'drives_conditional' => true,
                        'source_name' => 'employWorkersFor20Weeks',
                    ],
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

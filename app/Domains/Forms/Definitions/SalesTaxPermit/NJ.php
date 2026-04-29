<?php

/**
 * New Jersey — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/newJersey/application/`
 * (primary, organizationInformation, businessInformation, employmentActivity,
 * taxableActivities) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── NJ business identifiers ─────────
                    'nj_business_code' => [
                        'type' => 'text',
                        'label' => 'New Jersey Business Code',
                        'rules' => ['nullable', 'string', 'max:10'],
                        'source_name' => 'njBusinessCode',
                    ],
                    'nj_standard_industrial_code' => [
                        'type' => 'text',
                        'label' => 'Standard Industrial Code (SIC)',
                        'rules' => ['nullable', 'digits:4'],
                        'source_name' => 'standardIndustrialCode',
                    ],
                    'nj_attention_to' => [
                        'type' => 'text',
                        'label' => 'Attention To (mailing)',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'attentionTo',
                    ],
                    'nj_employees_in_state' => [
                        'type' => 'text',
                        'label' => 'Number of New Jersey Employees',
                        'rules' => ['required', 'integer', 'min:0'],
                        'source_name' => 'NewJerseyEmployees',
                    ],
                    'nj_year_round_business' => [
                        'type' => 'radio',
                        'label' => 'Is this a year-round business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'yearRoundBusiness',
                    ],
                    // Months of business — checkbox grid (12 separate fields). Only one
                    // shown for brevity; legacy form posted `MonthsOfBusiness[]` with
                    // values 1..12. Each option becomes its own named field per the
                    // checkbox-grid convention documented in the plan.
                    'nj_month_jan' => ['type' => 'checkbox', 'label' => 'January', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '1'],
                    'nj_month_feb' => ['type' => 'checkbox', 'label' => 'February', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '2'],
                    'nj_month_mar' => ['type' => 'checkbox', 'label' => 'March', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '3'],
                    'nj_month_apr' => ['type' => 'checkbox', 'label' => 'April', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '4'],
                    'nj_month_may' => ['type' => 'checkbox', 'label' => 'May', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '5'],
                    'nj_month_jun' => ['type' => 'checkbox', 'label' => 'June', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '6'],
                    'nj_month_jul' => ['type' => 'checkbox', 'label' => 'July', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '7'],
                    'nj_month_aug' => ['type' => 'checkbox', 'label' => 'August', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '8'],
                    'nj_month_sep' => ['type' => 'checkbox', 'label' => 'September', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '9'],
                    'nj_month_oct' => ['type' => 'checkbox', 'label' => 'October', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '10'],
                    'nj_month_nov' => ['type' => 'checkbox', 'label' => 'November', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '11'],
                    'nj_month_dec' => ['type' => 'checkbox', 'label' => 'December', 'when' => ['==' => [['var' => 'nj_year_round_business'], '0']], 'source_name' => 'MonthsOfBusiness[]', 'source_value' => '12'],

                    // ───────── Org-specific ─────────
                    'nj_resident_out_of_state_partner' => [
                        'type' => 'radio',
                        'label' => 'Are any partners NJ residents but out-of-state for tax purposes?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['general_partnership', 'limited_partnership', 'llp']]],
                        'source_name' => 'NJresidentOutOfStatePartner',
                    ],
                    'nj_subsidiary_of_corporation' => [
                        'type' => 'radio',
                        'label' => 'Is this entity a subsidiary of a corporation?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'subsidiaryOfCorporation',
                    ],
                    'nj_parent_corporation_name' => [
                        'type' => 'text',
                        'label' => 'Parent Corporation Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'nj_subsidiary_of_corporation'], '1']],
                        'source_name' => 'OwnershipTypeNameofParentCorp',
                    ],
                    'nj_parent_corporation_fein' => [
                        'type' => 'text',
                        'label' => 'Parent Corporation FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'nj_subsidiary_of_corporation'], '1']],
                        'sensitive' => true,
                        'source_name' => 'OwnershipTypeFIENofParentCorp',
                    ],
                    'nj_last_month_fiscal_year' => [
                        'type' => 'select',
                        'label' => 'Last Month of Fiscal Year',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'source_name' => 'LastMonthFiscalYear',
                    ],

                    // ───────── Employment activity ─────────
                    'nj_pay_labor' => [
                        'type' => 'radio',
                        'label' => 'Do you pay labor in New Jersey?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'PayLabor',
                    ],
                    'nj_first_pay_date' => [
                        'type' => 'date',
                        'label' => 'First Pay Date in NJ',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'nj_pay_labor'], '1']],
                        'source_name' => 'FirstPayDate',
                    ],
                    'nj_first_nj_hired_date' => [
                        'type' => 'date',
                        'label' => 'First NJ Hire Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'nj_pay_labor'], '1']],
                        'source_name' => 'FirstNjHiredDate',
                    ],
                    'nj_date_pay_exceeds_1k' => [
                        'type' => 'date',
                        'label' => 'Date Quarterly Pay Exceeds $1,000',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'nj_pay_labor'], '1']],
                        'source_name' => 'DatePayExceeds1K',
                    ],
                    'nj_pay_nj_residents_outside' => [
                        'type' => 'radio',
                        'label' => 'Do you pay NJ residents who work outside the state?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'PayNJresidentsOutsideState',
                    ],
                    'nj_pay_pension_or_annuity' => [
                        'type' => 'radio',
                        'label' => 'Do you pay pension or annuity income?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'PayPensionOrAnnuity',
                    ],
                    'nj_more_than_one_employing_facility' => [
                        'type' => 'radio',
                        'label' => 'Do you operate more than one NJ employing facility?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'MoreThanOneEmployingFacility',
                    ],
                    'nj_is_agricultural' => [
                        'type' => 'radio',
                        'label' => 'Is this an agricultural employer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'nj_pay_labor'], '1']],
                        'source_name' => 'IsAgricultural',
                    ],
                    'nj_is_household' => [
                        'type' => 'radio',
                        'label' => 'Is this a household employer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'nj_pay_labor'], '1']],
                        'source_name' => 'IsHouseHold',
                    ],
                    'nj_lease_employees' => [
                        'type' => 'radio',
                        'label' => 'Do you lease employees from a PEO?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'LeaseEmployees',
                    ],
                    'nj_acquired_employee_units' => [
                        'type' => 'radio',
                        'label' => 'Did you acquire employee units from another employer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'AquiredEmployeeUnits',
                    ],
                    'nj_acquired_ein' => [
                        'type' => 'text',
                        'label' => 'Acquired Business FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                        'sensitive' => true,
                        'source_name' => 'AquiredEin',
                    ],
                    'nj_acquired_name' => [
                        'type' => 'text',
                        'label' => 'Acquired Business Legal Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                        'source_name' => 'AquiredName',
                    ],
                    'nj_acquired_date' => [
                        'type' => 'date',
                        'label' => 'Acquisition Date',
                        'rules' => ['nullable', 'date', 'before_or_equal:today'],
                        'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                        'source_name' => 'AquiredDate',
                    ],

                    // ───────── Taxable activities ─────────
                    'nj_collect_or_pay_tax' => [
                        'type' => 'radio',
                        'label' => 'Will you collect or pay NJ sales/use tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'CollectOrPayTax',
                    ],
                    'nj_exempt_purchases' => [
                        'type' => 'radio',
                        'label' => 'Do you make exempt purchases?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'ExemptPurchases',
                    ],
                    'nj_sell_distribute_cigarettes' => [
                        'type' => 'radio',
                        'label' => 'Will you sell or distribute cigarettes?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'SellOrDistributeCigarettes',
                    ],
                    'nj_sell_fuel' => [
                        'type' => 'radio',
                        'label' => 'Will you sell motor fuel?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'SellFuel',
                    ],
                    'nj_hazmat_storage' => [
                        'type' => 'radio',
                        'label' => 'Do you store hazardous materials?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'HazmatStorage',
                    ],
                    'nj_rent_a_car' => [
                        'type' => 'radio',
                        'label' => 'Will you operate a rent-a-car business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'nj_telecom_services' => [
                        'type' => 'radio',
                        'label' => 'Will you provide telecommunications services?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'nj_hotel' => [
                        'type' => 'radio',
                        'label' => 'Will you operate a hotel/motel?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'nj_gambling' => [
                        'type' => 'radio',
                        'label' => 'Will you operate gambling activities?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                ],
            ],
        ],
    ],
];

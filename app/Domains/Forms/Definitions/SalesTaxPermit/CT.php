<?php

/**
 * Connecticut — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/connecticut/application/`
 * (primary, organizationInformation/*, locationInformation/*, generalQuestions/*)
 * plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── CT identifiers ─────────
                    'ct_secretary_of_state_business_id' => [
                        'type' => 'text',
                        'label' => 'CT Secretary of State Business ID',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'source_name' => 'ctSecretaryOfStateBusinessId',
                    ],
                    'ct_disregarded_entity' => [
                        'type' => 'radio',
                        'label' => 'Is this a disregarded entity (single-member LLC)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'llc_single']],
                        'source_name' => 'disregardedEntity',
                    ],

                    // ───────── Taxes/services requested ─────────
                    'ct_taxes_requested_retailer' => ['type' => 'checkbox', 'label' => 'Retailer of goods or services', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '1'],
                    'ct_taxes_requested_room_occupancy' => ['type' => 'checkbox', 'label' => 'Room Occupancy', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '2'],
                    'ct_taxes_requested_corp_business_tax' => ['type' => 'checkbox', 'label' => 'Corporation Business Tax', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '3'],
                    'ct_taxes_requested_pass_through' => ['type' => 'checkbox', 'label' => 'Pass-Through Entity Tax', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '4'],
                    'ct_taxes_requested_other' => ['type' => 'checkbox', 'label' => 'Other', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '5'],

                    'ct_description_business_activity' => [
                        'type' => 'text',
                        'label' => 'Description of Business Activity (CT-specific narrative)',
                        'rules' => ['required', 'string', 'max:500'],
                        'source_name' => 'descriptionBusinessActivity',
                    ],

                    // ───────── Banking ─────────
                    'ct_bank_name' => [
                        'type' => 'text',
                        'label' => 'Bank Name',
                        'rules' => ['required', 'string', 'max:100'],
                        'source_name' => 'bankName',
                    ],
                    'ct_type_of_account' => [
                        'type' => 'radio',
                        'label' => 'Type of Account',
                        'options' => ['1' => 'Checking', '0' => 'Savings'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'typeOfAccount',
                    ],
                    'ct_routing_number' => [
                        'type' => 'text',
                        'label' => 'Bank Routing Number',
                        'rules' => ['required', 'digits:9'],
                        'sensitive' => true,
                        'source_name' => 'routingNumber',
                    ],
                    'ct_checking_number' => [
                        'type' => 'text',
                        'label' => 'Bank Account Number',
                        'rules' => ['required', 'string', 'max:30'],
                        'sensitive' => true,
                        'source_name' => 'checkingNumber',
                    ],

                    // ───────── Withholding ─────────
                    'ct_pay_wages_to_residents' => [
                        'type' => 'radio',
                        'label' => 'Will you pay wages to CT resident employees?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'payWagesToResidentEmployees',
                    ],
                    'ct_out_of_state_withholding_ct_income_tax' => [
                        'type' => 'radio',
                        'label' => 'Withhold CT income tax from out-of-state employees?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'outOfStateWithholdingCtIncomeTax',
                    ],
                    'ct_payments_to_pensions_annuities' => [
                        'type' => 'radio',
                        'label' => 'Make payments to pensions, annuities, or retirement distributions?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'paymentsToPensionsAnnuitiesRetriementDistributions',
                    ],
                    'ct_pay_nonresident_athletes' => [
                        'type' => 'radio',
                        'label' => 'Pay nonresident athletes or entertainers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'payNonresidentAthletesOrEntertainers',
                    ],
                    'ct_household_employee_withholding' => [
                        'type' => 'radio',
                        'label' => 'Household employee with CT income tax withholding?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'haveHouseholdEmployeeAndWithholdCtIncomeTax',
                    ],
                    'ct_agricultural_employee_withholding' => [
                        'type' => 'radio',
                        'label' => 'Agricultural employee with CT income tax withholding?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'haveAgriculturalEmployeeAndWithholdCtIncomeTax',
                    ],
                    'ct_file_agriculture_forms_annually' => [
                        'type' => 'radio',
                        'label' => 'File agriculture forms annually?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'source_name' => 'fileAgricultureFormsAnnually',
                    ],
                    'ct_tax_registration_number' => [
                        'type' => 'text',
                        'label' => 'CT Tax Registration Number (if previously assigned)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'source_name' => 'ctTaxRegistrationNumber',
                    ],
                    'ct_income_tax_withholding_payroll_service' => [
                        'type' => 'radio',
                        'label' => 'Use a payroll service for CT withholding?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'source_name' => 'incomeTaxWithholdingPayrollService',
                    ],
                    'ct_withholding_liability_start_date' => [
                        'type' => 'date',
                        'label' => 'CT Income Tax Withholding Liability Start Date',
                        'rules' => ['nullable', 'date'],
                        'source_name' => 'incomeTaxWithholdingLiabilityStartDate',
                    ],

                    // ───────── Sales & Use ─────────
                    'ct_selling_goods_in_ct' => [
                        'type' => 'radio', 'label' => 'Selling goods in Connecticut?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingGoodsInCt',
                    ],
                    'ct_rent_equipment_to_individuals' => [
                        'type' => 'radio', 'label' => 'Rent equipment to individuals or businesses in CT?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'rentEquipmentToIndividualsOrBusinessesInCt',
                    ],
                    'ct_serving_meals_or_beverages' => [
                        'type' => 'radio', 'label' => 'Serving meals or beverages in CT?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'servingMealsOrBeveragesInCt',
                    ],
                    'ct_providing_taxable_services' => [
                        'type' => 'radio', 'label' => 'Providing taxable services in CT?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'providingTaxableServicesInCt',
                    ],
                    'ct_only_through_marketplace' => [
                        'type' => 'radio', 'label' => 'Selling only through marketplace facilitators?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingOnlyThroughMarketPlaceFacilitators',
                    ],
                    'ct_sales_tax_liability_start_date' => [
                        'type' => 'date',
                        'label' => 'Sales Tax Liability Start Date',
                        'rules' => ['required', 'date'],
                        'source_name' => 'salesTaxLiabilityStartDate',
                    ],

                    // ───────── Admissions / dues ─────────
                    'ct_amusement_entertainment' => [
                        'type' => 'radio',
                        'label' => 'Operate an amusement, entertainment, or recreation venue in CT?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'operateAmusmentEntertainmentOrRecreationPlaceInCt',
                    ],
                    'ct_social_athletic_dues' => [
                        'type' => 'radio',
                        'label' => 'Social/athletic/sporting club with >$100 annual dues?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'socialAthleticOrSportingWithMoreThan100InDuesAnnually',
                    ],
                    'ct_social_athletic_initiation' => [
                        'type' => 'radio',
                        'label' => 'Social/athletic/sporting club with >$100 initiation fees?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'socialAthleticOrSportingWithMoreThan100InitiationFees',
                    ],
                    'ct_when_business_is_active' => [
                        'type' => 'radio',
                        'label' => 'When is the business active?',
                        'options' => [
                            '0' => 'All Year',
                            '1' => 'Seasonal',
                            '2' => 'One Time',
                        ],
                        'rules' => ['required', 'in:0,1,2'],
                        'drives_conditional' => true,
                        'source_name' => 'whenBusinessIsActive',
                    ],
                    // monthsBusinessIsActive[] grid (12 separate fields)
                    'ct_month_jan' => ['type' => 'checkbox', 'label' => 'January', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '1'],
                    'ct_month_feb' => ['type' => 'checkbox', 'label' => 'February', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '2'],
                    'ct_month_mar' => ['type' => 'checkbox', 'label' => 'March', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '3'],
                    'ct_month_apr' => ['type' => 'checkbox', 'label' => 'April', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '4'],
                    'ct_month_may' => ['type' => 'checkbox', 'label' => 'May', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '5'],
                    'ct_month_jun' => ['type' => 'checkbox', 'label' => 'June', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '6'],
                    'ct_month_jul' => ['type' => 'checkbox', 'label' => 'July', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '7'],
                    'ct_month_aug' => ['type' => 'checkbox', 'label' => 'August', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '8'],
                    'ct_month_sep' => ['type' => 'checkbox', 'label' => 'September', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '9'],
                    'ct_month_oct' => ['type' => 'checkbox', 'label' => 'October', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '10'],
                    'ct_month_nov' => ['type' => 'checkbox', 'label' => 'November', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '11'],
                    'ct_month_dec' => ['type' => 'checkbox', 'label' => 'December', 'when' => ['==' => [['var' => 'ct_when_business_is_active'], '1']], 'source_name' => 'monthsBusinessIsActive[]', 'source_value' => '12'],
                    'ct_admissions_dues_liability_start_date' => [
                        'type' => 'date',
                        'label' => 'Admissions / Dues Tax Liability Start Date',
                        'rules' => ['nullable', 'date'],
                        'source_name' => 'admissionsAndDueTaxLiabilityStartDate',
                    ],

                    // ───────── Corporation Business Tax ─────────
                    'ct_corp_taxed_as_corp_with_nexus' => [
                        'type' => 'radio',
                        'label' => 'Is the corporation/association taxed as a corporation with CT nexus?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['c_corp', 's_corp']]],
                        'source_name' => 'coporationOrAssociationTaxedAsCorpWithNexus',
                    ],
                    'ct_federal_corp_income_tax_exemption' => [
                        'type' => 'radio',
                        'label' => 'Federal corporate income tax exempt?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['c_corp', 's_corp', 'nonprofit']]],
                        'source_name' => 'federalCorporateIncomeTaxExemption',
                    ],
                    'ct_state_organized_under' => [
                        'type' => 'select',
                        'label' => 'State Organized Under',
                        'options' => array_combine(
                            array_keys(config('states')),
                            array_values(config('states'))
                        ),
                        'rules' => ['nullable', 'size:2'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['c_corp', 's_corp']]],
                        'source_name' => 'stateOrganizedUnder',
                    ],
                    'ct_month_corporation_year_closes' => [
                        'type' => 'select',
                        'label' => 'Corporation Fiscal Year Ends',
                        'options' => [
                            '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                            '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                            '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['c_corp', 's_corp']]],
                        'source_name' => 'monthCorporationYearCloses',
                    ],

                    // ───────── Unrelated business / use tax ─────────
                    'ct_purchasing_taxable_without_paying_ct_tax' => [
                        'type' => 'radio',
                        'label' => 'Will you purchase taxable goods/services without paying CT sales tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'purchasingTaxableGoodsOrServiceswWithoutPayingCtSalesTax',
                    ],
                    'ct_unrelated_business_income_tax_liability_start_date' => [
                        'type' => 'date',
                        'label' => 'Unrelated Business Income Tax Liability Start Date',
                        'rules' => ['nullable', 'date'],
                        'source_name' => 'unrealtedBusinessIncomeTaxLibailityStartDate',
                    ],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ct_middle_initial' => [
                                'type' => 'text',
                                'label' => 'Middle Initial',
                                'rules' => ['nullable', 'string', 'max:1'],
                                'source_name' => 'primaryContactMiddleInitial',
                            ],
                            'ct_bank_name_per_person' => [
                                'type' => 'text',
                                'label' => 'Bank Name (CT collects per responsible person)',
                                'rules' => ['nullable', 'string', 'max:100'],
                                'source_name' => 'primaryContactBankName',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

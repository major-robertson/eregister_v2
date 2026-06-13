<?php

/**
 * Michigan — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/michigan/application/` (registrationReasons,
 * liabilityQuestions, corporationInformation, businessInformation,
 * generalQuestions partials, agreements).
 *
 * Collapsed into core: DBA / fax / fiscal-year month / products narrative
 * (core), employee leasing + payroll service gates (applies_payroll_
 * service_or_peo), purchasing existing business (applies_purchased...),
 * incorporation date + state (core / formation_state), legal + physical
 * addresses (locations[] rows), location count (derived).
 *
 * §3A.2 fix applied: businessOwnershipType restored to the legacy 44-option
 * list; annual gross receipts restored to the legacy Y/N + begin date + EFT
 * (replaces v2's invented numeric field).
 */
$miGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'MI']];

$miOwnershipTypes = [
    'Administration', 'Agency', 'Any Other Type of Business', 'Any Other Type of Partnership',
    'Association', 'Authority', 'Board', 'Bureau', 'Catholic School', 'City', 'College',
    'Commission', 'Consortium', 'Council', 'County', 'Court', 'Department',
    'Employee Leasing Company', 'Estate', 'Fund', 'Hospital', 'Husband/Wife Proprietorship',
    'Indian Tribal Unit', 'Individual/Sole Proprietorship', 'Joint Stock Club',
    'Limited Partnership (LP)', 'LLC - Files Fed Tax as C Corp',
    'LLC - Files Fed Tax as Disregarded Entity', 'LLC - Files Fed Tax as Partnership',
    'LLC - Files Fed Tax as S Corp', 'LLC - Files Fed Tax as Sole Proprietor',
    'Michigan Corporation - Files Fed Tax as C Corp', 'Michigan Corporation - Files Fed Tax as S Corp',
    'Non-Michigan Corporation - Files Fed Tax as C Corp', 'Non-Michigan Corporation - Files Fed Tax as S Corp',
    'Other Jurisdiction', 'Professional Employer Organization', 'Professional Management Organization',
    'School District', 'Social Club or Fraternal Organization', 'Township',
    'Trust or Estate (Fiduciary)', 'Union', 'Village',
];

$miPaymentTiers = [
    'over_300' => 'Over $300',
    'up_to_300' => 'Up To $300',
    'up_to_65' => 'Up to $65',
];

// Per-tax registration block builder: gate + begin date + (optional)
// estimated tier + EFT question, mirroring the legacy generalQuestions
// partials one-to-one.
$miTaxBlock = function (string $prefix, string $gateLabel, string $gateSource, string $dateSource, ?string $tierSource, string $eftSource) use ($miPaymentTiers): array {
    $fields = [
        "{$prefix}" => yesNoField($gateLabel, $gateSource, ['drives_conditional' => true]),
        "{$prefix}_begin_date" => [
            'type' => 'date',
            'label' => 'Liability Begin Date',
            'rules' => ['nullable', 'date'],
            'when' => ['==' => [['var' => $prefix], '1']],
            'source_name' => $dateSource,
        ],
    ];

    if ($tierSource !== null) {
        $fields["{$prefix}_estimated"] = [
            'type' => 'select',
            'label' => 'Estimated monthly payment',
            'options' => $miPaymentTiers,
            'rules' => ['nullable'],
            'when' => ['==' => [['var' => $prefix], '1']],
            'source_name' => $tierSource,
        ];
    }

    $fields["{$prefix}_electronic_payments"] = nullableYesNoField('Will you be making payments electronically (EFT/ACH)?', $eftSource, [
        'when' => ['==' => [['var' => $prefix], '1']],
    ]);

    return $fields;
};

return [
    'extends' => 'base',

    'state_steps' => [
        'mi_reasons_and_classification' => [
            'title' => 'Michigan Registration Reasons',
            'description' => 'Why you are registering and how Michigan classifies the business.',
            'groups' => [
                ['title' => 'Reasons for Applying', 'fields' => [
                    'mi_reason_started_new_business', 'mi_reason_hired_employee',
                    'mi_reason_incorporated_existing', 'mi_reason_acquired_transferred',
                    'mi_reason_added_locations', 'mi_reason_peo_client_level',
                    'mi_reason_report_after_total_transfer',
                ]],
                ['title' => 'Classification & IDs', 'fields' => [
                    'mi_business_ownership_type', 'mi_lara_id', 'mi_applied_for_corporate_id',
                    'mi_acquired_employee_units',
                ]],
            ],
            'fields' => [
                'mi_reason_started_new_business' => ['type' => 'checkbox', 'label' => 'Started a New Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '1'],
                'mi_reason_hired_employee' => ['type' => 'checkbox', 'label' => 'Hired Employee / Hired Michigan Resident', 'source_name' => 'reasonsForApplying[]', 'source_value' => '2'],
                'mi_reason_incorporated_existing' => ['type' => 'checkbox', 'label' => 'Incorporated / Purchased an Existing Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '3'],
                'mi_reason_acquired_transferred' => ['type' => 'checkbox', 'label' => 'Acquired / Transferred All or Part of a Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '4'],
                'mi_reason_added_locations' => ['type' => 'checkbox', 'label' => 'Added a New Location(s)', 'source_name' => 'reasonsForApplying[]', 'source_value' => '5'],
                'mi_reason_peo_client_level' => ['type' => 'checkbox', 'label' => 'PEO: Client Level Reporting', 'source_name' => 'reasonsForApplying[]', 'source_value' => '6'],
                'mi_reason_report_after_total_transfer' => ['type' => 'checkbox', 'label' => 'Report Wages After Total Transfer / Sale of Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => '7'],

                'mi_business_ownership_type' => [
                    'type' => 'select',
                    'label' => 'MI Business Ownership Type',
                    'options' => array_combine($miOwnershipTypes, $miOwnershipTypes),
                    'rules' => ['required'],
                    'source_name' => 'businessOwnershipType',
                ],
                'mi_lara_id' => [
                    'type' => 'text',
                    'label' => 'LARA Corporate ID Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi', 'nonprofit']]],
                    'source_name' => 'laraId',
                ],
                'mi_applied_for_corporate_id' => nullableYesNoField('If you do not have a Corporate ID Number, did you apply for one?', 'appliedForCorporateId', [
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'llc_single', 'llc_multi']]],
                ]),
                'mi_acquired_employee_units' => nullableYesNoField('Did you acquire substantially all the assets of another business?', 'aquiredEmployeeUnits'),
            ],
        ],

        'mi_tax_accounts' => [
            'title' => 'Michigan Tax Registrations',
            'description' => 'Each Michigan tax you register for has its own begin date and payment questions.',
            'groups' => [
                ['title' => 'Sales Tax', 'fields' => ['mi_sales_tax', 'mi_sales_tax_begin_date', 'mi_sales_tax_estimated', 'mi_sales_tax_electronic_payments']],
                ['title' => 'Use Tax', 'fields' => ['mi_use_tax', 'mi_use_tax_begin_date', 'mi_use_tax_estimated', 'mi_use_tax_electronic_payments']],
                ['title' => 'Withholding', 'fields' => ['mi_withholding', 'mi_withholding_begin_date', 'mi_withholding_estimated', 'mi_withholding_electronic_payments']],
                ['title' => 'Flow-Through Withholding', 'fields' => ['mi_flow_through', 'mi_flow_through_begin_date', 'mi_flow_through_electronic_payments']],
                ['title' => 'Tobacco Tax', 'fields' => [
                    'mi_tobacco_tax', 'mi_tobacco_tax_begin_date', 'mi_tobacco_tax_electronic_payments',
                    'mi_sell_tobacco_to_resellers', 'mi_purchase_tobacco_out_of_state',
                    'mi_operate_tobacco_vending_machine', 'mi_supply_tobacco_for_machine',
                    'mi_tobacco_supplier_name',
                ]],
                ['title' => 'Motor Fuel Tax', 'fields' => [
                    'mi_motor_fuel_tax', 'mi_motor_fuel_tax_begin_date', 'mi_motor_fuel_tax_electronic_payments',
                    'mi_operate_terminal_or_refinery', 'mi_transport_fuel_across_borders',
                ]],
                ['title' => 'IFTA', 'fields' => [
                    'mi_ifta_tax', 'mi_ifta_tax_begin_date', 'mi_ifta_tax_electronic_payments',
                    'mi_diesel_over_26000_pounds', 'mi_ifta_transport_fuel',
                ]],
                ['title' => 'Annual Gross Receipts', 'fields' => [
                    'mi_gross_receipts_over_350000', 'mi_gross_receipts_over_350000_begin_date',
                    'mi_gross_receipts_over_350000_electronic_payments',
                ]],
            ],
            'fields' => array_merge(
                $miTaxBlock('mi_sales_tax', 'Are you registering for sales tax?', 'registeringForSalesTax', 'salesTaxBeginDate', 'salesTaxEstimated', 'salesTaxElectronicPayments'),
                $miTaxBlock('mi_use_tax', 'Are you registering for use tax?', 'registeringForUseTax', 'useTaxBeginDate', 'useTaxEstimated', 'useTaxElectronicPayments'),
                $miTaxBlock('mi_withholding', 'Are you registering for employer and retirement withholding?', 'registeringForWitholding', 'witholdingBeginDate', 'incomeTaxEstimated', 'witholdingPaymentsElectronically'),
                $miTaxBlock('mi_flow_through', 'Are you registering for flow-through withholding tax?', 'registeringForFlowThroughWitholding', 'flowThroughBeginDate', null, 'flowThroughElectronicPayments'),
                $miTaxBlock('mi_tobacco_tax', 'Are you registering for tobacco tax?', 'registeringForTobaccoTax', 'tobaccoTaxBeginDate', null, 'tobaccoElectronicPayments'),
                [
                    'mi_sell_tobacco_to_resellers' => nullableYesNoField('Will you sell tobacco products to someone who will offer them for sale?', 'sellTobaccoToSomeoneWhoSells', [
                        'when' => ['==' => [['var' => 'mi_tobacco_tax'], '1']],
                    ]),
                    'mi_purchase_tobacco_out_of_state' => nullableYesNoField('Will you purchase tobacco products from an out-of-state or unlicensed source?', 'purchaseFromOutOfStateOrUnlicensed', [
                        'when' => ['==' => [['var' => 'mi_tobacco_tax'], '1']],
                    ]),
                    'mi_operate_tobacco_vending_machine' => nullableYesNoField('Will you operate a tobacco products vending machine?', 'operateTobaccoProducts', [
                        'when' => ['==' => [['var' => 'mi_tobacco_tax'], '1']],
                        'drives_conditional' => true,
                    ]),
                    'mi_supply_tobacco_for_machine' => nullableYesNoField('Do you supply the tobacco products for the machine?', 'supplyTobaccoProductsForMachine', [
                        'when' => ['==' => [['var' => 'mi_operate_tobacco_vending_machine'], '1']],
                    ]),
                    'mi_tobacco_supplier_name' => [
                        'type' => 'text',
                        'label' => 'Name of tobacco supplier',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'mi_operate_tobacco_vending_machine'], '1']],
                        'source_name' => 'nameOfSupplier',
                    ],
                ],
                $miTaxBlock('mi_motor_fuel_tax', 'Are you registering for motor fuel tax?', 'registeringForMotorOil', 'motorFuelBeginDate', null, 'fuelPaymentsElectronically'),
                [
                    'mi_operate_terminal_or_refinery' => nullableYesNoField('Will you operate a terminal or refinery?', 'operateTerminalOrRefinery', [
                        'when' => ['==' => [['var' => 'mi_motor_fuel_tax'], '1']],
                    ]),
                    'mi_transport_fuel_across_borders' => nullableYesNoField('Will you transport fuel across Michigan borders?', 'transportFuelAcrossBorder', [
                        'when' => ['==' => [['var' => 'mi_motor_fuel_tax'], '1']],
                    ]),
                ],
                $miTaxBlock('mi_ifta_tax', 'Are you registering for IFTA tax?', 'iftaTax', 'iftaBeginDate', null, 'iftaElectronicPayments'),
                [
                    'mi_diesel_over_26000_pounds' => nullableYesNoField('Do you own a diesel-powered vehicle over 26,000 pounds?', 'ownDieselPoweredVehicleOver26000Pounds', [
                        'when' => ['==' => [['var' => 'mi_ifta_tax'], '1']],
                    ]),
                    'mi_ifta_transport_fuel' => nullableYesNoField('Will you transport fuel across Michigan borders?', 'iftaTransportFuel', [
                        'when' => ['==' => [['var' => 'mi_ifta_tax'], '1']],
                    ]),
                ],
                $miTaxBlock('mi_gross_receipts_over_350000', 'Are the annual gross receipts over $350,000?', 'annualGrossReceiptsOver350000', 'annualGrossReceiptsBeginDate', null, 'annualGrossReceiptsElectronicPayments'),
            ),
        ],

        'mi_uia_payroll' => [
            'title' => 'Michigan UIA & Payroll',
            'description' => 'Shown because employees/payroll applies to Michigan.',
            'groups' => [
                ['title' => 'UIA Registration', 'fields' => [
                    'mi_registering_for_uia', 'mi_uia_correspondence_electronically',
                    'mi_first_date_employing_anyone', 'mi_item_best_describes_business', 'mi_employer_type',
                ]],
                ['title' => 'Liability Thresholds', 'fields' => [
                    'mi_date_gross_payroll_reaches_1000', 'mi_date_week_20_reached',
                    'mi_date_20000_reached', 'mi_date_week_20_reached_10_agricultural',
                    'mi_cash_payroll_of_1000_domestic',
                ]],
            ],
            'fields' => [
                'mi_registering_for_uia' => nullableYesNoField('Are you registering for a UIA Employer Account Number?', 'registeringForUIAEmployerAccountNumber', [
                    'when' => $miGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'mi_uia_correspondence_electronically' => nullableYesNoField('Would you like to receive UIA correspondence electronically?', 'recieveUIACorrespondenceElectronically', [
                    'when' => ['==' => [['var' => 'mi_registering_for_uia'], '1']],
                ]),
                'mi_first_date_employing_anyone' => [
                    'type' => 'date',
                    'label' => 'On what date did/will you first employ anyone in Michigan?',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_registering_for_uia'], '1']],
                    'source_name' => 'firstDateEmployingAnyoneInMi',
                ],
                'mi_item_best_describes_business' => [
                    'type' => 'select',
                    'label' => 'Choose the item that best describes your business',
                    'options' => [
                        'section_1' => 'General business employer (Section 1)',
                        'section_2' => 'Agricultural employer (Section 2)',
                        'section_3' => 'Domestic/household employer (Section 3)',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'mi_registering_for_uia'], '1']],
                    'drives_conditional' => true,
                    'source_name' => 'itemThatBestDescribesBusiness',
                ],
                'mi_employer_type' => [
                    'type' => 'select',
                    'label' => 'Select an employer type (if applicable)',
                    'options' => [
                        'nonprofit' => 'Nonprofit Employers',
                        'governmental' => 'Governmental / Tribes / Tribal',
                        'futa' => 'FUTA Subjectivity',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_1']],
                    'source_name' => 'employerType',
                ],
                'mi_date_gross_payroll_reaches_1000' => [
                    'type' => 'date',
                    'label' => 'Date gross payroll of $1,000 was (or will be) reached',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_1']],
                    'source_name' => 'dateGrossPayrollReaches1000',
                ],
                'mi_date_week_20_reached' => [
                    'type' => 'date',
                    'label' => 'Date the 20th calendar week of employment was (or will be) reached',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_1']],
                    'source_name' => 'dateWeek20Reached',
                ],
                'mi_date_20000_reached' => [
                    'type' => 'date',
                    'label' => 'Date a total cash payroll of $20,000 (agricultural) was reached',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_2']],
                    'source_name' => 'date20000Reached',
                ],
                'mi_date_week_20_reached_10_agricultural' => [
                    'type' => 'date',
                    'label' => 'Date you had at least 10 agricultural workers in the 20th week',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_2']],
                    'source_name' => 'dateWeek20Reached10WorkersAgricultural',
                ],
                'mi_cash_payroll_of_1000_domestic' => [
                    'type' => 'date',
                    'label' => 'Date a cash payroll of $1,000 (domestic) was reached',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'mi_item_best_describes_business'], 'section_3']],
                    'source_name' => 'cashPayrollOf1000OrMore',
                ],
            ],
        ],

        'mi_seasonal_and_history' => [
            'title' => 'Michigan Season & Successorship',
            'description' => 'Seasonal employer months and business formation history.',
            'groups' => [
                ['title' => 'Seasonal Employer', 'fields' => [
                    'mi_seasonal_employer', ['mi_business_opening_month', 'mi_business_closing_month'],
                ]],
                ['title' => 'Employee Leasing', 'fields' => ['mi_employee_leasing_license_number']],
                ['title' => 'Successorship', 'fields' => [
                    'mi_formed_acquired_merged_past_6_years', 'mi_how_many_formed_acquired_merged',
                    'mi_how_many_forming_or_acquiring', 'mi_how_many_incorporating',
                    'mi_currently_merging', 'mi_how_many_merging', 'mi_intending_future_business',
                ]],
            ],
            'fields' => [
                'mi_seasonal_employer' => yesNoField('Are you a seasonal employer?', 'seasonalEmployer', ['drives_conditional' => true]),
                'mi_business_opening_month' => [
                    'type' => 'select',
                    'label' => 'In what month does the business open?',
                    'options' => [
                        '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                        '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                        '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'mi_seasonal_employer'], '1']],
                    'source_name' => 'businessOpeningMonth',
                ],
                'mi_business_closing_month' => [
                    'type' => 'select',
                    'label' => 'In what month does the business close?',
                    'options' => [
                        '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                        '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                        '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'mi_seasonal_employer'], '1']],
                    'source_name' => 'businessClosingMonth',
                ],
                'mi_employee_leasing_license_number' => [
                    'type' => 'text',
                    'label' => 'Employee Leasing License Number (if an employee leasing company)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => $miGate('applies_payroll_service_or_peo'),
                    'source_name' => 'employeeLeasingLicenseNumber',
                ],
                'mi_formed_acquired_merged_past_6_years' => yesNoField('In the past 6 years, have you formed, acquired, or merged with another business?', 'formedAquiredOrMergedWithAnotherBusinessInPast6Years', ['drives_conditional' => true]),
                'mi_how_many_formed_acquired_merged' => [
                    'type' => 'text',
                    'label' => 'How many businesses have you formed, acquired, or merged with?',
                    'rules' => ['nullable', 'integer', 'min:1'],
                    'when' => ['==' => [['var' => 'mi_formed_acquired_merged_past_6_years'], '1']],
                    'source_name' => 'howManyBusinessHaveYouFormedAquiredOrMergedWith',
                ],
                'mi_how_many_forming_or_acquiring' => [
                    'type' => 'text',
                    'label' => 'How many businesses are you currently forming or acquiring?',
                    'rules' => ['nullable', 'integer', 'min:1'],
                    'when' => ['==' => [['var' => '$root.entity_currently_forming_or_acquiring'], '1']],
                    'source_name' => 'howManyBusinessesAreYourFormingOrAcquiring',
                ],
                'mi_how_many_incorporating' => [
                    'type' => 'text',
                    'label' => 'How many businesses are you incorporating from an existing business entity?',
                    'rules' => ['nullable', 'integer', 'min:1'],
                    'when' => ['==' => [['var' => '$root.entity_currently_incorporating_existing'], '1']],
                    'source_name' => 'howManyBusinessesAreYouIncorporatingFromExistingBusiness',
                ],
                'mi_currently_merging' => yesNoField('At the current time, are you merging with other business entities?', 'currentlyMergingWithOtherBusinessEntities', ['drives_conditional' => true]),
                'mi_how_many_merging' => [
                    'type' => 'text',
                    'label' => 'How many businesses are being merged?',
                    'rules' => ['nullable', 'integer', 'min:1'],
                    'when' => ['==' => [['var' => 'mi_currently_merging'], '1']],
                    'source_name' => 'howManyBusinessesAreBeingMerged',
                ],
                'mi_intending_future_business' => yesNoField('Are you intending to form a business at a future time?', 'intendingToFormBusinessInFuture'),
            ],
        ],
    ],
];

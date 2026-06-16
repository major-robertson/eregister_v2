<?php

/**
 * Connecticut — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/connecticut/application/` (organizationInformation,
 * primary, locationInformation, generalQuestions partials, agreements).
 *
 * Collapsed into core: disregarded entity (entity_extras), bank block
 * (core bank_*), employees/payroll + payroll service + retail / equipment
 * rental / taxable services / marketplace / admissions gates (applies_*),
 * sales tax liability start date (matrix_sales_tax_start_date), fiscal
 * year close month + state organized under (corporate extras / formation
 * state).
 *
 * §3A.2 fix applied: taxesServicesRequested[] options restored to the
 * legacy list (Retailer / Wholesaler / Manufacturer / Service Provider /
 * Other).
 */
$ctGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'CT']];

$ctMonths = [];
foreach ([
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
] as $value => $label) {
    $ctMonths['ct_month_'.strtolower(substr($label, 0, 3))] = [
        'type' => 'checkbox',
        'label' => $label,
        'when' => ['in' => [['var' => 'ct_when_business_is_active'], ['1', '2']]],
        'source_name' => 'monthsBusinessIsActive[]',
        'source_value' => (string) $value,
    ];
}

return [
    'extends' => 'base',

    'state_steps' => [
        'ct_state_ids_and_tax_services' => [
            'title' => 'Connecticut IDs & Taxes Requested',
            'description' => 'DRS registration basics.',
            'groups' => [
                ['title' => 'Identifiers', 'fields' => [
                    'ct_secretary_of_state_business_id', 'ct_tax_registration_number',
                ]],
                ['title' => 'Taxes & Services Requested', 'fields' => [
                    'ct_taxes_requested_retailer', 'ct_taxes_requested_wholesaler',
                    'ct_taxes_requested_manufacturer', 'ct_taxes_requested_service_provider',
                    'ct_taxes_requested_other', 'ct_description_business_activity',
                ]],
            ],
            'fields' => [
                'ct_secretary_of_state_business_id' => [
                    'type' => 'text',
                    'label' => 'CT Secretary of State Business ID',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'source_name' => 'ctSecretaryOfStateBusinessId',
                ],
                'ct_tax_registration_number' => [
                    'type' => 'text',
                    'label' => 'CT Tax Registration Number (if previously assigned)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'source_name' => 'ctTaxRegistrationNumber',
                ],
                // §3A.2.1: legacy option list restored.
                'ct_taxes_requested_retailer' => ['type' => 'checkbox', 'label' => 'Retailer', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '1'],
                'ct_taxes_requested_wholesaler' => ['type' => 'checkbox', 'label' => 'Wholesaler', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '2'],
                'ct_taxes_requested_manufacturer' => ['type' => 'checkbox', 'label' => 'Manufacturer', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '3'],
                'ct_taxes_requested_service_provider' => ['type' => 'checkbox', 'label' => 'Service Provider', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '4'],
                'ct_taxes_requested_other' => ['type' => 'checkbox', 'label' => 'Other', 'source_name' => 'taxesServicesRequested[]', 'source_value' => '5'],
                'ct_description_business_activity' => [
                    'type' => 'text',
                    'label' => 'Description of Business Activity (CT-specific narrative)',
                    'rules' => ['required', 'string', 'max:500'],
                    'source_name' => 'descriptionBusinessActivity',
                ],
            ],
        ],

        'ct_withholding' => [
            'title' => 'Connecticut Withholding',
            'description' => 'CT income tax withholding questions.',
            'fields' => [
                'ct_out_of_state_withholding' => yesNoField('Are you an out-of-state company voluntarily registering to withhold CT income tax?', 'outOfStateWithholdingCtIncomeTax', ['drives_conditional' => true]),
                'ct_payments_to_pensions_annuities' => yesNoField('Do you make payments of pensions, annuities, or retirement distributions?', 'paymentsToPensionsAnnuitiesRetriementDistributions'),
                'ct_pay_nonresident_athletes' => yesNoField('Do you pay nonresident athletes or entertainers?', 'payNonresidentAthletesOrEntertainers'),
                'ct_household_employee_withholding' => yesNoField('Do you only have household employees and withhold CT income tax?', 'haveHouseholdEmployeeAndWithholdCtIncomeTax'),
                'ct_agricultural_employee_withholding' => yesNoField('Do you only have agricultural employees and withhold CT income tax?', 'haveAgriculturalEmployeeAndWithholdCtIncomeTax'),
                'ct_file_agriculture_forms_annually' => nullableYesNoField('Do you file federal Form 943 (agriculture) annually?', 'fileAgricultureFormsAnnually', [
                    'when' => ['==' => [['var' => 'ct_out_of_state_withholding'], '1']],
                ]),
                'ct_withholding_liability_start_date' => [
                    'type' => 'date',
                    'label' => 'CT Income Tax Withholding Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'source_name' => 'incomeTaxWithholdingLiabilityStartDate',
                ],
            ],
        ],

        'ct_sales_use_and_surcharges' => [
            'title' => 'Connecticut Sales, Use & Surcharges',
            'description' => 'CT surcharge programs, each with its own liability start date.',
            'groups' => [
                ['title' => 'Meals & Use Tax', 'fields' => [
                    'ct_serving_meals_or_beverages',
                    'ct_purchasing_taxable_without_paying_ct_tax', 'ct_business_use_tax_start_date',
                    'ct_unrelated_business_income_tax_start_date',
                ]],
                ['title' => 'Surcharge Programs', 'fields' => [
                    'ct_prepaid_wireless_start_date', 'ct_room_occupancy_start_date',
                    'ct_dry_cleaning_establishment', 'ct_dry_cleaning_start_date',
                    'ct_tourism_surcharge_start_date', 'ct_vehicle_fleet_5_or_more',
                    'ct_rental_surcharge_start_date', 'ct_cigarette_retail_dealer',
                    'ct_cigarette_dealer_start_date',
                ]],
            ],
            'fields' => [
                'ct_serving_meals_or_beverages' => yesNoField('Do you serve meals or beverages in Connecticut?', 'servingMealsOrBeveragesInCt'),
                'ct_purchasing_taxable_without_paying_ct_tax' => yesNoField('Will you purchase taxable goods or services without paying CT sales tax?', 'purchasingTaxableGoodsOrServiceswWithoutPayingCtSalesTax', ['drives_conditional' => true]),
                'ct_business_use_tax_start_date' => [
                    'type' => 'date',
                    'label' => 'Business Use Tax Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ct_purchasing_taxable_without_paying_ct_tax'], '1']],
                    'source_name' => 'businessUseTaxLiabilityStartDate',
                ],
                'ct_unrelated_business_income_tax_start_date' => [
                    'type' => 'date',
                    'label' => 'Unrelated Business Income Tax Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'source_name' => 'unrealtedBusinessIncomeTaxLibailityStartDate',
                ],
                'ct_prepaid_wireless_start_date' => [
                    'type' => 'date',
                    'label' => 'Prepaid Wireless Fee Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => $ctGate('applies_telecom_or_prepaid_wireless'),
                    'source_name' => 'prepaidWirelessFeeLiabilityStartDate',
                ],
                'ct_room_occupancy_start_date' => [
                    'type' => 'date',
                    'label' => 'Room Occupancy Tax Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => $ctGate('applies_lodging_or_rentals'),
                    'source_name' => 'roomOccupancyTaxStartDate',
                ],
                'ct_dry_cleaning_establishment' => yesNoField('Are you a dry cleaning establishment in Connecticut?', 'dryCleaningEstablishmentInCt', ['drives_conditional' => true]),
                'ct_dry_cleaning_start_date' => [
                    'type' => 'date',
                    'label' => 'Dry Cleaning Surcharge Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ct_dry_cleaning_establishment'], '1']],
                    'source_name' => 'dryCleaningSurchargeTaxLiabilityStartDate',
                ],
                'ct_tourism_surcharge_start_date' => [
                    'type' => 'date',
                    'label' => 'Tourism Surcharge Liability Start Date (passenger vehicle rental/leasing)',
                    'rules' => ['nullable', 'date'],
                    'when' => $ctGate('applies_vehicle_rentals'),
                    'source_name' => 'tourismSurchargeTaxLiabilityStartDate',
                ],
                'ct_vehicle_fleet_5_or_more' => nullableYesNoField('Do you have a motor vehicle fleet of five or more rental vehicles?', 'motorVehcileFleetMoreThan5', [
                    'when' => $ctGate('applies_vehicle_rentals'),
                    'drives_conditional' => true,
                ]),
                'ct_rental_surcharge_start_date' => [
                    'type' => 'date',
                    'label' => 'Rental Surcharge Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ct_vehicle_fleet_5_or_more'], '1']],
                    'source_name' => 'rentalSurchargeTaxLiabilityStartDate',
                ],
                'ct_cigarette_retail_dealer' => nullableYesNoField('Are you engaged in the business of selling cigarettes at retail (cigarette dealer)?', 'engagedInBusinessOfSellingCigarettesAtRetail', [
                    'when' => $ctGate('applies_tobacco_vape'),
                    'drives_conditional' => true,
                ]),
                'ct_cigarette_dealer_start_date' => [
                    'type' => 'date',
                    'label' => 'Cigarette Dealer Liability Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ct_cigarette_retail_dealer'], '1']],
                    'source_name' => 'cigaretteDealerTaxLiabilityStartDate',
                ],
            ],
        ],

        'ct_admissions_dues_and_seasonal' => [
            'title' => 'Connecticut Admissions, Dues & Season',
            'description' => 'Club dues and active-months questions.',
            'groups' => [
                ['title' => 'Clubs & Dues', 'fields' => [
                    'ct_social_athletic_dues', 'ct_social_athletic_initiation',
                    'ct_admissions_dues_start_date',
                ]],
                ['title' => 'When Is the Business Active?', 'fields' => array_merge(
                    ['ct_when_business_is_active'],
                    array_keys($ctMonths),
                )],
            ],
            'fields' => array_merge(
                [
                    'ct_social_athletic_dues' => yesNoField('Are you a social, athletic, or sporting club with more than $100 in dues annually?', 'socialAthleticOrSportingWithMoreThan100InDuesAnnually'),
                    'ct_social_athletic_initiation' => yesNoField('Are you a social, athletic, or sporting club with more than $100 in initiation fees annually?', 'socialAthleticOrSportingWithMoreThan100InitiationFees'),
                    'ct_admissions_dues_start_date' => [
                        'type' => 'date',
                        'label' => 'Admissions / Dues Tax Liability Start Date',
                        'rules' => ['nullable', 'date'],
                        'source_name' => 'admissionsAndDueTaxLiabilityStartDate',
                    ],
                    'ct_when_business_is_active' => [
                        'type' => 'radio',
                        'label' => 'When is the business active?',
                        'options' => ['0' => 'All Year', '1' => 'Seasonal', '2' => 'One Time'],
                        'rules' => ['required', 'in:0,1,2'],
                        'drives_conditional' => true,
                        'source_name' => 'whenBusinessIsActive',
                    ],
                ],
                $ctMonths,
            ),
        ],

        'ct_corporate' => [
            'title' => 'Connecticut Corporation Business Tax',
            'description' => 'Corporation-specific CT questions.',
            'fields' => [
                'ct_corp_taxed_as_corp_with_nexus' => nullableYesNoField('Is the corporation/association taxed as a corporation with nexus in Connecticut?', 'coporationOrAssociationTaxedAsCorpWithNexus', [
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                ]),
                'ct_federal_corp_income_tax_exemption' => nullableYesNoField('Do you have a federal corporate income tax exemption?', 'federalCorporateIncomeTaxExemption', [
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                ]),
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

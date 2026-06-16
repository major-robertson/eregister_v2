<?php

/**
 * Pennsylvania — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/pennsylvania/application/` (organizationInformation,
 * primary, generalQuestions, businessInformation incl. businessActivity,
 * corporationInformation, predecessor, businessCounties).
 *
 * Collapsed into core: change-in-legal-structure / restructuring / 51%+ /
 * predecessor ceased questions + predecessor identity (core), construction
 * gate (applies_contractor), taxable services + cigarettes (applies_*),
 * establishments count (derived from locations[]), date of first operations
 * (core business_start_date), first operations in PA (matrix), fiscal year
 * end (core), incorporation date/country + publicly traded (core),
 * authorizer block (core authorized contact).
 *
 * DELETED (§3A): the 21 businessCategories[] checkboxes — dead code in
 * legacy (the partial exists but is never @included in the live flow).
 */
$paAcquisitionGate = ['or' => [
    ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'PA']],
    ['==' => [['var' => '$root.entity_legal_structure_change'], '1']],
    ['==' => [['var' => '$root.entity_underwent_restructuring'], '1']],
]];

// 68 county checkboxes (Out of State + 67 PA counties), value-mapped to
// the legacy countiesWithTaxableSales[] source ids.
$paCountyLabels = [
    1 => 'Out of State', 2 => 'Adams', 3 => 'Allegheny', 4 => 'Armstrong', 5 => 'Beaver',
    6 => 'Bedford', 7 => 'Berks', 8 => 'Blair', 9 => 'Bradford', 10 => 'Bucks',
    11 => 'Butler', 12 => 'Cambria', 13 => 'Cameron', 14 => 'Carbon', 15 => 'Centre',
    16 => 'Chester', 17 => 'Clarion', 18 => 'Clearfield', 19 => 'Clinton', 20 => 'Columbia',
    21 => 'Crawford', 22 => 'Cumberland', 23 => 'Dauphin', 24 => 'Delaware', 25 => 'Elk',
    26 => 'Erie', 27 => 'Fayette', 28 => 'Forest', 29 => 'Franklin', 30 => 'Fulton',
    31 => 'Greene', 32 => 'Huntingdon', 33 => 'Indiana', 34 => 'Jefferson', 35 => 'Juniata',
    36 => 'Lackawanna', 37 => 'Lancaster', 38 => 'Lawrence', 39 => 'Lebanon', 40 => 'Lehigh',
    41 => 'Luzerne', 42 => 'Lycoming', 43 => 'Mckean', 44 => 'Mercer', 45 => 'Mifflin',
    46 => 'Monroe', 47 => 'Montgomery', 48 => 'Montour', 49 => 'Northampton', 50 => 'Northumberland',
    51 => 'Perry', 52 => 'Philadelphia', 53 => 'Pike', 54 => 'Potter', 55 => 'Schuylkill',
    56 => 'Snyder', 57 => 'Somerset', 58 => 'Sullivan', 59 => 'Susquehanna', 60 => 'Tioga',
    61 => 'Union', 62 => 'Venango', 63 => 'Warren', 64 => 'Washington', 65 => 'Wayne',
    66 => 'Westmoreland', 67 => 'Wyoming', 68 => 'York',
];

$paCountyFields = [];
foreach ($paCountyLabels as $value => $label) {
    $paCountyFields['pa_county_'.$value] = [
        'type' => 'checkbox',
        'label' => $label,
        'source_name' => 'countiesWithTaxableSales[]',
        'source_value' => (string) $value,
    ];
}

// 13 asset types (predecessor assetsAcquired[]).
$paAssetLabels = [
    1 => 'Accounts Receivable', 2 => 'Contracts', 3 => 'Customers/Clients',
    4 => 'Employees', 5 => 'Equipment', 6 => 'Fixtures', 7 => 'Furniture',
    8 => 'Inventory', 9 => 'Leases', 10 => 'Machinery',
    11 => 'Name and/or Goodwill', 12 => 'Real Estate', 13 => 'Other',
];

$paAssetFields = [];
foreach ($paAssetLabels as $value => $label) {
    $paAssetFields['pa_asset_'.$value] = [
        'type' => 'checkbox',
        'label' => $label,
        'source_name' => 'assetsAcquired[]',
        'source_value' => (string) $value,
        'when' => $paAcquisitionGate,
    ];
}

$paCountyOptions = array_combine(config('counties.PA', []), config('counties.PA', []));

return [
    'extends' => 'base',

    'state_steps' => [
        'pa_registration' => [
            'title' => 'Pennsylvania Registration',
            'description' => 'PA-100 registration basics, lottery, and local sales declarations.',
            'groups' => [
                ['title' => 'Registration', 'fields' => [
                    'pa_reason_for_registration', 'pa_business_trade_name',
                    'pa_date_of_first_taxable_purchase', 'pa_taxes_services_sales_use_hotel',
                    'pa_percentage_receipts',
                ]],
                ['title' => 'Jurisdiction', 'fields' => [
                    'pa_school_district', 'pa_municipality',
                    'pa_selling_taxable_in_allegheny', 'pa_selling_taxable_in_philadelphia',
                ]],
                ['title' => 'Lottery & Nonprofit', 'fields' => [
                    'pa_lottery_retailer_in_past', 'pa_want_to_become_lottery_retailer',
                    'pa_phone_for_lottery', 'pa_exempt_from_taxation',
                ]],
            ],
            'fields' => [
                'pa_reason_for_registration' => [
                    'type' => 'radio',
                    'label' => 'Reason for Registration',
                    'options' => ['1' => 'New registration (opening a new business)', '0' => 'Adding additional taxes to an existing registration'],
                    'rules' => ['required', 'in:0,1'],
                    'source_name' => 'reasonForRegistration',
                ],
                'pa_business_trade_name' => [
                    'type' => 'text',
                    'label' => 'Business Trade Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'help' => 'Defaults to your DBA / trade name if you have one.',
                    'source_name' => 'businessTradeName',
                ],
                'pa_date_of_first_taxable_purchase' => [
                    'type' => 'date',
                    'label' => 'Date of First Purchase of Taxable Products',
                    'rules' => ['required', 'date'],
                    'source_name' => 'dateOfFirstPurchaseOfTaxableProducts',
                ],
                'pa_taxes_services_sales_use_hotel' => [
                    'type' => 'checkbox',
                    'label' => 'Sales, Use, Hotel Occupancy Tax License (always required for this application)',
                    'source_name' => 'taxesServicesRequested[]',
                    'source_value' => '1',
                ],
                'pa_percentage_receipts' => [
                    'type' => 'percent',
                    'label' => 'Percentage of Receipts From Taxable Sales',
                    'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                    'source_name' => 'percentageReceipts',
                ],
                'pa_school_district' => [
                    'type' => 'text',
                    'label' => 'PA School District',
                    'rules' => ['required', 'string', 'max:120'],
                    'source_name' => 'paSchoolDistrict',
                ],
                'pa_municipality' => [
                    'type' => 'text',
                    'label' => 'PA Municipality',
                    'rules' => ['required', 'string', 'max:120'],
                    'source_name' => 'paMunicipality',
                ],
                'pa_selling_taxable_in_allegheny' => yesNoField('Selling taxable products to consumers in Allegheny County?', 'sellingTaxableProductsInAllegny'),
                'pa_selling_taxable_in_philadelphia' => yesNoField('Selling taxable products to consumers in Philadelphia County?', 'sellingTaxableProductsInPhiladelphia'),
                'pa_lottery_retailer_in_past' => yesNoField('Has the business entity ever been a lottery retailer in the past?', 'lotteryRetailerInPast', ['drives_conditional' => true]),
                'pa_want_to_become_lottery_retailer' => yesNoField('Does this business entity want to become a Pennsylvania lottery retailer?', 'wantToBecomeLotteryRetailer', ['drives_conditional' => true]),
                'pa_phone_for_lottery' => [
                    'type' => 'text',
                    'label' => 'Preferred phone number for a Lottery District Sales Representative',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
                    'when' => ['or' => [
                        ['==' => [['var' => 'pa_lottery_retailer_in_past'], '1']],
                        ['==' => [['var' => 'pa_want_to_become_lottery_retailer'], '1']],
                    ]],
                    'source_name' => 'phoneForLottery',
                ],
                'pa_exempt_from_taxation' => nullableYesNoField('Is the Business Entity exempt from taxation under IRC Section 501(c)(3)?', 'exemptFromTaxation', [
                    'when' => ['==' => [['var' => '$root.entity_type'], 'nonprofit']],
                ]),
            ],
        ],

        'pa_construction' => [
            'title' => 'Pennsylvania Construction Activity',
            'description' => 'Shown because construction/contractor work applies to Pennsylvania.',
            'fields' => [
                'pa_new_construction_percentage' => [
                    'type' => 'percent',
                    'label' => 'New Construction %',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['contains' => [['var' => '$root.applies_contractor.states'], 'PA']],
                    'source_name' => 'newConstructionPercentage',
                ],
                'pa_renovative_construction_percentage' => [
                    'type' => 'percent',
                    'label' => 'Renovative Construction %',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['contains' => [['var' => '$root.applies_contractor.states'], 'PA']],
                    'source_name' => 'renovativeConstructionPercentage',
                ],
                'pa_residential_construction_percentage' => [
                    'type' => 'percent',
                    'label' => 'Residential Construction %',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['contains' => [['var' => '$root.applies_contractor.states'], 'PA']],
                    'source_name' => 'residentalConstructionPercentage',
                ],
                'pa_commercial_construction_percentage' => [
                    'type' => 'percent',
                    'label' => 'Commercial Construction %',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['contains' => [['var' => '$root.applies_contractor.states'], 'PA']],
                    'source_name' => 'commercialConstructionPercentage',
                ],
            ],
        ],

        'pa_business_history' => [
            'title' => 'Pennsylvania Predecessor Details',
            'description' => 'Shown because you acquired a business, changed legal structure, or restructured.',
            'groups' => [
                ['title' => 'Predecessor (PA detail)', 'fields' => [
                    'pa_predecessor_uc_account_number', 'pa_predecessor_trade_name',
                    'pa_predecessor_address', 'pa_how_business_was_acquired',
                    'pa_how_business_was_acquired_other',
                ]],
                ['title' => 'Acquisition Extent', 'fields' => [
                    'pa_predecessor_pct_total_business', 'pa_predecessor_pct_pa_business',
                    'pa_predecessor_business_activity',
                    'pa_predecessor_also_owner_1', 'pa_predecessor_also_owner_2', 'pa_predecessor_also_owner_3',
                ]],
                ['title' => 'Assets Acquired', 'fields' => array_merge(
                    array_keys($paAssetFields),
                    ['pa_assets_acquired_other'],
                )],
            ],
            'fields' => array_merge(
                [
                    'pa_predecessor_uc_account_number' => [
                        'type' => 'text',
                        'label' => 'Predecessor PA UC Account Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => $paAcquisitionGate,
                        'source_name' => 'predecessorPaUcAccountNumber',
                    ],
                    'pa_predecessor_trade_name' => [
                        'type' => 'text',
                        'label' => 'Predecessor Trade Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => $paAcquisitionGate,
                        'source_name' => 'predecessorTradeName',
                    ],
                    'pa_predecessor_address' => [
                        'type' => 'address',
                        'label' => 'Predecessor Address',
                        'rules' => ['nullable'],
                        'when' => $paAcquisitionGate,
                    ],
                    'pa_how_business_was_acquired' => [
                        'type' => 'select',
                        'label' => 'How was the business acquired?',
                        'options' => [
                            'purchase' => 'Purchase',
                            'change_in_legal_structure' => 'Change in legal structure',
                            'consolidation' => 'Consolidation',
                            'gift' => 'Gift',
                            'merger' => 'Merger',
                            'irs_338_election' => 'IRS Sec. 338 Election',
                            'other' => 'Other',
                        ],
                        'rules' => ['nullable'],
                        'when' => $paAcquisitionGate,
                        'drives_conditional' => true,
                        'source_name' => 'howBusinessWasAcquired',
                    ],
                    'pa_how_business_was_acquired_other' => [
                        'type' => 'text',
                        'label' => 'Please describe how the business was acquired',
                        'rules' => ['nullable', 'string', 'max:255'],
                        'when' => ['==' => [['var' => 'pa_how_business_was_acquired'], 'other']],
                        'source_name' => 'howBusinessWasAcquiredOther',
                    ],
                    'pa_predecessor_pct_total_business' => [
                        'type' => 'percent',
                        'label' => "Percentage of the predecessor's total business (PA and non-PA) acquired",
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => $paAcquisitionGate,
                        'source_name' => 'predecessorPercentageTotalBusinessPa',
                    ],
                    'pa_predecessor_pct_pa_business' => [
                        'type' => 'percent',
                        'label' => "Percentage of the predecessor's PA business acquired",
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => $paAcquisitionGate,
                        'source_name' => 'predecessorPercentagePaBusinessAcquired',
                    ],
                    'pa_predecessor_business_activity' => [
                        'type' => 'text',
                        'label' => "Predecessor's business activity in the acquired PA operation",
                        'rules' => ['nullable', 'string', 'max:255'],
                        'when' => $paAcquisitionGate,
                        'source_name' => 'predecessorBusinessActivity',
                    ],
                    'pa_predecessor_also_owner_1' => nullableYesNoField('At the time of transfer, did any owner/shareholder (5%+) of the predecessor also hold ownership in this business?', 'predecessorAlsoOwnerOrShareholder', [
                        'when' => $paAcquisitionGate,
                    ]),
                    'pa_predecessor_also_owner_2' => nullableYesNoField('Did any officer or director of the predecessor also serve this business?', 'predecessorAlsoOwnerOrShareholder2', [
                        'when' => $paAcquisitionGate,
                    ]),
                    'pa_predecessor_also_owner_3' => nullableYesNoField('Was there any other common ownership or control with the predecessor?', 'predecessorAlsoOwnerOrShareholder3', [
                        'when' => $paAcquisitionGate,
                    ]),
                ],
                $paAssetFields,
                [
                    'pa_assets_acquired_other' => [
                        'type' => 'text',
                        'label' => 'Other Asset Description',
                        'rules' => ['nullable', 'string', 'max:200'],
                        'when' => ['==' => [['var' => 'pa_asset_13'], '1']],
                        'source_name' => 'assetsAcquiredOther',
                    ],
                ],
            ),
        ],

        'pa_corporate' => [
            'title' => 'Pennsylvania Corporate Details',
            'description' => 'Corporation-specific PA questions.',
            'fields' => [
                'pa_certificate_of_authority_date' => [
                    'type' => 'date',
                    'label' => 'PA Certificate of Authority Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                    'source_name' => 'certificateOfAuthorityDate',
                ],
                'pa_s_corporation_election' => [
                    'type' => 'select',
                    'label' => 'Federal S Corporation election?',
                    'options' => ['na' => 'N/A', 'federal' => 'Federal'],
                    'rules' => ['nullable'],
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                    'source_name' => 'sCorporation',
                ],
                'pa_best_description_corporation' => [
                    'type' => 'select',
                    'label' => 'Best Description of the Corporation',
                    'options' => [
                        'stock' => 'Stock',
                        'non_stock' => 'Non-stock',
                        'management' => 'Management',
                        'professional' => 'Professional',
                        'cooperative' => 'Cooperative',
                        'statutory_close' => 'Statutory Close',
                        'bank_state' => 'Bank: State',
                        'bank_federal' => 'Bank: Federal',
                        'mutual_thrift_state' => 'Mutual Thrift: State',
                        'mutual_thrift_federal' => 'Mutual Thrift: Federal',
                        'insurance_pa' => 'Insurance Company: PA',
                        'insurance_non_pa' => 'Insurance Company: Non-PA',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                    'source_name' => 'bestDescriptionCorporation',
                ],
            ],
        ],

        'pa_counties' => [
            'title' => 'Pennsylvania Counties With Taxable Sales',
            'description' => 'Select every county where you will make taxable sales (including Out of State).',
            'groups' => [
                ['title' => 'Counties', 'fields' => array_keys($paCountyFields)],
            ],
            'fields' => $paCountyFields,
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'pa_county' => [
                                'type' => 'select',
                                'label' => 'PA County of Residence',
                                'options' => $paCountyOptions,
                                'rules' => ['required'],
                                'source_name' => 'primaryContactCounty',
                            ],
                            'pa_effective_date_of_ownership' => [
                                'type' => 'date',
                                'label' => 'Effective Date of Ownership',
                                'rules' => ['required', 'date'],
                                'source_name' => 'primaryContactEffectiveDateOfOwnership',
                            ],
                            'pa_responsible_sales_tax' => [
                                'type' => 'radio',
                                'label' => 'Responsible to remit sales tax?',
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => 'primaryContactResponsibleSalesTax',
                            ],
                            'pa_responsible_employer_withholding' => [
                                'type' => 'radio',
                                'label' => 'Responsible to remit employer withholding?',
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => 'primaryContactResponsibleEmployerWitholding',
                            ],
                            'pa_responsible_compensation_coverage' => [
                                'type' => 'radio',
                                'label' => 'Responsible to remit workers compensation coverage?',
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => 'primaryContactResponsibleCompensationCoverage',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

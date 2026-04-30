<?php

/**
 * Pennsylvania — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/pennsylvania/application/`
 * (primary, organizationInformation/*, generalQuestions/*, businessInformation/*
 * including businessAddress, mailingAddress, recordLocation, businessActivity,
 * corporationInformation, predecessor, businessActivities/businessCategories,
 * businessActivities/businessCounties) plus matching JS validators.
 *
 * PA has the largest state-specific question set: construction percentages,
 * 21 business categories, all 67 PA counties + Out of State, and a full
 * predecessor block with assets-acquired grid.
 */

// Helper to declare a checkbox-grid field with traceability metadata.
$checkboxGrid = function (string $key, string $label, string $sourceName, string $sourceValue, ?array $when = null): array {
    $field = [
        'type' => 'checkbox',
        'label' => $label,
        'source_name' => $sourceName,
        'source_value' => $sourceValue,
    ];
    if ($when !== null) {
        $field['when'] = $when;
    }

    return $field;
};

// 21 business categories
$businessCategoryLabels = [
    1 => 'Agriculture, Forestry, Fishing and Hunting',
    2 => 'Mining, Quarrying, and Oil and Gas Extraction',
    3 => 'Utilities',
    4 => 'Construction',
    5 => 'Manufacturing',
    6 => 'Wholesale Trade',
    7 => 'Retail Trade',
    8 => 'Transportation and Warehousing',
    9 => 'Information',
    10 => 'Finance and Insurance',
    11 => 'Real Estate and Rental and Leasing',
    12 => 'Retail Trade (alt)',
    13 => 'Professional and Technical Services',
    14 => 'Management of Companies and Enterprises',
    15 => 'Administrative and Waste Services',
    16 => 'Educational Services',
    17 => 'Health Care and Social Assistance',
    18 => 'Arts, Entertainment, and Recreation',
    19 => 'Accommodation and Food Services',
    20 => 'Other Services, Except Public Administration',
    21 => 'Public Administration',
];

// 68 counties (Out of State + 67 PA counties)
$countyLabels = [
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

// 13 asset types (predecessor)
$assetLabels = [
    1 => 'Accounts Receivable', 2 => 'Contracts', 3 => 'Customers/Clients',
    4 => 'Employees', 5 => 'Equipment', 6 => 'Fixtures', 7 => 'Furniture',
    8 => 'Inventory', 9 => 'Leases', 10 => 'Machinery',
    11 => 'Name and/or Goodwill', 12 => 'Real Estate', 13 => 'Other',
];

$businessCategoryFields = [];
foreach ($businessCategoryLabels as $value => $label) {
    $key = 'pa_business_category_'.$value;
    $businessCategoryFields[$key] = $checkboxGrid($key, $label, 'businessCategories[]', (string) $value);
}

$countyFields = [];
foreach ($countyLabels as $value => $label) {
    $key = 'pa_county_'.$value;
    $countyFields[$key] = $checkboxGrid($key, $label, 'countiesWithTaxableSales[]', (string) $value);
}

$assetFields = [];
foreach ($assetLabels as $value => $label) {
    $key = 'pa_asset_'.$value;
    $assetFields[$key] = $checkboxGrid(
        $key,
        $label,
        'assetsAcquired[]',
        (string) $value,
        ['==' => [['var' => 'pa_acquire_another_business'], '1']]
    );
}

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => array_merge([
                    // ───────── PA-specific identifiers / org ─────────
                    'pa_reason_for_registration' => [
                        'type' => 'radio',
                        'label' => 'Reason for Registration',
                        'options' => ['1' => 'Opening a new business', '0' => 'Adding a business activity to an existing registration'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'reasonForRegistration',
                    ],
                    'pa_business_trade_name' => [
                        'type' => 'text',
                        'label' => 'Business Trade Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'source_name' => 'businessTradeName',
                    ],
                    'pa_number_of_establishments' => [
                        'type' => 'text',
                        'label' => 'Number of Establishments',
                        'rules' => ['required', 'integer', 'min:1'],
                        'source_name' => 'numberOfEstablishments',
                    ],
                    'pa_date_of_first_taxable_purchase' => [
                        'type' => 'date',
                        'label' => 'Date of First Purchase of Taxable Products',
                        'rules' => ['required', 'date'],
                        'source_name' => 'dateOfFirstPurchaseOfTaxableProducts',
                    ],
                    'pa_taxes_services_sales_use_hotel' => [
                        'type' => 'checkbox',
                        'label' => 'Sales, Use, Hotel Occupancy Tax License (always required)',
                        'source_name' => 'taxesServicesRequested[]',
                        'source_value' => '1',
                    ],
                    'pa_percentage_receipts' => [
                        'type' => 'percent',
                        'label' => 'Percentage of Receipts From Taxable Sales',
                        'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                        'source_name' => 'percentageReceipts',
                    ],

                    // ───────── Construction activity & percentages ─────────
                    'pa_engaged_in_construction_activity' => [
                        'type' => 'radio',
                        'label' => 'Are you engaged in construction activity?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'engagedInConstructionActivity',
                    ],
                    'pa_new_construction_percentage' => [
                        'type' => 'percent', 'label' => 'New Construction %',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => ['==' => [['var' => 'pa_engaged_in_construction_activity'], '1']],
                        'source_name' => 'newConstructionPercentage',
                    ],
                    'pa_renovative_construction_percentage' => [
                        'type' => 'percent', 'label' => 'Renovative Construction %',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => ['==' => [['var' => 'pa_engaged_in_construction_activity'], '1']],
                        'source_name' => 'renovativeConstructionPercentage',
                    ],
                    'pa_residential_construction_percentage' => [
                        'type' => 'percent', 'label' => 'Residential Construction %',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => ['==' => [['var' => 'pa_engaged_in_construction_activity'], '1']],
                        'source_name' => 'residentalConstructionPercentage',
                    ],
                    'pa_commercial_construction_percentage' => [
                        'type' => 'percent', 'label' => 'Commercial Construction %',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => ['==' => [['var' => 'pa_engaged_in_construction_activity'], '1']],
                        'source_name' => 'commercialConstructionPercentage',
                    ],

                    // ───────── Lottery / nonprofit / general ─────────
                    'pa_lottery_retailer_in_past' => [
                        'type' => 'radio', 'label' => 'Lottery retailer in the past?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'lotteryRetailerInPast',
                    ],
                    'pa_want_to_become_lottery_retailer' => [
                        'type' => 'radio', 'label' => 'Want to become a lottery retailer?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'wantToBecomeLotteryRetailer',
                    ],
                    'pa_organized_for_profit' => [
                        'type' => 'radio', 'label' => 'Organized for profit or non-profit?',
                        'options' => ['1' => 'Profit', '0' => 'Non-profit'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'organizedForProfit',
                    ],
                    'pa_exempt_from_taxation' => [
                        'type' => 'radio', 'label' => 'Section 501(c)(3) exempt?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'exemptFromTaxation',
                    ],
                    'pa_selling_taxable_services_in_pa' => [
                        'type' => 'radio', 'label' => 'Selling taxable services in PA?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingTaxableServicesInPa',
                    ],
                    'pa_selling_cigarettes_in_pa' => [
                        'type' => 'radio', 'label' => 'Selling cigarettes in PA?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingCigarettesInPa',
                    ],
                    'pa_date_of_first_operations' => [
                        'type' => 'date', 'label' => 'Date of First Operations (anywhere)',
                        'rules' => ['required', 'date'],
                        'source_name' => 'dateOfFirstOperations',
                    ],
                    'pa_date_of_first_operations_in_pa' => [
                        'type' => 'date', 'label' => 'Date of First Operations in PA',
                        'rules' => ['required', 'date'],
                        'source_name' => 'dateOfFirstOperationsInPa',
                    ],
                    'pa_business_entity_fiscal_year_end' => [
                        'type' => 'date', 'label' => 'Business Entity Fiscal Year End',
                        'rules' => ['required', 'date'],
                        'source_name' => 'businessEntityFiscalYearEnd',
                    ],
                    'pa_school_district' => [
                        'type' => 'text', 'label' => 'PA School District',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'paSchoolDistrict',
                    ],
                    'pa_municipality' => [
                        'type' => 'text', 'label' => 'PA Municipality',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'paMunicipality',
                    ],

                    // ───────── Records location / activity ─────────
                    'pa_different_records_location_address' => [
                        'type' => 'radio',
                        'label' => 'Are records kept at a different address than the business location?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'differentRecordsLocationAddress',
                    ],
                    'pa_records_location_address' => [
                        'type' => 'address',
                        'label' => 'Records Location Address',
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'pa_different_records_location_address'], '1']],
                    ],
                    'pa_selling_taxable_in_allegheny' => [
                        'type' => 'radio', 'label' => 'Selling taxable products in Allegheny County?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingTaxableProductsInAllegny',
                    ],
                    'pa_selling_taxable_in_philadelphia' => [
                        'type' => 'radio', 'label' => 'Selling taxable products in Philadelphia County?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellingTaxableProductsInPhiladelphia',
                    ],

                    // ───────── Predecessor / acquisition ─────────
                    'pa_change_in_legal_structure' => [
                        'type' => 'radio', 'label' => 'Change in legal structure?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'changeInLegalStructure',
                    ],
                    'pa_underwent_restructuring' => [
                        'type' => 'radio', 'label' => 'Underwent restructuring?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'source_name' => 'underwentRestructuring',
                    ],
                    'pa_acquire_another_business' => [
                        'type' => 'radio', 'label' => 'Did you acquire another business?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'acquireAnotherBusiness',
                    ],
                    'pa_acquire_51_percent_or_more_any_class' => [
                        'type' => 'radio', 'label' => 'Acquired 51%+ of any stock class?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'source_name' => 'acqiuire51PercentOrMoreAnyClass',
                    ],
                    'pa_acquire_51_percent_or_more_total_asset' => [
                        'type' => 'radio', 'label' => 'Acquired 51%+ of total assets?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'source_name' => 'acqiuire51PercentOrMoreTotalAsset',
                    ],
                    'pa_predecessor_name' => [
                        'type' => 'text', 'label' => 'Predecessor Business Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                    ],
                    'pa_predecessor_fein' => [
                        'type' => 'text', 'label' => 'Predecessor FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'sensitive' => true,
                    ],
                    'pa_acquisition_date' => [
                        'type' => 'date', 'label' => 'Acquisition Date',
                        'rules' => ['nullable', 'date', 'before_or_equal:today'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'source_name' => 'acquisitionDate',
                    ],
                    'pa_predecessor_ceased_paying_wages' => [
                        'type' => 'radio', 'label' => 'Predecessor ceased paying wages?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'source_name' => 'predecessorCeasedPayingWages',
                    ],
                    'pa_predecessor_ceased_operations' => [
                        'type' => 'radio', 'label' => 'Predecessor ceased operations?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'pa_acquire_another_business'], '1']],
                        'source_name' => 'predecessorCeasedOperations',
                    ],
                    'pa_assets_acquired_other' => [
                        'type' => 'text', 'label' => 'Other Asset Description',
                        'rules' => ['nullable', 'string', 'max:200'],
                        'when' => ['==' => [['var' => 'pa_asset_13'], '1']],
                        'source_name' => 'assetsAcquiredOther',
                    ],

                    // ───────── Corporation Information ─────────
                    'pa_date_of_incorporation' => [
                        'type' => 'date', 'label' => 'Date of Incorporation',
                        'rules' => ['nullable', 'date'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                        'source_name' => 'dateOfIncorporation',
                    ],
                    'pa_certificate_of_authority_date' => [
                        'type' => 'date', 'label' => 'PA Certificate of Authority Date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                        'source_name' => 'certificateOfAuthorityDate',
                    ],
                    'pa_country_of_incorporation' => [
                        'type' => 'text', 'label' => 'Country of Incorporation',
                        'rules' => ['nullable', 'string', 'max:60'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                        'source_name' => 'countryOfIncorporation',
                    ],
                    'pa_s_corporation_election' => [
                        'type' => 'radio', 'label' => 'Filed Federal S Corporation election?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'source_name' => 'sCorporation',
                    ],
                    'pa_best_description_corporation' => [
                        'type' => 'text', 'label' => 'Best Description of the Corporation',
                        'rules' => ['nullable', 'string', 'max:255'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'source_name' => 'bestDescriptionCorporation',
                    ],
                    'pa_stock_publicly_traded' => [
                        'type' => 'radio', 'label' => 'Is the stock publicly traded?',
                        'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp']]],
                        'source_name' => 'stockPubliclyTraded',
                    ],
                ],
                    $businessCategoryFields,
                    $countyFields,
                    $assetFields),
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'pa_county' => [
                                'type' => 'text',
                                'label' => 'PA County of Residence',
                                'rules' => ['required', 'string', 'max:60'],
                                'source_name' => 'primaryContactCounty',
                            ],
                            'pa_effective_date_of_ownership' => [
                                'type' => 'date',
                                'label' => 'Effective Date of Ownership',
                                'rules' => ['required', 'date'],
                                'source_name' => 'primaryContactEffectiveDateOfOwnership',
                            ],
                            'pa_responsible_sales_tax' => [
                                'type' => 'checkbox',
                                'label' => 'Responsible to remit sales tax',
                                'source_name' => 'primaryContactResponsibleSalesTax',
                            ],
                            'pa_responsible_employer_withholding' => [
                                'type' => 'checkbox',
                                'label' => 'Responsible to remit employer withholding',
                                'source_name' => 'primaryContactResponsibleEmployerWitholding',
                            ],
                            'pa_responsible_compensation_coverage' => [
                                'type' => 'checkbox',
                                'label' => 'Responsible to remit workers compensation coverage',
                                'source_name' => 'primaryContactResponsibleCompensationCoverage',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

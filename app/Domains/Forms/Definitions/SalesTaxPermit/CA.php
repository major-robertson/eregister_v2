<?php

/**
 * California — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/california/application/`
 * (primary, organizationInformation, businessInformation, supplierInformation,
 * generalQuestions) plus matching JS validators.
 *
 * NAICS, FEIN, business contact, business address, formation state, and
 * the responsible_people repeater all live in base.php. Only CA-specific
 * questions are added here.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'California Identifiers', 'fields' => [
                    'ca_seller_permit_number', 'ca_corporate_number', 'ca_llc_number',
                    'ca_secretary_of_state_number', 'ca_state_employer_id',
                ]],
                ['title' => 'Business Location & Activity', 'fields' => [
                    'ca_business_location_type', 'ca_retail_location',
                    'ca_economic_nexus', 'ca_within_city_limits',
                ]],
                ['title' => 'Operations', 'fields' => [
                    'ca_ship_or_deliver', 'ca_use_tax', 'ca_construction_contractor',
                    'ca_itinerant_seller', 'ca_auction_sales', 'ca_vending_machines',
                    'ca_lessor', 'ca_motor_vehicle_lessor',
                ]],
                ['title' => 'Banking & Payment', 'fields' => ['ca_bank_name', 'ca_branch_location']],
                ['title' => 'Sales Projections', 'fields' => [
                    'ca_projected_monthly_sales', 'ca_projected_monthly_taxable_sales',
                    'ca_products_that_will_be_sold',
                ]],
                ['title' => 'Industry-Specific (CDTFA)', 'fields' => [
                    'ca_holding_abc_license', 'ca_abc_license_number',
                    'ca_sell_alcohol', 'ca_sell_tobacco', 'ca_sell_batteries',
                    'ca_sell_fuel_products', 'ca_sell_covered_devices',
                ]],
                ['title' => 'Supplier', 'fields' => [
                    'ca_supplier_name', 'ca_supplier_phone', 'ca_supplier_address',
                    'ca_supplier_products_purchased',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── California-specific identifiers ─────────
                    'ca_seller_permit_number' => [
                        'type' => 'text',
                        'label' => 'Existing CA Seller Permit # (if any)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Leave blank if you do not have an existing permit.',
                    ],
                    'ca_corporate_number' => [
                        'type' => 'text',
                        'label' => 'CA Corporate Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Required for California-incorporated entities.',
                        'source_name' => 'corporateNumber',
                    ],
                    'ca_llc_number' => [
                        'type' => 'text',
                        'label' => 'CA LLC Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Required for California-formed LLCs.',
                        'source_name' => 'llcNumber',
                    ],
                    'ca_secretary_of_state_number' => [
                        'type' => 'text',
                        'label' => 'CA Secretary of State File Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'source_name' => 'californiaSecretaryOfStateNumber',
                    ],
                    'ca_state_employer_id' => [
                        'type' => 'text',
                        'label' => 'CA State Employer ID Number (SEIN)',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'help' => 'Optional. EDD-issued employer account number.',
                        'source_name' => 'SEIN',
                    ],

                    // ───────── Business location / activity ─────────
                    'ca_business_location_type' => [
                        'type' => 'select',
                        'label' => 'Type of Business Location',
                        'options' => [
                            'retail_storefront' => 'Retail Storefront',
                            'office' => 'Office',
                            'warehouse' => 'Warehouse',
                            'home_based' => 'Home-Based',
                            'online_only' => 'Online Only',
                        ],
                        'rules' => ['required'],
                    ],
                    'ca_retail_location' => yesNoField('Will you operate a retail location in California?', 'retailLocation'),
                    'ca_economic_nexus' => yesNoField('Do you meet California economic nexus thresholds (over $500,000 in sales)?', 'economicNexus'),
                    'ca_within_city_limits' => yesNoField('Is the business address within city limits?', 'withinCityLimits'),

                    // ───────── Operations radios (CA generalQuestions) ─────────
                    'ca_ship_or_deliver' => yesNoField('Will you ship or deliver tangible personal property to California customers?'),
                    'ca_use_tax' => yesNoField('Will you owe California use tax on items purchased without sales tax?'),
                    'ca_construction_contractor' => yesNoField('Are you a construction contractor?'),
                    'ca_itinerant_seller' => yesNoField('Are you an itinerant seller (sell at temporary locations)?'),
                    'ca_auction_sales' => yesNoField('Will you conduct auction sales?'),
                    'ca_vending_machines' => yesNoField('Will you sell from vending machines?'),
                    'ca_lessor' => yesNoField('Are you a lessor of tangible personal property?'),
                    'ca_motor_vehicle_lessor' => yesNoField('Are you a motor vehicle lessor?'),

                    // ───────── Banking & payment ─────────
                    'ca_bank_name' => [
                        'type' => 'text',
                        'label' => 'Bank Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'source_name' => 'bankName',
                    ],
                    'ca_branch_location' => [
                        'type' => 'text',
                        'label' => 'Bank Branch Location',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'source_name' => 'branchLocation',
                    ],

                    // ───────── Sales projections ─────────
                    'ca_projected_monthly_sales' => [
                        'type' => 'text',
                        'label' => 'Projected Monthly Sales (USD)',
                        'rules' => ['required', 'numeric', 'min:0'],
                        'source_name' => 'projectedMonthlySales',
                    ],
                    'ca_projected_monthly_taxable_sales' => [
                        'type' => 'text',
                        'label' => 'Projected Monthly Taxable Sales (USD)',
                        'rules' => ['required', 'numeric', 'min:0'],
                        'source_name' => 'projectedMonthlyTaxableSales',
                    ],
                    'ca_products_that_will_be_sold' => [
                        'type' => 'text',
                        'label' => 'Specific Products That Will Be Sold',
                        'rules' => ['required', 'string', 'max:500'],
                        'source_name' => 'productsThatWillBeSold',
                    ],

                    // ───────── Industry-specific (CDTFA) ─────────
                    'ca_holding_abc_license' => yesNoField('Do you hold an ABC (Alcoholic Beverage Control) license?', 'holdingABCLicense', ['drives_conditional' => true]),
                    'ca_abc_license_number' => [
                        'type' => 'text',
                        'label' => 'ABC License Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ca_holding_abc_license'], '1']],
                    ],
                    'ca_sell_alcohol' => yesNoField('Will you sell alcoholic beverages?', 'sellAlcohol'),
                    'ca_sell_tobacco' => yesNoField('Will you sell tobacco or cigarettes?', 'sellTobacco'),
                    'ca_sell_batteries' => yesNoField('Will you sell lead-acid batteries?', 'sellBatteries'),
                    'ca_sell_fuel_products' => yesNoField('Will you sell fuel products?', 'sellFuelProducts'),
                    'ca_sell_covered_devices' => yesNoField('Will you sell covered electronic devices subject to recycling fee?', 'sellCoveredDevice'),

                    // ───────── Supplier (CA-specific) ─────────
                    'ca_supplier_name' => [
                        'type' => 'text',
                        'label' => 'Primary Supplier Name',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'supplierName',
                    ],
                    'ca_supplier_phone' => [
                        'type' => 'text',
                        'label' => 'Supplier Phone',
                        'rules' => ['required', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'supplierPhoneNumber',
                    ],
                    'ca_supplier_address' => [
                        'type' => 'address',
                        'label' => 'Supplier Address',
                        'rules' => ['required'],
                    ],
                    'ca_supplier_products_purchased' => [
                        'type' => 'text',
                        'label' => 'Products Purchased from Supplier',
                        'rules' => ['required', 'string', 'max:500'],
                        'source_name' => 'supplierProductsPurchased',
                    ],
                ],
            ],
        ],

        // No state-specific responsible_people fields: driver license
        // state, number, and expiration are now collected once in the
        // base responsible_people repeater for every entity.
    ],
];

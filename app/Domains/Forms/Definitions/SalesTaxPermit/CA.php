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
                    'ca_retail_location' => [
                        'type' => 'radio',
                        'label' => 'Will you operate a retail location in California?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'retailLocation',
                    ],
                    'ca_economic_nexus' => [
                        'type' => 'radio',
                        'label' => 'Do you meet California economic nexus thresholds (over $500,000 in sales)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'economicNexus',
                    ],
                    'ca_within_city_limits' => [
                        'type' => 'radio',
                        'label' => 'Is the business address within city limits?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'withinCityLimits',
                    ],

                    // ───────── Operations radios (CA generalQuestions) ─────────
                    'ca_ship_or_deliver' => [
                        'type' => 'radio',
                        'label' => 'Will you ship or deliver tangible personal property to California customers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_use_tax' => [
                        'type' => 'radio',
                        'label' => 'Will you owe California use tax on items purchased without sales tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_construction_contractor' => [
                        'type' => 'radio',
                        'label' => 'Are you a construction contractor?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_itinerant_seller' => [
                        'type' => 'radio',
                        'label' => 'Are you an itinerant seller (sell at temporary locations)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_auction_sales' => [
                        'type' => 'radio',
                        'label' => 'Will you conduct auction sales?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_vending_machines' => [
                        'type' => 'radio',
                        'label' => 'Will you sell from vending machines?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_lessor' => [
                        'type' => 'radio',
                        'label' => 'Are you a lessor of tangible personal property?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],
                    'ca_motor_vehicle_lessor' => [
                        'type' => 'radio',
                        'label' => 'Are you a motor vehicle lessor?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                    ],

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
                    'ca_holding_abc_license' => [
                        'type' => 'radio',
                        'label' => 'Do you hold an ABC (Alcoholic Beverage Control) license?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'holdingABCLicense',
                    ],
                    'ca_abc_license_number' => [
                        'type' => 'text',
                        'label' => 'ABC License Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ca_holding_abc_license'], '1']],
                    ],
                    'ca_sell_alcohol' => [
                        'type' => 'radio',
                        'label' => 'Will you sell alcoholic beverages?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellAlcohol',
                    ],
                    'ca_sell_tobacco' => [
                        'type' => 'radio',
                        'label' => 'Will you sell tobacco or cigarettes?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellTobacco',
                    ],
                    'ca_sell_batteries' => [
                        'type' => 'radio',
                        'label' => 'Will you sell lead-acid batteries?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellBatteries',
                    ],
                    'ca_sell_fuel_products' => [
                        'type' => 'radio',
                        'label' => 'Will you sell fuel products?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellFuelProducts',
                    ],
                    'ca_sell_covered_devices' => [
                        'type' => 'radio',
                        'label' => 'Will you sell covered electronic devices subject to recycling fee?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellCoveredDevice',
                    ],

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

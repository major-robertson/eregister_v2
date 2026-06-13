<?php

/**
 * Texas — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/texas/application/` (organizationInformation,
 * primary, businessInformation, entityQuestions, contactInformation).
 *
 * Collapsed into core: merger (entity_involved_in_merger), internet/mail
 * order + temporary events + home-based + ship/deliver + marketplace +
 * taxable services + alcohol/tobacco/telecom/fireworks gates (applies_*),
 * monthly taxable sales threshold prefill (matrix), bank + card processor
 * + records/alternate contacts (core), landlord + distribution locations
 * (locations[] rows), predecessor identity (core predecessor_*).
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'tx_state_ids' => [
            'title' => 'Texas Identifiers',
            'description' => 'Comptroller and Secretary of State identifiers.',
            'fields' => [
                'tx_taxpayer_number' => [
                    'type' => 'text',
                    'label' => 'Texas Taxpayer Number (if previously issued)',
                    'rules' => ['nullable', 'digits:11'],
                    'help' => '11-digit Texas Comptroller taxpayer number. Leave blank if you have not been issued one.',
                    'source_name' => 'texasTaxpayerNumber',
                ],
                'tx_franchise_tax_id' => [
                    'type' => 'text',
                    'label' => 'Texas Franchise Tax ID (if any)',
                    'rules' => ['nullable', 'string', 'max:20'],
                ],
                'tx_sos_file_number' => [
                    'type' => 'text',
                    'label' => 'TX Secretary of State File Number',
                    'rules' => ['nullable', 'digits:10'],
                    'help' => 'Required for corporations, LLCs, LPs, and LLPs. 10 digits.',
                    'source_name' => 'texasFileNumber',
                ],
                'tx_business_location_in_texas' => [
                    'type' => 'radio',
                    'label' => 'Is the principal place of business located in Texas?',
                    'options' => ['1' => 'Texas', '0' => 'Another state'],
                    'rules' => ['required', 'in:0,1'],
                    'source_name' => 'businessLocation',
                ],
            ],
        ],

        'tx_sales_activity' => [
            'title' => 'Texas Sales Activity',
            'description' => 'Sales operations and nexus questions specific to Texas.',
            'fields' => [
                'tx_exceeds_8k_monthly' => yesNoField('Will your monthly taxable sales exceed $8,000?', 'exceed8k', [
                    'help' => 'Compare against the monthly taxable sales estimate you entered for Texas earlier.',
                ]),
                'tx_taking_orders_taxable_items' => yesNoField('Do representatives take orders for taxable items in Texas?', 'takingOrderTaxableItems'),
                'tx_receipts_from_personal_property' => yesNoField('Do you have receipts from tangible personal property in Texas?', 'receiptFromPersonalProperty'),
                'tx_sales_people_other_locations' => yesNoField('Do you have sales people operating in other locations?', 'salesPeople'),
                'tx_directions' => [
                    'type' => 'text',
                    'label' => 'Directions to the home-based business location',
                    'rules' => ['nullable', 'string', 'max:500'],
                    'when' => ['contains' => [['var' => '$root.applies_home_or_residence_based.states'], 'TX']],
                    'source_name' => 'directions',
                ],
            ],
        ],

        'tx_special_products_services' => [
            'title' => 'Texas Products & Services',
            'description' => 'Texas-specific follow-ups for regulated products and services.',
            'fields' => [
                'tx_alcoholic_beverages_permit' => [
                    'type' => 'select',
                    'label' => 'Which alcoholic beverages permit will you hold?',
                    'options' => [
                        'mixed_beverage' => 'Mixed Beverage',
                        'beer_and_wine' => 'Beer and Wine',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'TX']],
                    'source_name' => 'alcoholicBeveragesPermit',
                ],
                'tx_winery_outside_texas' => nullableYesNoField('Are you a winery located outside Texas shipping wine to Texas customers?', 'wineryOutsideTexas', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'TX']],
                ]),
                'tx_electronic_cigarettes_online' => nullableYesNoField('Do you sell e-cigarettes online or by mail?', 'electronicCigarettesOnline', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'TX']],
                ]),
                'tx_telecommunication_chapter_771' => nullableYesNoField('Do you provide telecommunication services under Tax Code Chapter 771?', 'telecommunicationServicesUnderChapter711', [
                    'when' => ['contains' => [['var' => '$root.applies_telecom_or_prepaid_wireless.states'], 'TX']],
                ]),
                'tx_diesel_50hp_equipment' => yesNoField('Do you sell or operate diesel-powered equipment of 50 horsepower or greater?', 'dieselPoweredEquipment'),
                'tx_health_spa' => yesNoField('Do you sell health spa memberships?', 'healthSpa'),
            ],
        ],

        'tx_business_relationships' => [
            'title' => 'Texas Business Relationships',
            'description' => 'Franchise and affiliated-business questions.',
            'fields' => [
                'tx_franchisee_in_texas' => yesNoField('Are you a franchisee or licensee operating under a name in Texas?', 'franchiseeOperatingUnderName'),
                'tx_ownership_in_similar_business' => yesNoField('Do you have substantial ownership in a similar business?', 'ownershipInSimilarBusiness'),
                'tx_ownership_business_maintains_location' => yesNoField('Do you have ownership in a business that maintains a location in Texas to promote sales?', 'ownershipInBusinessMaintainsLocation'),
                'tx_personal_bank' => nullableYesNoField('Is your business bank also your personal bank?', 'personalBank', [
                    'when' => ['==' => [['var' => '$root.has_business_bank_account'], '1']],
                ]),
            ],
        ],

        'tx_acquisition' => [
            'title' => 'Texas Acquisition Details',
            'description' => 'Shown because you purchased an existing business in Texas.',
            'groups' => [
                ['title' => 'Previous Owner', 'fields' => [
                    'tx_previous_owner_trade_name', 'tx_previous_owner_purchase_price',
                    'tx_previous_owner_taxpayer_number',
                ]],
                ['title' => 'What Was Purchased', 'fields' => [
                    'tx_purchased_inventory', 'tx_purchased_real_estate', 'tx_purchased_corporate_stock',
                    'tx_purchased_equipment', 'tx_purchased_other', 'tx_other_purchased_description',
                ]],
            ],
            'fields' => [
                'tx_previous_owner_trade_name' => [
                    'type' => 'text',
                    'label' => 'Previous Owner Trade Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'previousOwnerTradeName',
                ],
                'tx_previous_owner_purchase_price' => [
                    'type' => 'text',
                    'label' => 'Purchase Price (USD)',
                    'rules' => ['nullable', 'numeric', 'min:0'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'previousOwnerPurchasePrice',
                ],
                'tx_previous_owner_taxpayer_number' => [
                    'type' => 'text',
                    'label' => 'Previous Owner Texas Taxpayer Number',
                    'rules' => ['nullable', 'digits:11'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'previousOwnerTexasTaxpayerNumber',
                ],
                'tx_purchased_inventory' => [
                    'type' => 'checkbox',
                    'label' => 'Inventory',
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'purchased[]',
                    'source_value' => 'Inventory',
                ],
                'tx_purchased_real_estate' => [
                    'type' => 'checkbox',
                    'label' => 'Real Estate',
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'purchased[]',
                    'source_value' => 'Real Estate',
                ],
                'tx_purchased_corporate_stock' => [
                    'type' => 'checkbox',
                    'label' => 'Corporate Stock',
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'purchased[]',
                    'source_value' => 'Corporate Stock',
                ],
                'tx_purchased_equipment' => [
                    'type' => 'checkbox',
                    'label' => 'Equipment',
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'source_name' => 'purchased[]',
                    'source_value' => 'Equipment',
                ],
                'tx_purchased_other' => [
                    'type' => 'checkbox',
                    'label' => 'Other',
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'TX']],
                    'drives_conditional' => true,
                    'source_name' => 'purchased[]',
                    'source_value' => 'Other',
                ],
                'tx_other_purchased_description' => [
                    'type' => 'text',
                    'label' => 'Other Purchased — Description',
                    'rules' => ['nullable', 'string', 'max:200'],
                    'when' => ['==' => [['var' => 'tx_purchased_other'], '1']],
                    'source_name' => 'otherPurchased',
                ],
            ],
        ],
    ],
];

<?php

/**
 * California — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/california/application/` (organizationInformation,
 * primary, businessInformation, supplierInformation, generalQuestions)
 * plus matching JS validators.
 *
 * Cross-state questions collapsed into core: alcohol/tobacco/fuel/vending/
 * contractor/temporary-event/equipment- and vehicle-lease gates (applies_*),
 * monthly taxable sales + annual sales (matrix_*), bank + card processor +
 * predecessor identity (core), location type / city limits (locations[]).
 * Only CA-specific follow-ups and CDTFA-only questions remain here.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'ca_state_ids' => [
            'title' => 'California Identifiers & Prior Accounts',
            'description' => 'CDTFA identifiers and any prior California accounts.',
            'groups' => [
                ['title' => 'California Identifiers', 'fields' => [
                    'ca_seller_permit_number', 'ca_corporate_number', 'ca_llc_number',
                    'ca_secretary_of_state_number', 'ca_state_employer_id',
                    'ca_non_california_entity_number',
                ]],
                ['title' => 'Prior Organization / CDTFA Accounts', 'fields' => [
                    'ca_changing_organization', 'ca_prior_organization_name', 'ca_prior_organization_number',
                    'ca_other_cdtfa_accounts', 'ca_cdtfa_account_1', 'ca_cdtfa_account_2',
                ]],
            ],
            'fields' => [
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
                    'when' => ['in' => [['var' => '$root.entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                    'source_name' => 'corporateNumber',
                ],
                'ca_llc_number' => [
                    'type' => 'text',
                    'label' => 'CA LLC Number',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi', 'llp', 'limited_partnership']]],
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
                'ca_non_california_entity_number' => [
                    'type' => 'text',
                    'label' => 'Non-California Issued Entity Number (optional)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['!=' => [['var' => '$root.formation_state'], 'CA']],
                    'source_name' => 'nonCaliforniaEntityNumber',
                ],

                'ca_changing_organization' => yesNoField('Are you changing from a prior organization (entity change on an existing CDTFA account)?', 'changingOrganization', ['drives_conditional' => true]),
                'ca_prior_organization_name' => [
                    'type' => 'text',
                    'label' => 'Name on Prior Account',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ca_changing_organization'], '1']],
                    'source_name' => 'priorOrganizationName',
                ],
                'ca_prior_organization_number' => [
                    'type' => 'text',
                    'label' => 'Prior Account Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ca_changing_organization'], '1']],
                    'source_name' => 'priorOrganizationNumber',
                ],
                'ca_other_cdtfa_accounts' => yesNoField('Do you have other CDTFA accounts?', 'otherCDTFAAccounts', ['drives_conditional' => true]),
                'ca_cdtfa_account_1' => [
                    'type' => 'text',
                    'label' => 'CDTFA Account 1',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ca_other_cdtfa_accounts'], '1']],
                    'source_name' => 'CDTFAAccount1',
                ],
                'ca_cdtfa_account_2' => [
                    'type' => 'text',
                    'label' => 'CDTFA Account 2',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ca_other_cdtfa_accounts'], '1']],
                    'source_name' => 'CDTFAAccount2',
                ],
            ],
        ],

        'ca_sales_and_operations' => [
            'title' => 'California Sales & Operations',
            'description' => 'Nexus, projections, and CDTFA operational questions.',
            'groups' => [
                ['title' => 'Nexus & Projections', 'fields' => [
                    'ca_economic_nexus', 'ca_projected_monthly_sales', 'ca_products_that_will_be_sold',
                ]],
                ['title' => 'Internet Sales (CA detail)', 'fields' => [
                    'ca_third_party_internet_sales', 'ca_third_party_name', 'ca_third_party_website',
                ]],
                ['title' => 'Operations', 'fields' => [
                    'ca_use_tax', 'ca_purchasing_items_without_paying_tax', 'ca_auction_sales',
                    'ca_underground_storage_tanks', 'ca_registering_for_dealer_license',
                    'ca_vehicle_auctioneer',
                ]],
                ['title' => 'Contacts', 'fields' => [
                    'ca_personal_reference_name', 'ca_personal_reference_phone',
                    'ca_owner_is_records_contact', 'ca_owner_is_activities_contact',
                ]],
            ],
            'fields' => [
                'ca_economic_nexus' => yesNoField('Are you registering because your business has economic nexus in California (over $500,000 in sales)?', 'economicNexus'),
                'ca_projected_monthly_sales' => [
                    'type' => 'text',
                    'label' => 'Projected Monthly Gross Sales (USD)',
                    'rules' => ['required', 'numeric', 'min:0'],
                    'help' => 'Gross monthly sales. Taxable monthly sales were collected earlier.',
                    'source_name' => 'projectedMonthlySales',
                ],
                'ca_products_that_will_be_sold' => [
                    'type' => 'text',
                    'label' => 'Specific Products That Will Be Sold',
                    'rules' => ['required', 'string', 'max:500'],
                    'source_name' => 'productsThatWillBeSold',
                ],

                'ca_third_party_internet_sales' => nullableYesNoField('Are you making internet sales through a third party?', 'thirdPartyInternetSales', [
                    'when' => ['contains' => [['var' => '$root.applies_internet_or_mail_order.states'], 'CA']],
                    'drives_conditional' => true,
                ]),
                'ca_third_party_name' => [
                    'type' => 'text',
                    'label' => 'Third Party Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ca_third_party_internet_sales'], '1']],
                    'source_name' => 'thirdPartyName',
                ],
                'ca_third_party_website' => [
                    'type' => 'text',
                    'label' => 'Third Party Website Address',
                    'rules' => ['nullable', 'string', 'max:255'],
                    'when' => ['==' => [['var' => 'ca_third_party_internet_sales'], '1']],
                    'source_name' => 'thirdPartyInternetSalesWebsite',
                ],

                'ca_use_tax' => yesNoField('Will purchases subject to use tax be consumed at places other than your sales location?', 'subjectToUseTax'),
                'ca_purchasing_items_without_paying_tax' => yesNoField('Will you purchase items for business use without paying tax?', 'purchasingItemsWithoutPayingTax'),
                'ca_auction_sales' => yesNoField('Will you conduct auction events transacted at a temporary location?', 'eventsTransactedTempLocation'),
                'ca_underground_storage_tanks' => yesNoField('Do you own or operate underground storage tanks?', 'undergroundStorageTanks'),
                'ca_registering_for_dealer_license' => yesNoField('Are you registering for a dealer license?', 'registeringForDealerLicense'),
                'ca_vehicle_auctioneer' => yesNoField('Are you a vehicle auctioneer?', 'vehicleAuctioneer'),

                'ca_personal_reference_name' => [
                    'type' => 'text',
                    'label' => 'Personal Reference Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                    'source_name' => 'personalRefrenceName',
                ],
                'ca_personal_reference_phone' => [
                    'type' => 'text',
                    'label' => 'Personal Reference Phone Number',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
                    'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                    'source_name' => 'personalRefrencePhoneNumber',
                ],
                'ca_owner_is_records_contact' => yesNoField('Are the books and records maintained by the owner?', 'ownerBRContact', [
                    'help' => 'If no, fill in the Business Records Contact on the Additional Contacts step.',
                ]),
                'ca_owner_is_activities_contact' => yesNoField('Is the owner the contact for business activities?', 'ownerBAContact', [
                    'help' => 'If no, fill in the Authorized Contact on the Additional Contacts step.',
                ]),
            ],
        ],

        'ca_products_and_fees' => [
            'title' => 'California Product Fees',
            'description' => 'CDTFA product fee programs.',
            'groups' => [
                ['title' => 'Product Fee Programs', 'fields' => [
                    'ca_sell_new_tires', 'ca_sell_lumber', 'ca_sell_covered_devices',
                    'ca_sell_prepaid_wireless',
                ]],
                ['title' => 'Lead-Acid Batteries', 'fields' => [
                    'ca_sell_batteries', 'ca_manufacturing_lead_batteries', 'ca_selling_lead_batteries',
                ]],
            ],
            'fields' => [
                'ca_sell_new_tires' => yesNoField('Will you sell new tires?', 'sellNewTires'),
                'ca_sell_lumber' => yesNoField('Will you sell lumber or engineered wood products?', 'sellLumber'),
                'ca_sell_covered_devices' => yesNoField('Will you sell covered electronic devices subject to the recycling fee?', 'sellCoveredDevice'),
                'ca_sell_prepaid_wireless' => yesNoField('Will you sell prepaid wireless services?', 'sellPrepaidWireless'),

                'ca_sell_batteries' => yesNoField('Will you sell lead-acid batteries?', 'sellBatteries', ['drives_conditional' => true]),
                'ca_manufacturing_lead_batteries' => nullableYesNoField('Are you a manufacturer of lead-acid batteries?', 'manufacturingLeadBatteries', [
                    'when' => ['==' => [['var' => 'ca_sell_batteries'], '1']],
                ]),
                'ca_selling_lead_batteries' => nullableYesNoField('Will you sell lead-acid batteries at retail?', 'sellingLeadBatteries', [
                    'when' => ['==' => [['var' => 'ca_sell_batteries'], '1']],
                ]),
            ],
        ],

        'ca_alcohol_and_tobacco' => [
            'title' => 'California Alcohol & Tobacco',
            'description' => 'Shown because alcohol or tobacco applies to California.',
            'groups' => [
                ['title' => 'Alcohol (ABC)', 'fields' => [
                    'ca_holding_abc_license', 'ca_abc_license_number',
                    'ca_shipping_beer_out_of_state', 'ca_abc_serial_number',
                ]],
                ['title' => 'Cigarettes', 'fields' => [
                    'ca_retail_tobacco_sales', 'ca_wholesale_cigarette_sales', 'ca_importing_cigarettes',
                    'ca_manufacturing_cigarettes', 'ca_distributing_cigarettes', 'ca_stamping_cigarettes',
                ]],
                ['title' => 'Other Tobacco Products', 'fields' => [
                    'ca_other_tobacco_products_sold', 'ca_importing_other_tobacco_products',
                    'ca_distributing_other_tobacco_products', 'ca_member_of_indian_tribe',
                    'ca_tobacco_vending_machine_operator', 'ca_mobile_tobacco_vendor',
                    'ca_distribute_smokeless_tobacco', 'ca_interstate_tobacco_to_tribe',
                    'ca_interstate_tobacco_shipped_to_ca',
                ]],
            ],
            'fields' => [
                'ca_holding_abc_license' => nullableYesNoField('Do you hold an ABC (Alcoholic Beverage Control) license?', 'holdingABCLicense', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'CA']],
                    'drives_conditional' => true,
                ]),
                'ca_abc_license_number' => [
                    'type' => 'text',
                    'label' => 'ABC License Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ca_holding_abc_license'], '1']],
                    'source_name' => 'ABCLicenseNumber',
                ],
                'ca_shipping_beer_out_of_state' => nullableYesNoField('Will you ship beer out of state?', 'shippingBeerOutOfState', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'CA']],
                    'drives_conditional' => true,
                ]),
                'ca_abc_serial_number' => [
                    'type' => 'text',
                    'label' => 'ABC Serial Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ca_shipping_beer_out_of_state'], '1']],
                    'source_name' => 'ABCSerialNumber',
                ],

                'ca_retail_tobacco_sales' => nullableYesNoField('Will you make retail sales of cigarettes or tobacco products?', 'retailTobaccoSales', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_wholesale_cigarette_sales' => nullableYesNoField('Will you make wholesale sales of cigarettes?', 'wholesaleCigaretteSales', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_importing_cigarettes' => nullableYesNoField('Will you import cigarettes?', 'importingCigarettes', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_manufacturing_cigarettes' => nullableYesNoField('Will you manufacture cigarettes?', 'manufacturingCigarettes', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_distributing_cigarettes' => nullableYesNoField('Will you distribute cigarettes?', 'distributingCigarettes', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_stamping_cigarettes' => nullableYesNoField('Will you stamp cigarettes (tax stamps)?', 'stampingCigarettes', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),

                'ca_other_tobacco_products_sold' => nullableYesNoField('Will you sell other tobacco products?', 'otherTobaccoProductsSold', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_importing_other_tobacco_products' => nullableYesNoField('Will you import other tobacco products?', 'importingOtherTobaccoProducts', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_distributing_other_tobacco_products' => nullableYesNoField('Will you distribute other tobacco products?', 'distributingOtherTobaccoProducts', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_member_of_indian_tribe' => nullableYesNoField('Are you a member of an Indian tribe?', 'memberOfIndianTribe', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_tobacco_vending_machine_operator' => nullableYesNoField('Are you a tobacco vending machine operator?', 'tobaccoVendingMachineOperator', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_mobile_tobacco_vendor' => nullableYesNoField('Are you a mobile tobacco vendor?', 'mobileTobaccoVendor', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_distribute_smokeless_tobacco' => nullableYesNoField('Will you distribute smokeless tobacco?', 'distributeSmokelessTobacco', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_interstate_tobacco_to_tribe' => nullableYesNoField('Will you engage in interstate commerce of tobacco sold to a Native American tribe?', 'interstateCommerceOfTobaccoToNativeAmericanTribe', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
                'ca_interstate_tobacco_shipped_to_ca' => nullableYesNoField('Will you engage in interstate commerce of tobacco shipped into California?', 'interstateCommerceOfTobaccoShippedToCalifornia', [
                    'when' => ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'CA']],
                ]),
            ],
        ],

        'ca_fuel' => [
            'title' => 'California Fuel Programs',
            'description' => 'Shown because fuel products apply to California.',
            'groups' => [
                ['title' => 'Fuel Activities', 'fields' => [
                    'ca_sell_fuel_at_retail', 'ca_operating_gas_station', 'ca_sell_fuels_other_than_gas_diesel',
                    'ca_selling_tax_paid_fuel', 'ca_resale_fuel', 'ca_cardlock_network', 'ca_wholesale_fuel',
                    'ca_import_fuel_to_california', 'ca_selling_and_delivering_fuel',
                    'ca_selling_and_delivering_jet_fuel', 'ca_biodiesel_from_waste',
                    'ca_control_terminal_pipeline', 'ca_selling_diesel_to_train',
                    'ca_operating_marine_terminal', 'ca_transport_crude_oil',
                ]],
                ['title' => 'Fuel Products Owned in a Bulk System', 'fields' => [
                    'ca_bulk_fuel_diesel', 'ca_bulk_fuel_gasoline', 'ca_bulk_fuel_aviation',
                ]],
                ['title' => 'Fuel Products Imported', 'fields' => [
                    'ca_import_fuel_diesel', 'ca_import_fuel_gasoline', 'ca_import_fuel_aviation',
                ]],
                ['title' => 'Fuel Products Blended', 'fields' => [
                    'ca_blend_fuel_diesel', 'ca_blend_fuel_gasoline', 'ca_blend_fuel_aviation',
                ]],
            ],
            'fields' => array_merge(
                collect([
                    'ca_sell_fuel_at_retail' => ['Will you sell fuel at retail?', 'sellFuelAtRetail'],
                    'ca_operating_gas_station' => ['Will you operate a gas station?', 'opeartingGasStation'],
                    'ca_sell_fuels_other_than_gas_diesel' => ['Will you sell fuels other than gasoline or diesel?', 'sellFuelsOtherThanGasolineOrDiesel'],
                    'ca_selling_tax_paid_fuel' => ['Will you sell tax-paid fuel?', 'sellingTaxPaidFuel'],
                    'ca_resale_fuel' => ['Will you sell fuel for resale?', 'resaleFuel'],
                    'ca_cardlock_network' => ['Do you participate in a cardlock network?', 'participateInCardlockNetwork'],
                    'ca_wholesale_fuel' => ['Will you sell fuel at wholesale?', 'wholesaleFuel'],
                    'ca_import_fuel_to_california' => ['Will you import fuel into California?', 'importFuelToCalifornia'],
                    'ca_selling_and_delivering_fuel' => ['Will you sell and deliver fuel?', 'sellingAndDeliveringFuel'],
                    'ca_selling_and_delivering_jet_fuel' => ['Will you sell and deliver jet fuel?', 'sellingAndDeliveringJetFuel'],
                    'ca_biodiesel_from_waste' => ['Will you produce biodiesel from waste products?', 'biodieselFromWaste'],
                    'ca_control_terminal_pipeline' => ['Do you control a terminal or pipeline?', 'controlTerminalPipeline'],
                    'ca_selling_diesel_to_train' => ['Will you sell diesel to train operators?', 'sellingDieselToTrain'],
                    'ca_operating_marine_terminal' => ['Will you operate a marine terminal?', 'operatinMarineTerminal'],
                    'ca_transport_crude_oil' => ['Will you transport crude oil?', 'transportCrudeOil'],
                ])->map(fn ($def) => nullableYesNoField($def[0], $def[1], [
                    'when' => ['contains' => [['var' => '$root.applies_fuel.states'], 'CA']],
                ]))->all(),
                collect([
                    'ca_bulk_fuel_diesel' => ['Diesel fuel', 'fuelProductsOwnedInBulkSystem[]', 'Diesel fuel'],
                    'ca_bulk_fuel_gasoline' => ['Motor vehicle fuel (gasoline)', 'fuelProductsOwnedInBulkSystem[]', 'Motor vehicle fuel (gasoline)'],
                    'ca_bulk_fuel_aviation' => ['Aviation gasoline', 'fuelProductsOwnedInBulkSystem[]', 'Aviation gasoline'],
                    'ca_import_fuel_diesel' => ['Diesel fuel', 'importFuel[]', 'Diesel fuel'],
                    'ca_import_fuel_gasoline' => ['Motor vehicle fuel (gasoline)', 'importFuel[]', 'Motor vehicle fuel (gasoline)'],
                    'ca_import_fuel_aviation' => ['Aviation gasoline', 'importFuel[]', 'Aviation gasoline'],
                    'ca_blend_fuel_diesel' => ['Diesel fuel', 'blendFuel[]', 'Diesel fuel'],
                    'ca_blend_fuel_gasoline' => ['Motor vehicle fuel (gasoline)', 'blendFuel[]', 'Motor vehicle fuel (gasoline)'],
                    'ca_blend_fuel_aviation' => ['Aviation gasoline', 'blendFuel[]', 'Aviation gasoline'],
                ])->map(fn ($def) => [
                    'type' => 'checkbox',
                    'label' => $def[0],
                    'when' => ['contains' => [['var' => '$root.applies_fuel.states'], 'CA']],
                    'source_name' => $def[1],
                    'source_value' => $def[2],
                ])->all(),
            ),
        ],

        'ca_supplier' => [
            'title' => 'California Supplier',
            'description' => 'CDTFA requires your primary supplier.',
            'fields' => [
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

        'ca_acquisition' => [
            'title' => 'California Acquisition Details',
            'description' => 'Shown because you purchased an existing business in California.',
            'fields' => [
                'ca_former_owner_account_number' => [
                    'type' => 'text',
                    'label' => "Former Owner's CDTFA Account Number",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'CA']],
                    'source_name' => 'formerOwnerAccountNumber',
                ],
                'ca_purchase_price' => [
                    'type' => 'text',
                    'label' => 'Purchase Price (USD)',
                    'rules' => ['nullable', 'numeric', 'min:0'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'CA']],
                    'source_name' => 'previousOwnerPurchasePrice',
                ],
                'ca_value_of_fixtures' => [
                    'type' => 'text',
                    'label' => 'Value of Purchased Fixtures and Equipment (USD)',
                    'rules' => ['nullable', 'numeric', 'min:0'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'CA']],
                    'source_name' => 'valueOfPurchasedFixtures',
                ],
                'ca_escrow_company_name' => [
                    'type' => 'text',
                    'label' => 'Escrow Company Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'CA']],
                    'source_name' => 'escrowCompanyName',
                ],
                'ca_escrow_number' => [
                    'type' => 'text',
                    'label' => 'Escrow Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'CA']],
                    'source_name' => 'escrowNumber',
                ],
            ],
        ],
    ],
];

<?php

/**
 * Oklahoma — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/oklahoma/application/` (reasonForRegistration,
 * generalInformation, businessInformation, physicalLocationInformation,
 * generalQuestions partials, agreements).
 *
 * Collapsed into core: remote seller / contractor / retail sales / cards /
 * lodging / vending gates (applies_*), first retail sales date (matrix_
 * first_sales_date), card processor taxpayer ID + bank block (core),
 * home-based / city / county limits (locations[] rows), main contact
 * (core authorized contact).
 *
 * §3A.2 fixes applied: the 10 tobacco agreements restored as Y/N radios
 * with the legacy legal text; eligibility-blocking answers enforced via
 * the ok_tobacco_eligibility cross-validation note (validation handled by
 * the per-field rules + the review step; blocking semantics documented).
 */
$okGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'OK']];

$okTobaccoGate = ['contains' => [['var' => '$root.applies_tobacco_vape.states'], 'OK']];

$okUnitTypes = [
    'Apartment', 'Basement', 'Building', 'Department', 'Floor', 'Front', 'Hangar', 'Lobby',
    'Lot', 'Number', 'Office', 'Penthouse', 'Pier', 'Rear', 'Room', 'Side', 'Slip', 'Space',
    'Stop', 'Suite', 'Trailer', 'Unit', 'Upper',
];

$okLodgingJurisdictions = [
    'ATOKA', 'BLAINE COUNTY', 'CARLTON LANDING', 'CHANDLER', 'CHEROKEE', 'CIMARRON COUNTY',
    'COAL COUNTY', 'COTTON COUNTY', 'DRUMRIGHT (CREEK)', 'DRUMRIGHT (PAYNE)', 'DURANT',
    'EUFAULA', 'GREER COUNTY', 'GROVE', 'JEFFERSON COUNTY', 'JOHNSTON COUNTY',
    'LATIMER COUNTY', 'LOVE COUNTY', 'MARSHALL COUNTY', 'MCCURTAIN COUNTY', 'MUSKOGEE COUNTY',
    'OSAGE COUNTY', 'PONTOTOC COUNTY', 'SAND SPRINGS (OSAGE)', 'SAND SPRINGS (TULSA)',
    'STILLWATER', 'STROUD (CREEK)', 'STROUD (LINCOLN)', 'SULPHUR', 'WAYNOKA',
];

// 10 tobacco agreements — legacy legal text, Y/N radios. Answers marked
// "blocks" make the application ineligible per legacy JS; surfaced via help.
$okTobaccoAgreements = [
    'ok_tobacco_agreement_one' => ['Applicant agrees to the jurisdiction of the Oklahoma Tax Commission.', 'tobaccoAgreementOne'],
    'ok_tobacco_agreement_two' => ['Applicant agrees to abide by the provisions of Title 68.', 'tobaccoAgreementTwo'],
    'ok_tobacco_agreement_three' => ['Applicant agrees that they shall not purchase any cigarettes or tobacco products from unlicensed sources.', 'tobaccoAgreementThree'],
    'ok_tobacco_agreement_four' => ['Applicant agrees to sell cigarettes or tobacco products only to consumers.', 'tobaccoAgreementFour'],
    'ok_tobacco_agreement_five' => ['Have you been convicted for violation of any law pertaining to controlled substances?', 'tobaccoAgreementFive'],
    'ok_tobacco_agreement_six' => ['Applicant agrees to sell cigarettes and/or tobacco products only to a licensed wholesaler.', 'tobaccoAgreementSix'],
    'ok_tobacco_agreement_seven' => ['I am a participating manufacturer as defined in the Master Settlement Agreement.', 'tobaccoAgreementSeven'],
    'ok_tobacco_agreement_eight' => ['I am in full compliance with Section 600.23.', 'tobaccoAgreementEight'],
    'ok_tobacco_agreement_nine' => ['Are any of the cigarettes you import into the United States in violation of 19 U.S.C. Section 1681A?', 'tobaccoAgreementNine'],
    'ok_tobacco_agreement_ten' => ['Are any of the cigarettes imported or manufactured not in compliance with the Federal Cigarette Labeling Act?', 'tobaccoAgreementTen'],
];

return [
    'extends' => 'base',

    'state_steps' => [
        'ok_registration_and_accounts' => [
            'title' => 'Oklahoma Registration & Tax Accounts',
            'description' => 'OTC identifiers and additional tax accounts.',
            'groups' => [
                ['title' => 'Identifiers & Classification', 'fields' => [
                    'ok_secretary_of_state_number', 'ok_ownership_type',
                ]],
                ['title' => 'Additional Tax Accounts', 'fields' => [
                    'ok_need_vendor_use_account', 'ok_vendor_use_tax_start_date',
                    'ok_franchise_tax_account',
                ]],
                ['title' => 'Withholding (OK detail)', 'fields' => [
                    'ok_more_than_500_per_quarter', 'ok_federal_deposits_more_than_monthly',
                    'ok_register_wage_withholding', 'ok_type_of_wage_withholding',
                    'ok_wage_withholding_begin_date',
                    'ok_register_royalty_withholding', 'ok_royalty_withholding_begin_date',
                    'ok_register_pass_through_withholding', 'ok_pass_through_begin_date',
                ]],
            ],
            'fields' => [
                'ok_secretary_of_state_number' => [
                    'type' => 'text',
                    'label' => 'OK Secretary of State Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'source_name' => 'secretaryOfStateNumber',
                ],
                'ok_ownership_type' => [
                    'type' => 'select',
                    'label' => 'OK Ownership Type',
                    'options' => [
                        'association' => 'Association',
                        'church' => 'Church',
                        'cooperative' => 'Cooperative',
                        'corporation' => 'Corporation',
                        'estate' => 'Estate',
                        'foreign_country' => 'Foreign Country',
                        'general_partnership' => 'General Partnership',
                        'llc' => 'Limited Liability Company',
                        'llp' => 'Limited Liability Partnership',
                        'limited_partnership' => 'Limited Partnership',
                        'non_profit' => 'Non Profit',
                        'receivership' => 'Receivership',
                        'school' => 'School',
                        'tribal' => 'Tribal',
                        'trust' => 'Trust',
                        'sole_proprietor' => 'Sole Proprietor',
                    ],
                    'rules' => ['required'],
                    'source_name' => 'ownershipType',
                ],
                'ok_need_vendor_use_account' => yesNoField('Do you need a vendor use account?', 'needVendorUseAccount', ['drives_conditional' => true]),
                'ok_vendor_use_tax_start_date' => [
                    'type' => 'date',
                    'label' => 'Vendor Use Tax Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_need_vendor_use_account'], '1']],
                    'source_name' => 'vendorUseTaxStartDate',
                ],
                'ok_franchise_tax_account' => yesNoField('Do you need a franchise tax account?', 'needFranchiseTaxAccount'),

                'ok_more_than_500_per_quarter' => nullableYesNoField('Do you expect to withhold more than $500 per quarter?', 'moreThan500PerQuarter', [
                    'when' => $okGate('applies_employees_or_payroll'),
                ]),
                'ok_federal_deposits_more_than_monthly' => nullableYesNoField('Are you required to make federal withholding tax deposits more than once a month?', 'withholdingTaxDepositsMoreThanOnceAMonth', [
                    'when' => $okGate('applies_employees_or_payroll'),
                ]),
                'ok_register_wage_withholding' => nullableYesNoField('Do you need to register a new wage withholding account?', 'registerANewWageWithholdingAccount', [
                    'when' => $okGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'ok_type_of_wage_withholding' => [
                    'type' => 'radio',
                    'label' => 'Type of Wage Withholding',
                    'options' => ['1' => 'Payroll', '0' => 'Retirement'],
                    'rules' => ['nullable', 'in:0,1'],
                    'when' => ['==' => [['var' => 'ok_register_wage_withholding'], '1']],
                    'source_name' => 'typeOfWageWithholding',
                ],
                'ok_wage_withholding_begin_date' => [
                    'type' => 'date',
                    'label' => 'Wage Withholding Begin Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_register_wage_withholding'], '1']],
                    'source_name' => 'wageWithholdingBeginDate',
                ],
                'ok_register_royalty_withholding' => nullableYesNoField('Do you need to register a new royalty withholding account?', 'registerANewRoyaltyWithholdingAccount', [
                    'when' => $okGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'ok_royalty_withholding_begin_date' => [
                    'type' => 'date',
                    'label' => 'Royalty Withholding Begin Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_register_royalty_withholding'], '1']],
                    'source_name' => 'royaltyWithholdingBeginDate',
                ],
                'ok_register_pass_through_withholding' => nullableYesNoField('Do you need to register a new pass-through withholding account?', 'registerANewPassThroughWithholdingAccount', [
                    'when' => $okGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'ok_pass_through_begin_date' => [
                    'type' => 'date',
                    'label' => 'Pass-Through Withholding Begin Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_register_pass_through_withholding'], '1']],
                    'source_name' => 'passThroughWithholdingBeginDate',
                ],
            ],
        ],

        'ok_sales_activities' => [
            'title' => 'Oklahoma Sales Activities',
            'description' => 'Oklahoma sales tax account questions.',
            'groups' => [
                ['title' => 'Sales Activities', 'fields' => [
                    'ok_purchase_inventory_vending_or_lease', 'ok_sell_electronic_cigarettes_retail',
                    'ok_process_or_dispense_marijuana', 'ok_grow_marijuana',
                    'ok_sell_prepaid_wireless', 'ok_rural_electric_coop',
                    'ok_rent_vehicles', 'ok_collecting_911_fee', 'ok_sell_tires',
                    'ok_deal_scrap_metal', 'ok_provide_taxable_services',
                    'ok_sell_motor_fuel_convenience_store', 'ok_ship_wine_directly',
                ]],
                ['title' => 'Mobile Vendor', 'fields' => [
                    'ok_mobile_vendor', 'ok_mobile_vendor_selling_food', 'ok_state_health_license_number',
                ]],
                ['title' => 'Vending Machines (OK detail)', 'fields' => [
                    'ok_distribute_vending_machines', 'ok_distribute_vending_start_date',
                    'ok_operate_vending_machines', 'ok_operate_vending_start_date',
                ]],
                ['title' => 'Lodging (OK detail)', 'fields' => ['ok_lodging_jurisdiction', 'ok_lodging_not_listed']],
                ['title' => 'Previous Business at This Location', 'fields' => [
                    'ok_business_previously_at_location', 'ok_previous_owner_name_at_location',
                    'ok_previous_owner_permit_number', 'ok_tangible_goods_purchased',
                    'ok_sales_tax_paid_on_tangible_items',
                ]],
            ],
            'fields' => array_merge(
                [
                    'ok_purchase_inventory_vending_or_lease' => yesNoField('Do you purchase inventory for vending machines or lease equipment?', 'purchaseInventoryForVendingMachinesOrLeaseEquipment'),
                    'ok_sell_electronic_cigarettes_retail' => nullableYesNoField('Will you sell electronic cigarettes at retail?', 'sellElectronicCigarettesOrTobaccoInRetail', [
                        'when' => $okTobaccoGate,
                    ]),
                    'ok_process_or_dispense_marijuana' => nullableYesNoField('Do you process or dispense medical marijuana?', 'processOrDispenseMedicalMarijuana', [
                        'when' => $okGate('applies_cannabis'),
                    ]),
                    'ok_grow_marijuana' => nullableYesNoField('Do you grow medical marijuana?', 'growMedicalMarijuana', [
                        'when' => $okGate('applies_cannabis'),
                    ]),
                    'ok_sell_prepaid_wireless' => nullableYesNoField('Will you sell prepaid wireless service?', 'sellPrepaidWirelessService', [
                        'when' => $okGate('applies_telecom_or_prepaid_wireless'),
                    ]),
                    'ok_rural_electric_coop' => yesNoField('Are you a rural electric co-op?', 'ruralElectricCoOp'),
                    'ok_rent_vehicles' => nullableYesNoField('Will you rent vehicles to fleets or the public?', 'rentVehiclesPublicOrFleet', [
                        'when' => $okGate('applies_vehicle_rentals'),
                    ]),
                    'ok_collecting_911_fee' => nullableYesNoField('Will you collect the 911 fee on phone services?', 'collecting911Fee', [
                        'when' => $okGate('applies_telecom_or_prepaid_wireless'),
                    ]),
                    'ok_sell_tires' => yesNoField('Will you sell tires?', 'sellTires'),
                    'ok_deal_scrap_metal' => yesNoField('Will you deal scrap metal?', 'dealScrapMetal'),
                    'ok_provide_taxable_services' => nullableYesNoField('Will you provide taxable services (e.g., tanning salon, photography)?', 'provideTaxableServices', [
                        'when' => $okGate('applies_taxable_services'),
                    ]),
                    'ok_sell_motor_fuel_convenience_store' => yesNoField('Will you sell motor fuel from a convenience store?', 'sellMotorFuelFromConvenienceStore'),
                    'ok_ship_wine_directly' => nullableYesNoField('Will you ship wine directly to Oklahoma consumers?', 'shipWineDirectly', [
                        'when' => $okGate('applies_alcohol'),
                    ]),
                    'ok_mobile_vendor' => yesNoField('Are you a mobile vendor?', 'mobileVendor', ['drives_conditional' => true]),
                    'ok_mobile_vendor_selling_food' => nullableYesNoField('Will you sell food as a mobile vendor?', 'mobileVendorSellingFood', [
                        'when' => ['==' => [['var' => 'ok_mobile_vendor'], '1']],
                        'drives_conditional' => true,
                    ]),
                    'ok_state_health_license_number' => [
                        'type' => 'text',
                        'label' => 'State Health License Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ok_mobile_vendor_selling_food'], '1']],
                        'source_name' => 'stateHealthLicenseNumber',
                    ],
                    'ok_distribute_vending_machines' => nullableYesNoField('Do you distribute vending machines?', 'distributeVendingMachines', [
                        'when' => $okGate('applies_vending'),
                        'drives_conditional' => true,
                    ]),
                    'ok_distribute_vending_start_date' => [
                        'type' => 'date',
                        'label' => 'Coin-operated vending machine distributor start date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'ok_distribute_vending_machines'], '1']],
                        'source_name' => 'coinOperatedVendingMachineStartDate',
                    ],
                    'ok_operate_vending_machines' => nullableYesNoField('Do you operate vending machines?', 'operateVendingMachines', [
                        'when' => $okGate('applies_vending'),
                        'drives_conditional' => true,
                    ]),
                    'ok_operate_vending_start_date' => [
                        'type' => 'date',
                        'label' => 'Vending machine operator start date',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'ok_operate_vending_machines'], '1']],
                        'source_name' => 'operateVendingMachinesStartDate',
                    ],
                    'ok_lodging_jurisdiction' => [
                        'type' => 'select',
                        'label' => 'Lodging City/County',
                        'options' => array_combine($okLodgingJurisdictions, $okLodgingJurisdictions),
                        'rules' => ['nullable'],
                        'when' => $okGate('applies_lodging_or_rentals'),
                        'source_name' => 'lodgingInformationCityCounty',
                    ],
                    'ok_lodging_not_listed' => [
                        'type' => 'checkbox',
                        'label' => 'My city/county is not listed',
                        'when' => $okGate('applies_lodging_or_rentals'),
                        'source_name' => 'cityNotListedLodging[]',
                        'source_value' => 'My City/ County is not listed',
                    ],
                    'ok_business_previously_at_location' => yesNoField('Did this location have a business previously?', 'businessPreviouslyAtLocation', ['drives_conditional' => true]),
                    'ok_previous_owner_name_at_location' => [
                        'type' => 'text',
                        'label' => 'Name of Previous Owner',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'ok_business_previously_at_location'], '1']],
                        'source_name' => 'nameOfPreviousOwner',
                    ],
                    'ok_previous_owner_permit_number' => [
                        'type' => 'text',
                        'label' => 'Permit Number of the Previous Owner',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ok_business_previously_at_location'], '1']],
                        'source_name' => 'previousOwnerPermitNumber',
                    ],
                    'ok_tangible_goods_purchased' => nullableYesNoField('Were any tangible items purchased from the previous owner?', 'tangibleGoodsPurchasedFromPreviousOwner', [
                        'when' => ['==' => [['var' => 'ok_business_previously_at_location'], '1']],
                        'drives_conditional' => true,
                    ]),
                    'ok_sales_tax_paid_on_tangible_items' => nullableYesNoField('Was sales tax paid on the purchased tangible items?', 'salesTaxPaidForTangibleItems', [
                        'when' => ['==' => [['var' => 'ok_tangible_goods_purchased'], '1']],
                    ]),
                ],
            ),
        ],

        'ok_alcohol_and_tobacco' => [
            'title' => 'Oklahoma Alcohol & Tobacco Retail',
            'description' => 'Shown because alcohol or tobacco applies to Oklahoma.',
            'groups' => [
                ['title' => 'Alcohol Retail', 'fields' => [
                    'ok_sell_alcohol_on_premise',
                    'ok_on_premise_mixed_beverage', 'ok_on_premise_mixed_caterer',
                    'ok_on_premise_caterer', 'ok_on_premise_beer_wine',
                    'ok_sell_alcohol_off_premise', 'ok_able_number_off_premise',
                    'ok_operate_liquor_store', 'ok_able_number_liquor_store',
                ]],
                ['title' => 'Tobacco Retail Eligibility', 'fields' => [
                    'ok_sell_tobacco_products', 'ok_sell_cigarette_products',
                    'ok_owe_delinquent_tobacco_taxes', 'ok_tobacco_license_revoked',
                    'ok_convicted_tobacco_crime',
                ]],
                ['title' => 'Tobacco Agreements', 'fields' => array_keys($okTobaccoAgreements)],
            ],
            'fields' => array_merge(
                [
                    'ok_sell_alcohol_on_premise' => nullableYesNoField('Do you sell alcohol for on-premise consumption?', 'sellAlcoholForOnPremiseConsumption', [
                        'when' => $okGate('applies_alcohol'),
                        'drives_conditional' => true,
                    ]),
                ],
                collect([
                    'ok_on_premise_mixed_beverage' => ['Mixed Beverage', 'Mixed Beverage'],
                    'ok_on_premise_mixed_caterer' => ['Mixed Beverage & Caterer', 'Mixed Beverage & Caterer'],
                    'ok_on_premise_caterer' => ['Caterer', 'Caterer'],
                    'ok_on_premise_beer_wine' => ['Beer & Wine', 'Beer & Wine'],
                ])->map(fn ($def) => [
                    'type' => 'checkbox',
                    'label' => $def[0],
                    'when' => ['==' => [['var' => 'ok_sell_alcohol_on_premise'], '1']],
                    'source_name' => 'onPremiseAlcoholTypes[]',
                    'source_value' => $def[1],
                ])->all(),
                [
                    'ok_sell_alcohol_off_premise' => nullableYesNoField('Do you sell beer/wine for off-premise consumption?', 'sellAlcoholForOffPremiseConsumption', [
                        'when' => ['==' => [['var' => 'ok_sell_alcohol_on_premise'], '0']],
                        'drives_conditional' => true,
                    ]),
                    'ok_able_number_off_premise' => [
                        'type' => 'text',
                        'label' => 'ABLE # (off-premise)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ok_sell_alcohol_off_premise'], '1']],
                        'source_name' => 'ableNumberForOffPremise',
                    ],
                    'ok_operate_liquor_store' => nullableYesNoField('Do you operate a liquor store?', 'operateLiquorStore', [
                        'when' => ['==' => [['var' => 'ok_sell_alcohol_on_premise'], '0']],
                        'drives_conditional' => true,
                    ]),
                    'ok_able_number_liquor_store' => [
                        'type' => 'text',
                        'label' => 'ABLE # (liquor store)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'ok_operate_liquor_store'], '1']],
                        'source_name' => 'ableNumberForLiquorStore',
                    ],

                    'ok_sell_tobacco_products' => nullableYesNoField('Do you sell tobacco products?', 'sellTobaccoProducts', [
                        'when' => $okTobaccoGate,
                    ]),
                    'ok_sell_cigarette_products' => nullableYesNoField('Do you sell cigarette products?', 'sellCigaretteProducts', [
                        'when' => $okTobaccoGate,
                    ]),
                    'ok_owe_delinquent_tobacco_taxes' => nullableYesNoField('Do you owe $500.00 or more in delinquent cigarette or tobacco taxes?', 'oweMoreThan500InDeliquentTobaccoTaxes', [
                        'when' => $okTobaccoGate,
                        'help' => 'Answering Yes makes the business ineligible for an OK tobacco license.',
                    ]),
                    'ok_tobacco_license_revoked' => nullableYesNoField('Have you had a cigarette/tobacco license revoked?', 'tobaccoLicenseRevoked', [
                        'when' => $okTobaccoGate,
                        'help' => 'Answering Yes makes the business ineligible for an OK tobacco license.',
                    ]),
                    'ok_convicted_tobacco_crime' => nullableYesNoField('Have you been convicted of a crime relating to stolen or counterfeit cigarettes?', 'convictedOfCrimeTobacco', [
                        'when' => $okTobaccoGate,
                        'help' => 'Answering Yes makes the business ineligible for an OK tobacco license.',
                    ]),
                ],
                collect($okTobaccoAgreements)->map(fn ($def) => nullableYesNoField($def[0], $def[1], [
                    'when' => $okTobaccoGate,
                ]))->all(),
            ),
        ],

        'ok_wholesale_licensing' => [
            'title' => 'Oklahoma Wholesale Licensing',
            'description' => 'Alcohol, cigarette, and tobacco wholesale licensing.',
            'groups' => [
                ['title' => 'Alcohol Wholesale', 'fields' => [
                    'ok_alcohol_wholesaler', 'ok_alcohol_permit_type', 'ok_alcohol_wholesale_address',
                ]],
                ['title' => 'Cigarette Wholesale', 'fields' => [
                    'ok_cigarette_wholesaler', 'ok_cigarette_permit_type',
                    'ok_cigarette_manufacturer_start_date', 'ok_cigarette_wholesale_address',
                ]],
                ['title' => 'Tobacco Wholesale', 'fields' => [
                    'ok_tobacco_wholesaler', 'ok_tobacco_permit_type',
                    'ok_tobacco_manufacturer_start_date', 'ok_tobacco_wholesale_address',
                ]],
            ],
            'fields' => [
                'ok_alcohol_wholesaler' => nullableYesNoField('Are you an alcohol wholesaler, distributor, or direct wine shipper?', 'alcoholWholesaler', [
                    'when' => $okGate('applies_alcohol'),
                    'drives_conditional' => true,
                ]),
                'ok_alcohol_permit_type' => [
                    'type' => 'select',
                    'label' => 'Alcohol Permit Type',
                    'options' => [
                        'beer_brew_pub' => 'Beer Brew Pub',
                        'beer_brewster' => 'Beer Brewster',
                        'beer_wholesale' => 'Beer Wholesale/Distributor',
                        'direct_wine_shipper' => 'Direct Wine Shipper',
                        'distiller_rectifier' => 'Distiller-Rectifier',
                        'liquor_wholesale' => 'Liquor Wholesale',
                        'non_resident_seller' => 'Non-Resident Seller',
                        'winery' => 'Winery',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_alcohol_wholesaler'], '1']],
                    'source_name' => 'alcoholPermitType',
                ],
                'ok_alcohol_wholesale_address' => [
                    'type' => 'address',
                    'label' => 'Alcohol Wholesale Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_alcohol_wholesaler'], '1']],
                ],

                'ok_cigarette_wholesaler' => nullableYesNoField('Are you a cigarette wholesaler and/or manufacturer?', 'cigaretteWholesalerOrManufacturer', [
                    'when' => $okTobaccoGate,
                    'drives_conditional' => true,
                ]),
                'ok_cigarette_permit_type' => [
                    'type' => 'select',
                    'label' => 'Cigarette Permit Type',
                    'options' => [
                        'distributor' => 'Cigarette Distributor',
                        'manufacturer' => 'Cigarette Manufacturer',
                        'wholesale' => 'Cigarette Wholesale',
                        'joint' => 'Joint Wholesaler Cigarette & Tobacco',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_cigarette_wholesaler'], '1']],
                    'source_name' => 'cigarettePermitType',
                ],
                'ok_cigarette_manufacturer_start_date' => [
                    'type' => 'date',
                    'label' => 'Cigarette Manufacturer Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_cigarette_wholesaler'], '1']],
                    'source_name' => 'cigaretteManufacturerStartDate',
                ],
                'ok_cigarette_wholesale_address' => [
                    'type' => 'address',
                    'label' => 'Cigarette Wholesale Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_cigarette_wholesaler'], '1']],
                ],

                'ok_tobacco_wholesaler' => nullableYesNoField('Are you a tobacco wholesaler and/or manufacturer?', 'tobaccoWholesalerOrManufacturer', [
                    'when' => ['==' => [['var' => 'ok_cigarette_wholesaler'], '0']],
                    'drives_conditional' => true,
                ]),
                'ok_tobacco_permit_type' => [
                    'type' => 'select',
                    'label' => 'Tobacco Permit Type',
                    'options' => [
                        'distributor' => 'Tobacco Distributor',
                        'manufacturer' => 'Tobacco Manufacturer',
                    ],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_tobacco_wholesaler'], '1']],
                    'source_name' => 'tobaccoPermitType',
                ],
                'ok_tobacco_manufacturer_start_date' => [
                    'type' => 'date',
                    'label' => 'Tobacco Manufacturer Start Date',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'ok_tobacco_wholesaler'], '1']],
                    'source_name' => 'tobaccoManufacturerStartDate',
                ],
                'ok_tobacco_wholesale_address' => [
                    'type' => 'address',
                    'label' => 'Tobacco Wholesale Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ok_tobacco_wholesaler'], '1']],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ok_commence_date' => [
                                'type' => 'date',
                                'label' => 'Date Person Commenced Responsibility',
                                'rules' => ['required', 'date'],
                                'source_name' => 'primaryContactCommenceDate',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

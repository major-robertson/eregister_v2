<?php

/**
 * Illinois — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/illinois/application/` (organizationInformation,
 * primary, businessInformation, entityQuestions, salesActivity).
 *
 * Collapsed into core: unitary group + disregarded entity + publicly
 * traded + ticker (entity/corporate extras), cigarettes/tobacco/fuel/
 * vending/cannabis/utilities/general-merchandise gates (applies_*),
 * internet sales + website (applies_internet_or_mail_order), IL payroll
 * begin date (matrix_payroll_begin_date), annual sales (matrix).
 *
 * §3A.2 fixes applied here: liquorAtRetailPlace options restored to the
 * legacy list (Eating Place / Drinking Place / Liquor Store).
 */
$ilBusinessTypes = [
    'ADVERTISING, BUSINESS SERVICES', 'AUTO SUPPLIES', 'BOOKS, JEWELRY, GIFTS, CAMERAS',
    'BUILDING TRADES, CONSTRUCTION, CONTRACTORS', 'CLOTHING AND ACCESSORIES',
    'COIN-OPERATED AMUSEMENT DEVICES', 'COMMUNICATION', 'COMPUTER/PROGRAMMING/SOFTWARE',
    'DENTAL, MEDICAL SERVICES/FACILITIES', 'DEPT. STORE/GENERAL MERCHANDISE',
    'DRINKING PLACES', 'EATING PLACES', 'ELECTRIC', 'ELECTRONICS, TELEVISIONS, MUSIC',
    'FORESTRY, LIVESTOCK, AGRICULTURE, FISHING', 'FURNITURE, FLOORING, APPLIANCES',
    'GASOLINE, OTHER PETROLEUM PRODUCTS', 'GROCERY ITEMS', 'HARDWARE',
    'HOMES - MOBILE/MODULAR', 'HOTEL/MOTEL', 'INSTRUMENTS, EQUIPMENT', 'LEASING',
    'LIQUOR', 'LUMBER, BUILDING MATERIALS', 'MACHINERY PARTS',
    'MAIL ORDER, DIRECT/VENDING SALES', 'MEDICAL SUPPLIES', 'METALS, RUBBER, PLASTIC',
    'MINING, COAL, OTHER MINERALS', 'NATURAL GAS', 'NOT-FOR-PROFIT BUSINESS/ORGANIZATION',
    'NURSERY, FLORISTS, GARDEN SUPPLIES', 'OTHER MFGING NOT LISTED',
    'OTHER RETAIL NOT LISTED', 'OTHER SERVICES NOT LISTED', 'OTHER WHOLESALE NOT LISTED',
    'PAPER, TEXTILES, PRINTING, CHEMICALS', 'PHARMACEUTICALS/DRUG STORES',
    'PUBLIC ADMINISTRATION, GOVERNMENT', 'REAL ESTATE , INSURANCE, FINANCE',
    'SPORTING GOODS, BICYCLES, TOYS', 'TOBACCO PRODUCTS', 'TRANSPORTATION/LEASING',
    'VEHICLES, BOATS, MOTORCYCLES', 'WATER, SEWER',
];
$ilBusinessTypeOptions = array_combine($ilBusinessTypes, $ilBusinessTypes);

$ilActivityOptions = [
    'MANUFACTURING' => 'MANUFACTURING',
    'RETAIL' => 'RETAIL',
    'SERVICE' => 'SERVICE',
    'WHOLESALE' => 'WHOLESALE',
];

$ilGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'IL']];

return [
    'extends' => 'base',

    'state_steps' => [
        'il_state_ids_and_entity' => [
            'title' => 'Illinois Identifiers & Entity',
            'description' => 'IL Secretary of State and sole-proprietor identification.',
            'fields' => [
                'il_secretary_of_state_number' => [
                    'type' => 'text',
                    'label' => 'IL Secretary of State Number',
                    'rules' => ['nullable', 'regex:/^[Aa]\d{8}$/'],
                    'help' => 'Format: A12345678 (letter A followed by 8 digits).',
                    'source_name' => 'illinoisSecretaryOfStateNumber',
                ],
                'il_ssn_or_itin' => [
                    'type' => 'select',
                    'label' => 'For Sole Proprietors: SSN or ITIN?',
                    'options' => ['ssn' => 'SSN', 'itin' => 'ITIN'],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                    'drives_conditional' => true,
                    'source_name' => 'ssnOrItin',
                ],
                'il_individual_itin' => [
                    'type' => 'text',
                    'label' => 'Individual Taxpayer Identification Number (ITIN)',
                    'rules' => ['nullable', 'regex:/^9\d{2}-?\d{2}-?\d{4}$/'],
                    'when' => ['==' => [['var' => 'il_ssn_or_itin'], 'itin']],
                    'sensitive' => true,
                    'source_name' => 'individualITIN',
                ],
                'il_married_couple' => nullableYesNoField('Is this business owned by a married couple or civil union?', 'marriedCouple', [
                    'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                ]),
            ],
        ],

        'il_business_type' => [
            'title' => 'Illinois Business Classification',
            'description' => "Illinois' own business type taxonomy (separate from your NAICS code).",
            'fields' => [
                'il_primary_business_type' => [
                    'type' => 'select',
                    'label' => 'Primary Business Type',
                    'options' => $ilBusinessTypeOptions,
                    'rules' => ['required'],
                    'source_name' => 'primaryBusinessType',
                ],
                'il_primary_business_activity' => [
                    'type' => 'select',
                    'label' => 'Primary Business Activity',
                    'options' => $ilActivityOptions,
                    'rules' => ['required'],
                    'source_name' => 'primaryBusinessActivity',
                ],
                'il_secondary_business_type' => [
                    'type' => 'select',
                    'label' => 'Secondary Business Type (optional)',
                    'options' => $ilBusinessTypeOptions,
                    'rules' => ['nullable'],
                    'source_name' => 'secondaryBusinessType',
                ],
                'il_secondary_business_activity' => [
                    'type' => 'select',
                    'label' => 'Secondary Business Activity (optional)',
                    'options' => $ilActivityOptions,
                    'rules' => ['nullable'],
                    'source_name' => 'secondaryBusinessActivity',
                ],
            ],
        ],

        'il_tax_liability_and_purchases' => [
            'title' => 'Illinois Tax Liability & Purchases',
            'description' => 'Income tax liability, untaxed purchases, and withholding.',
            'fields' => [
                'il_liable_for_business_income' => yesNoField('Are you liable for IL business income or replacement tax?', 'liableForBusinessIncome'),
                'il_supplier_not_charge_tax_merchandise' => yesNoField('Do suppliers fail to charge Illinois sales tax on merchandise?', 'supplierNotChargeTaxMerchandise', ['drives_conditional' => true]),
                'il_supplier_not_charge_tax_aviation_fuel' => nullableYesNoField('Do suppliers fail to charge Illinois sales tax on aviation fuel?', 'supplierNotChargeTaxAviationFuel', [
                    'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                ]),
                'il_not_charge_tax_activities_begin_date' => [
                    'type' => 'date',
                    'label' => 'Date Untaxed Purchase Activities Began',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'il_supplier_not_charge_tax_merchandise'], '1']],
                    'source_name' => 'notChargeTaxActivitiesBeginDate',
                ],
                'il_individuals_performing_services' => yesNoField('Will you have individuals performing services for you in Illinois?', 'individualsPerformingServices'),
                'il_pay_unemployment_other_states' => nullableYesNoField("Do you pay your employees' unemployment insurance in another state?", 'payUnemploymentInOtherStates'),
            ],
        ],

        'il_product_taxes' => [
            'title' => 'Illinois Product Taxes',
            'description' => 'Illinois-specific product tax programs.',
            'groups' => [
                ['title' => 'General Merchandise & Services', 'fields' => [
                    'il_general_merchandise_activity_type', 'il_merchandise_liability_under_200',
                    'il_collect_tax_as_service', 'il_service_liability_under_200',
                ]],
                ['title' => 'Products', 'fields' => [
                    'il_chicago_soft_drink_tax', 'il_sell_tires', 'il_always_pay_tire_fee',
                    'il_rental_purchase_agreement_tax', 'il_vehicle_watercraft_aircraft_trailers',
                ]],
                ['title' => 'Vending & Leasing', 'fields' => [
                    'il_how_many_vending_machines', 'il_rent_hotel_less_than_30_days',
                    'il_lease_vehicles_more_than_one_year', 'il_leasing_company_location',
                    'il_rent_vehicles_less_than_one_year', 'il_renting_or_leasing_begin_date',
                ]],
                ['title' => 'Tobacco & Cigarettes (IL detail)', 'fields' => [
                    'il_tobacco_activity_type', 'il_register_as_tobacco_distributor', 'il_tobacco_start_date',
                    'il_cigarettes_activity_type', 'il_cigarette_machine_operator', 'il_cigarettes_start_date',
                ]],
                ['title' => 'Motor Fuel (IL detail)', 'fields' => [
                    'il_motor_fuel_retail', 'il_motor_fuel_wholesale',
                    'il_sell_motor_fuel_to_retailers', 'il_sell_fuel_in_counties',
                ]],
                ['title' => 'Cannabis (IL detail)', 'fields' => [
                    'il_medical_cannabis_dispensary_begin_date',
                ]],
            ],
            'fields' => [
                'il_general_merchandise_activity_type' => [
                    'type' => 'radio',
                    'label' => 'General merchandise sales are primarily:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => $ilGate('applies_retail_sales'),
                    'source_name' => 'generalMerchandiseActivityType',
                ],
                'il_merchandise_liability_under_200' => nullableYesNoField('Is your monthly sales tax liability under $200?', 'generalMerchandiseTaxLiabilityUnder200', [
                    'when' => $ilGate('applies_retail_sales'),
                ]),
                'il_collect_tax_as_service' => yesNoField('Do you sell items on which tax may be collected as part of your service?', 'collectTaxAsService', ['drives_conditional' => true]),
                'il_service_liability_under_200' => nullableYesNoField('Is your monthly sales tax liability under $200 for those service sales?', 'serviceTaxLiabilityUnder200', [
                    'when' => ['==' => [['var' => 'il_collect_tax_as_service'], '1']],
                ]),

                'il_chicago_soft_drink_tax' => yesNoField('Are you subject to the Chicago Soft Drink Tax?', 'chicagoSoftDrinkTax'),
                'il_sell_tires' => yesNoField('Will you sell or deliver tires?', 'sellTires', ['drives_conditional' => true]),
                'il_always_pay_tire_fee' => nullableYesNoField('Do you always pay the Tire User Fee to your supplier?', 'alwaysPayTireFeeToSupplier', [
                    'when' => ['==' => [['var' => 'il_sell_tires'], '1']],
                ]),
                'il_rental_purchase_agreement_tax' => yesNoField('Are you subject to the Rental Purchase Agreement Tax?', 'rentalPurchaseAgreementTax'),
                'il_vehicle_watercraft_aircraft_trailers' => yesNoField('Will you sell vehicles, watercraft, aircraft, or trailers?', 'vehicleWatercraftAircraftTrailers'),

                'il_how_many_vending_machines' => [
                    'type' => 'text',
                    'label' => 'How many vending machines?',
                    'rules' => ['nullable', 'integer', 'min:1'],
                    'when' => $ilGate('applies_vending'),
                    'source_name' => 'howManyVendingMachines',
                ],
                'il_rent_hotel_less_than_30_days' => nullableYesNoField('Will you rent or lease hotel rooms for less than 30 days?', 'rentHotelLessThan30Days', [
                    'when' => $ilGate('applies_lodging_or_rentals'),
                ]),
                'il_lease_vehicles_more_than_one_year' => nullableYesNoField('Will you lease vehicles for more than one year (LSE-1)?', 'leaseVehiclesMoreThanOneYear', [
                    'when' => $ilGate('applies_vehicle_rentals'),
                    'drives_conditional' => true,
                ]),
                'il_leasing_company_location' => [
                    'type' => 'radio',
                    'label' => 'Is the leasing company located in or out of state?',
                    'options' => ['in_state' => 'In state', 'out_of_state' => 'Out of state'],
                    'rules' => ['nullable', 'in:in_state,out_of_state'],
                    'when' => ['==' => [['var' => 'il_lease_vehicles_more_than_one_year'], '1']],
                    'source_name' => 'inOrOutOfStateLeasingCompany',
                ],
                'il_rent_vehicles_less_than_one_year' => nullableYesNoField('Will you rent or lease vehicles for one year or less?', 'rentVehiclesLessThanOneYear', [
                    'when' => $ilGate('applies_vehicle_rentals'),
                ]),
                'il_renting_or_leasing_begin_date' => [
                    'type' => 'date',
                    'label' => 'When will (did) renting or leasing activities begin?',
                    'rules' => ['nullable', 'date'],
                    'when' => $ilGate('applies_vehicle_rentals'),
                    'source_name' => 'rentingOrLeasingBeginDate',
                ],

                'il_tobacco_activity_type' => [
                    'type' => 'radio',
                    'label' => 'Tobacco products activity type:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => $ilGate('applies_tobacco_vape'),
                    'source_name' => 'tobaccoProductsActivityType',
                ],
                'il_register_as_tobacco_distributor' => nullableYesNoField('Will you register as a tobacco products distributor?', 'registerAsTobaccoProductsDistributor', [
                    'when' => $ilGate('applies_tobacco_vape'),
                ]),
                'il_tobacco_start_date' => [
                    'type' => 'date',
                    'label' => 'Tobacco products start date',
                    'rules' => ['nullable', 'date'],
                    'when' => $ilGate('applies_tobacco_vape'),
                    'source_name' => 'tobaccoProductsStartDate',
                ],
                'il_cigarettes_activity_type' => [
                    'type' => 'radio',
                    'label' => 'Cigarettes activity type:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => $ilGate('applies_tobacco_vape'),
                    'source_name' => 'cigarettesActivityType',
                ],
                'il_cigarette_machine_operator' => nullableYesNoField('Are you a cigarette machine operator?', 'cigaretteMachineOperator', [
                    'when' => $ilGate('applies_tobacco_vape'),
                ]),
                'il_cigarettes_start_date' => [
                    'type' => 'date',
                    'label' => 'Cigarette sales start date',
                    'rules' => ['nullable', 'date'],
                    'when' => $ilGate('applies_tobacco_vape'),
                    'source_name' => 'cigarettesProductsStartDate',
                ],

                'il_motor_fuel_retail' => nullableYesNoField('Will you sell motor fuel at retail?', 'motorFuelRetail', [
                    'when' => $ilGate('applies_fuel'),
                ]),
                'il_motor_fuel_wholesale' => nullableYesNoField('Will you sell motor fuel at wholesale?', 'motorFuelWholesale', [
                    'when' => $ilGate('applies_fuel'),
                ]),
                'il_sell_motor_fuel_to_retailers' => nullableYesNoField('Will you sell motor fuel to retailers?', 'sellMotorFuelToRetailers', [
                    'when' => $ilGate('applies_fuel'),
                ]),
                'il_sell_fuel_in_counties' => nullableYesNoField('Will you sell fuel at retail in DuPage, Kane, or McHenry county?', 'sellFuelInCounties', [
                    'when' => $ilGate('applies_fuel'),
                ]),

                'il_medical_cannabis_dispensary_begin_date' => [
                    'type' => 'date',
                    'label' => 'When will (did) medical cannabis activities begin?',
                    'rules' => ['nullable', 'date'],
                    'when' => $ilGate('applies_cannabis'),
                    'source_name' => 'medicalCannabisDispensaryBeginDate',
                ],
            ],
        ],

        'il_nexus' => [
            'title' => 'Illinois Nexus',
            'description' => 'Remote sales and economic nexus thresholds.',
            'fields' => [
                'il_sales_from_out_of_state' => yesNoField('Do sales to Illinois customers originate from out of state?', 'salesFromOutOfState'),
                'il_over_100k' => yesNoField('Do you make $100,000 or more in annual sales to Illinois customers?', 'over100000', [
                    'help' => 'Compare against the annual sales estimate you entered for Illinois earlier.',
                ]),
                'il_separate_transactions_over_200' => yesNoField('Do you make 200 or more separate transactions annually to Illinois customers?', 'seperateTransactionsOver200'),
            ],
        ],

        'il_utilities_and_other_taxes' => [
            'title' => 'Illinois Utilities & Other Tax Types',
            'description' => 'Utility provider detail and remaining IL tax programs.',
            'groups' => [
                ['title' => 'Utility Provider (IL detail)', 'fields' => [
                    'il_utility_electricity', 'il_electricity_activity_type',
                    'il_utility_natural_gas', 'il_natural_gas_activity_type',
                    'il_utility_telecommunications', 'il_telecommunications_activity_type',
                    'il_paging_or_wireless_exclusively', 'il_utility_water',
                    'il_utility_cooperative', 'il_utility_begin_date',
                ]],
                ['title' => 'Liquor at Retail', 'fields' => [
                    'il_liquor_at_retail',
                    'il_liquor_place_eating', 'il_liquor_place_drinking', 'il_liquor_place_liquor_store',
                ]],
                ['title' => 'All Other Tax Types', 'fields' => [
                    'il_dry_cleaning_operator', 'il_liquor_warehousing', 'il_liquor_warehouse_for_other',
                    'il_municipality', 'il_facility', 'il_solvent_supplier',
                    'il_coin_amusement_devices', 'il_purchase_electricity_pay_idor',
                    'il_purchase_natural_gas_pay_idor', 'il_other_tax_type_begin_date',
                ]],
            ],
            'fields' => [
                'il_utility_electricity' => nullableYesNoField('Do you provide electricity?', 'utilityProviderElectricity', [
                    'when' => $ilGate('applies_utilities'),
                    'drives_conditional' => true,
                ]),
                'il_electricity_activity_type' => [
                    'type' => 'radio',
                    'label' => 'Electricity activity type:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => ['==' => [['var' => 'il_utility_electricity'], '1']],
                    'source_name' => 'electricityActivityType',
                ],
                'il_utility_natural_gas' => nullableYesNoField('Do you provide natural gas?', 'utilityProviderNaturalGas', [
                    'when' => $ilGate('applies_utilities'),
                    'drives_conditional' => true,
                ]),
                'il_natural_gas_activity_type' => [
                    'type' => 'radio',
                    'label' => 'Natural gas activity type:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => ['==' => [['var' => 'il_utility_natural_gas'], '1']],
                    'source_name' => 'naturalGasActivityType',
                ],
                'il_utility_telecommunications' => nullableYesNoField('Do you provide telecommunications?', 'utilityProviderTelecommunications', [
                    'when' => $ilGate('applies_utilities'),
                    'drives_conditional' => true,
                ]),
                'il_telecommunications_activity_type' => [
                    'type' => 'radio',
                    'label' => 'Telecommunications activity type:',
                    'options' => ['retail' => 'Retail', 'wholesale' => 'Wholesale'],
                    'rules' => ['nullable', 'in:retail,wholesale'],
                    'when' => ['==' => [['var' => 'il_utility_telecommunications'], '1']],
                    'source_name' => 'telecommunicationsActivityType',
                ],
                'il_paging_or_wireless_exclusively' => nullableYesNoField('Do you provide paging or wireless service exclusively?', 'pagingOrWirelessExclusively', [
                    'when' => ['==' => [['var' => 'il_utility_telecommunications'], '1']],
                ]),
                'il_utility_water' => nullableYesNoField('Do you provide water or sewer services?', 'utilityProviderWater', [
                    'when' => $ilGate('applies_utilities'),
                ]),
                'il_utility_cooperative' => nullableYesNoField('Are you a utility cooperative?', 'utilityCooperative', [
                    'when' => $ilGate('applies_utilities'),
                ]),
                'il_utility_begin_date' => [
                    'type' => 'date',
                    'label' => 'When will (did) utility activities begin?',
                    'rules' => ['nullable', 'date'],
                    'when' => $ilGate('applies_utilities'),
                    'source_name' => 'utilityProviderBeginDate',
                ],

                'il_liquor_at_retail' => nullableYesNoField('Will you sell liquor at retail?', 'liquorAtRetail', [
                    'when' => $ilGate('applies_alcohol'),
                    'drives_conditional' => true,
                ]),
                // §3A.2.4: legacy option list restored (Eating Place /
                // Drinking Place / Liquor Store).
                'il_liquor_place_eating' => [
                    'type' => 'checkbox',
                    'label' => 'Eating Place',
                    'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']],
                    'source_name' => 'liquorAtRetailPlace[]',
                    'source_value' => 'Eating Place',
                ],
                'il_liquor_place_drinking' => [
                    'type' => 'checkbox',
                    'label' => 'Drinking Place',
                    'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']],
                    'source_name' => 'liquorAtRetailPlace[]',
                    'source_value' => 'Drinking Place',
                ],
                'il_liquor_place_liquor_store' => [
                    'type' => 'checkbox',
                    'label' => 'Liquor Store',
                    'when' => ['==' => [['var' => 'il_liquor_at_retail'], '1']],
                    'source_name' => 'liquorAtRetailPlace[]',
                    'source_value' => 'Liquor Store',
                ],

                'il_dry_cleaning_operator' => yesNoField('Are you a dry-cleaning operator?', 'dryCleaningOperator'),
                'il_liquor_warehousing' => yesNoField('Do you warehouse liquor?', 'liquorWarehousing', ['drives_conditional' => true]),
                'il_liquor_warehouse_for_other' => nullableYesNoField('Do you warehouse liquor for a company other than your own?', 'liquorWarehouseForOtherCompany', [
                    'when' => ['==' => [['var' => 'il_liquor_warehousing'], '1']],
                ]),
                'il_municipality' => yesNoField('Are you a municipality?', 'municipality'),
                'il_facility' => yesNoField('Are you a facility?', 'facility'),
                'il_solvent_supplier' => yesNoField('Are you a solvent supplier?', 'solventSupplier'),
                'il_coin_amusement_devices' => yesNoField('Do you own or operate coin-operated amusement devices?', 'ownOrOperateCoinAmusementDevices'),
                'il_purchase_electricity_pay_idor' => yesNoField('Do you purchase electricity for non-residential use and wish to pay tax directly to IDOR?', 'purchaseElectricityPayTaxToIdor'),
                'il_purchase_natural_gas_pay_idor' => yesNoField('Do you purchase natural gas from outside IL and wish/need to pay tax directly to IDOR?', 'purchaseNaturalGasPayTaxToIdor'),
                'il_other_tax_type_begin_date' => [
                    'type' => 'date',
                    'label' => 'When will (did) these other tax activities begin?',
                    'rules' => ['nullable', 'date'],
                    'source_name' => 'otherTaxTypeBeginDate',
                ],
            ],
        ],
    ],
];

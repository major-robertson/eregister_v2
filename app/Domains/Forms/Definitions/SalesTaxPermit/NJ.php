<?php

/**
 * New Jersey — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/newJersey/application/` (organizationInformation,
 * primary, businessInformation, taxableActivities, employmentActivity).
 *
 * Collapsed into core: year-round/seasonal (applies_seasonal), employee
 * count (matrix), fiscal year month (core), labor gate (applies_employees
 * _or_payroll), PEO (applies_payroll_service_or_peo), cigarettes / fuel /
 * hazmat / rent-a-car / telecom / hotel gates (applies_*), business fax
 * + contact block (core contacts), counties (locations[]).
 *
 * Deliberately OMITTED (§3A.4): Millville Sports & Entertainment District
 * questions (hyper-local).
 */
$njGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'NJ']];

$njMonthFields = [];
foreach ([
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
    7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
] as $value => $label) {
    $njMonthFields['nj_month_'.strtolower(substr($label, 0, 3))] = [
        'type' => 'checkbox',
        'label' => $label,
        'when' => ['contains' => [['var' => '$root.applies_seasonal.states'], 'NJ']],
        'source_name' => 'MonthsOfBusiness[]',
        'source_value' => $label,
    ];
}

$njOtherTaxes = [
    'nj_other_tax_none' => ['No other taxes', 'NoTax'],
    'nj_other_tax_alcohol' => ['Alcoholic Beverage Tax', 'Alcohol'],
    'nj_other_tax_atlantic_city' => ['Atlantic City Taxes/Fees', 'AtlanticCity'],
    'nj_other_tax_cape_may' => ['Cape May County Tourism Tax', 'CapeMay'],
    'nj_other_tax_corporate' => ['Corporation Business Tax', 'Corporate'],
    'nj_other_tax_insurance' => ['Insurance Premiums Tax', 'Insurance'],
    'nj_other_tax_landfill' => ['Landfill Closure Tax', 'Landfill'],
    'nj_other_tax_utility' => ['Public Utility Taxes', 'Utility'],
    'nj_other_tax_sanitary' => ['Sanitary Landfill Taxes', 'Sanitary'],
    'nj_other_tax_solid_waste' => ['Solid Waste Services Tax', 'SolidWaste'],
    'nj_other_tax_inheritance' => ['Inheritance/Estate Tax', 'Inheritance'],
    'nj_other_tax_unemployment' => ['Unemployment/Disability Insurance', 'Unemployment'],
    'nj_other_tax_salem' => ['Salem County Sales Tax', 'Salem'],
];

$njOtherTaxFields = [];
foreach ($njOtherTaxes as $key => $def) {
    $njOtherTaxFields[$key] = [
        'type' => 'checkbox',
        'label' => $def[0],
        'source_name' => 'OtherTaxes[]',
        'source_value' => $def[1],
    ];
}

return [
    'extends' => 'base',

    'state_steps' => [
        'nj_classification' => [
            'title' => 'New Jersey Classification',
            'description' => 'NJ business codes, parent corporation, and schedule.',
            'groups' => [
                ['title' => 'NJ Business Codes', 'fields' => [
                    'nj_business_code', 'nj_standard_industrial_code', 'nj_attention_to',
                ]],
                ['title' => 'Remote Seller Questions', 'fields' => [
                    'nj_has_employees', 'nj_seller_has_employees', 'nj_public_agency_seller',
                ]],
                ['title' => 'Parent Corporation & Partners', 'fields' => [
                    'nj_resident_out_of_state_partner', 'nj_subsidiary_of_corporation',
                    'nj_parent_corporation_name', 'nj_parent_corporation_fein',
                ]],
                ['title' => 'Months of Business', 'fields' => array_keys($njMonthFields)],
            ],
            'fields' => array_merge(
                [
                    'nj_business_code' => [
                        'type' => 'text',
                        'label' => 'New Jersey Business Code',
                        'rules' => ['nullable', 'string', 'max:10'],
                        'source_name' => 'njBusinessCode',
                    ],
                    'nj_standard_industrial_code' => [
                        'type' => 'text',
                        'label' => 'Standard Industrial Code (SIC)',
                        'rules' => ['nullable', 'digits:4'],
                        'source_name' => 'standardIndustrialCode',
                    ],
                    'nj_attention_to' => [
                        'type' => 'text',
                        'label' => 'Attention To (mailing)',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'attentionTo',
                    ],
                    'nj_has_employees' => yesNoField('Will you have employees?', 'HasEmployees'),
                    'nj_seller_has_employees' => nullableYesNoField('Does your business have employees in New Jersey?', 'SellerHasEmployees', [
                        'when' => $njGate('applies_remote_seller'),
                    ]),
                    'nj_public_agency_seller' => nullableYesNoField('Do you intend to contract with and sell goods to public agencies?', 'PubAgencySeller', [
                        'when' => $njGate('applies_remote_seller'),
                    ]),
                    'nj_resident_out_of_state_partner' => nullableYesNoField('Are you registering because you have NJ residents as partners but operate out of state?', 'NJresidentOutOfStatePartner', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['general_partnership', 'limited_partnership', 'llp']]],
                    ]),
                    'nj_subsidiary_of_corporation' => yesNoField('Is this business a subsidiary of another corporation?', 'subsidiaryOfCorporation', ['drives_conditional' => true]),
                    'nj_parent_corporation_name' => [
                        'type' => 'text',
                        'label' => 'Parent Corporation Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'nj_subsidiary_of_corporation'], '1']],
                        'source_name' => 'OwnershipTypeNameofParentCorp',
                    ],
                    'nj_parent_corporation_fein' => [
                        'type' => 'text',
                        'label' => 'Parent Corporation FEIN',
                        'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                        'placeholder' => '12-3456789',
                        'mask' => '99-9999999',
                        'when' => ['==' => [['var' => 'nj_subsidiary_of_corporation'], '1']],
                        'sensitive' => true,
                        'source_name' => 'OwnershipTypeFIENofParentCorp',
                    ],
                ],
                $njMonthFields,
            ),
        ],

        'nj_employment' => [
            'title' => 'New Jersey Employment',
            'description' => 'Shown because employees/payroll applies to New Jersey.',
            'groups' => [
                ['title' => 'Payroll & Withholding', 'fields' => [
                    'nj_date_pay_exceeds_1k', 'nj_pay_nj_residents_outside',
                    'nj_pay_pension_or_annuity', 'nj_more_than_one_employing_facility',
                    'nj_is_agricultural', 'nj_is_household', 'nj_date_cash_pay_exceeds_1k',
                ]],
                ['title' => 'Federal Unemployment & Exemption', 'fields' => [
                    'nj_federal_unemployment_tax', 'nj_futa_year',
                    'nj_unemployment_exempt', 'nj_exempt_reason', 'nj_pay_unemployment_anyway',
                ]],
                ['title' => 'Acquired Employee Units', 'fields' => [
                    'nj_acquired_employee_units', 'nj_acquired_status', 'nj_acquired_ein',
                    'nj_acquired_name', 'nj_acquired_date', 'nj_acquired_address',
                    'nj_units_owned_by_same_interest', 'nj_protest_transfer_experience',
                    'nj_acquired_assets', 'nj_acquired_assets_percent',
                    'nj_acquired_trade', 'nj_acquired_trade_percent',
                    'nj_acquired_employees', 'nj_acquired_employees_percent',
                ]],
            ],
            'fields' => [
                'nj_date_pay_exceeds_1k' => [
                    'type' => 'date',
                    'label' => 'Date cumulative gross payroll exceeds $1,000',
                    'rules' => ['nullable', 'date'],
                    'when' => $njGate('applies_employees_or_payroll'),
                    'source_name' => 'DatePayExceeds1K',
                ],
                'nj_pay_nj_residents_outside' => nullableYesNoField('Will you pay wages to NJ residents working outside New Jersey?', 'PayNJresidentsOutsideState', [
                    'when' => $njGate('applies_employees_or_payroll'),
                ]),
                'nj_pay_pension_or_annuity' => nullableYesNoField('Will you be a payer of pension or annuity income?', 'PayPensionOrAnnuity', [
                    'when' => $njGate('applies_employees_or_payroll'),
                ]),
                'nj_more_than_one_employing_facility' => nullableYesNoField('Do you have more than one employing facility in New Jersey?', 'MoreThanOneEmployingFacility', [
                    'when' => $njGate('applies_employees_or_payroll'),
                ]),
                'nj_is_agricultural' => nullableYesNoField('Is your employment agricultural?', 'IsAgricultural', [
                    'when' => $njGate('applies_employees_or_payroll'),
                ]),
                'nj_is_household' => nullableYesNoField('Is your employment household?', 'IsHouseHold', [
                    'when' => $njGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'nj_date_cash_pay_exceeds_1k' => [
                    'type' => 'date',
                    'label' => 'Date gross cash wages totaled $1,000 or more (household)',
                    'rules' => ['nullable', 'date'],
                    'when' => ['==' => [['var' => 'nj_is_household'], '1']],
                    'source_name' => 'DateCashPayExceeds1K',
                ],
                'nj_federal_unemployment_tax' => nullableYesNoField('Were you subject to the Federal Unemployment Tax Act (FUTA)?', 'FederalUnemploymentTax', [
                    'when' => $njGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'nj_futa_year' => [
                    'type' => 'text',
                    'label' => 'Year you were subject to FUTA',
                    'rules' => ['nullable', 'digits:4'],
                    'when' => ['==' => [['var' => 'nj_federal_unemployment_tax'], '1']],
                    'source_name' => 'FutaYear',
                ],
                'nj_unemployment_exempt' => nullableYesNoField('Does this employing unit claim exemption from unemployment liability?', 'UnemploymentExempt', [
                    'when' => $njGate('applies_employees_or_payroll'),
                    'drives_conditional' => true,
                ]),
                'nj_exempt_reason' => [
                    'type' => 'text',
                    'label' => 'Explain why this employing unit claims exemption',
                    'rules' => ['nullable', 'string', 'max:500'],
                    'when' => ['==' => [['var' => 'nj_unemployment_exempt'], '1']],
                    'source_name' => 'ExemptReason',
                ],
                'nj_pay_unemployment_anyway' => nullableYesNoField('Does this employing unit wish to voluntarily elect unemployment coverage?', 'PayUnemployementAnyway', [
                    'when' => ['==' => [['var' => 'nj_unemployment_exempt'], '1']],
                ]),

                'nj_acquired_employee_units' => yesNoField('Did you acquire substantially all the assets of another employer?', 'AquiredEmployeeUnits', ['drives_conditional' => true]),
                'nj_acquired_status' => [
                    'type' => 'radio',
                    'label' => 'The status of the acquisition',
                    'options' => ['1' => 'In Whole', '0' => 'In Part'],
                    'rules' => ['nullable', 'in:0,1'],
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'source_name' => 'AquiredAll',
                ],
                'nj_acquired_ein' => [
                    'type' => 'text',
                    'label' => 'EIN of the Acquired Unit',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'sensitive' => true,
                    'source_name' => 'AquiredEin',
                ],
                'nj_acquired_name' => [
                    'type' => 'text',
                    'label' => 'Name of the Acquired Unit',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'source_name' => 'AquiredName',
                ],
                'nj_acquired_date' => [
                    'type' => 'date',
                    'label' => 'Date Acquired',
                    'rules' => ['nullable', 'date', 'before_or_equal:today'],
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'source_name' => 'AquiredDate',
                ],
                'nj_acquired_address' => [
                    'type' => 'address',
                    'label' => 'Acquired Unit Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                ],
                'nj_units_owned_by_same_interest' => nullableYesNoField('Are the predecessor and successor units owned or controlled by the same interest?', 'UnitsOwnedBySameInterest', [
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                ]),
                'nj_protest_transfer_experience' => nullableYesNoField('Do you protest the transfer of employment experience?', 'ProtestTransferOfEmploymentExperience', [
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                ]),
                'nj_acquired_assets' => nullableYesNoField('Were any assets acquired?', 'AquiredAssets', [
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'drives_conditional' => true,
                ]),
                'nj_acquired_assets_percent' => [
                    'type' => 'percent',
                    'label' => 'Percentage of Assets Acquired',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['==' => [['var' => 'nj_acquired_assets'], '1']],
                    'source_name' => 'AquiredAssetsPercent',
                ],
                'nj_acquired_trade' => nullableYesNoField('Was any trade or business acquired?', 'TradeOrCustomers', [
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'drives_conditional' => true,
                ]),
                'nj_acquired_trade_percent' => [
                    'type' => 'percent',
                    'label' => 'Percentage of Trade or Business Acquired',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['==' => [['var' => 'nj_acquired_trade'], '1']],
                    'source_name' => 'AcquiredTradePercentage',
                ],
                'nj_acquired_employees' => nullableYesNoField('Were any employees acquired?', 'AquiredEmployees', [
                    'when' => ['==' => [['var' => 'nj_acquired_employee_units'], '1']],
                    'drives_conditional' => true,
                ]),
                'nj_acquired_employees_percent' => [
                    'type' => 'percent',
                    'label' => 'Percentage of Employees Acquired',
                    'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                    'when' => ['==' => [['var' => 'nj_acquired_employees'], '1']],
                    'source_name' => 'AquiredEmployeesPercent',
                ],
            ],
        ],

        'nj_taxable_activities' => [
            'title' => 'New Jersey Taxable Activities',
            'description' => 'NJ-REG taxable activity questions.',
            'groups' => [
                ['title' => 'Sales & Use', 'fields' => [
                    'nj_collect_or_pay_tax', 'nj_date_first_sale', 'nj_exempt_purchases',
                ]],
                ['title' => 'Cigarettes & Tobacco (NJ detail)', 'fields' => [
                    'nj_sell_cigarettes', 'nj_distribute_cigarettes', 'nj_buy_foreign_tobacco',
                ]],
                ['title' => 'Fuel (NJ detail)', 'fields' => [
                    'nj_refine_import_distribute_fuel', 'nj_direct_payment_permit_fuel',
                ]],
                ['title' => 'Hazardous Materials (NJ detail)', 'fields' => [
                    'nj_large_fuel_supply', 'nj_large_hazmat_storage',
                    'nj_hazmat_public_storage', 'nj_storage_terminal_name',
                ]],
                ['title' => 'Environmental & Other Activities', 'fields' => [
                    'nj_litter_products', 'nj_handle_solid_waste', 'nj_export_solid_waste',
                    'nj_landfill', 'nj_dep_id', 'nj_sell_gas_or_electricity',
                    'nj_sell_or_service_government', 'nj_first_rental_date',
                    'nj_sell_tires_or_cars', 'nj_gambling',
                ]],
                ['title' => 'Other New Jersey Taxes', 'fields' => array_keys($njOtherTaxFields)],
            ],
            'fields' => array_merge(
                [
                    'nj_collect_or_pay_tax' => yesNoField('Will you collect New Jersey Sales Tax and/or pay Use Tax?', 'CollectOrPayTax', ['drives_conditional' => true]),
                    'nj_date_first_sale' => [
                        'type' => 'date',
                        'label' => 'Exact date you expect to make the first sale',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'nj_collect_or_pay_tax'], '1']],
                        'source_name' => 'DateFirstSale',
                    ],
                    'nj_exempt_purchases' => yesNoField('Will you need to make exempt purchases?', 'ExemptPurchases'),

                    'nj_sell_cigarettes' => nullableYesNoField('Do you intend to sell cigarettes?', 'SellCigarettes', [
                        'when' => $njGate('applies_tobacco_vape'),
                    ]),
                    'nj_distribute_cigarettes' => nullableYesNoField('Will you be a distributor or wholesaler of tobacco or nicotine products?', 'DistributeCigarettes', [
                        'when' => $njGate('applies_tobacco_vape'),
                    ]),
                    'nj_buy_foreign_tobacco' => nullableYesNoField('Do you intend to purchase tobacco products from outside New Jersey?', 'BuyForeignTobacco', [
                        'when' => $njGate('applies_tobacco_vape'),
                    ]),

                    'nj_refine_import_distribute_fuel' => nullableYesNoField('Will your company be engaged in refining and/or distributing motor fuels?', 'RefineImportDistributeFuel', [
                        'when' => $njGate('applies_fuel'),
                    ]),
                    'nj_direct_payment_permit_fuel' => nullableYesNoField('Will your activity require a Direct Payment Permit for fuel?', 'DirectPaymentPermitForFuel', [
                        'when' => $njGate('applies_fuel'),
                    ]),

                    'nj_large_fuel_supply' => nullableYesNoField('Do you operate a facility storing 200,000 gallons or more of fuel?', 'LargeFuelSupply', [
                        'when' => $njGate('applies_hazardous_materials'),
                    ]),
                    'nj_large_hazmat_storage' => nullableYesNoField('Do you operate a facility storing 20,000 gallons or more of hazardous chemicals?', 'LargeHazmatStorage', [
                        'when' => $njGate('applies_hazardous_materials'),
                    ]),
                    'nj_hazmat_public_storage' => nullableYesNoField('Do you store petroleum products or hazardous chemicals at a public storage terminal?', 'HazmatPublicStorage', [
                        'when' => $njGate('applies_hazardous_materials'),
                        'drives_conditional' => true,
                    ]),
                    'nj_storage_terminal_name' => [
                        'type' => 'text',
                        'label' => 'Name of the terminal',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'nj_hazmat_public_storage'], '1']],
                        'source_name' => 'StorageTerminalName',
                    ],

                    'nj_litter_products' => yesNoField('Are you a manufacturer, wholesaler, distributor, or retailer of litter-generating products?', 'MakeSellDistributeLitter'),
                    'nj_handle_solid_waste' => yesNoField('Do you operate a solid waste facility?', 'HandleSolidWaste'),
                    'nj_export_solid_waste' => yesNoField('Will your business collect and transport solid waste out of state?', 'ExportSolidWaste'),
                    'nj_landfill' => yesNoField('Are you an owner or operator of a sanitary landfill facility?', 'Landfill', ['drives_conditional' => true]),
                    'nj_dep_id' => [
                        'type' => 'text',
                        'label' => 'D.E.P. Facility Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'nj_landfill'], '1']],
                        'source_name' => 'DepId',
                    ],
                    'nj_sell_gas_or_electricity' => yesNoField('Do you sell, store, deliver, or transport natural gas or electricity?', 'SellGasOrElectricity'),
                    'nj_sell_or_service_government' => yesNoField('Will you provide goods or services as a direct contractor to government?', 'SellOrServiceGov'),
                    'nj_first_rental_date' => [
                        'type' => 'date',
                        'label' => 'Begin date for vehicle rentals',
                        'rules' => ['nullable', 'date'],
                        'when' => $njGate('applies_vehicle_rentals'),
                        'source_name' => 'FirstRentalDate',
                    ],
                    'nj_sell_tires_or_cars' => yesNoField('Do you make retail sales of new motor vehicle tires, or sell or lease motor vehicles?', 'SellTiresOrCars'),
                    'nj_gambling' => yesNoField('Will you be holding legalized games of chance where proceeds exceed $1,000?', 'Gamble'),
                ],
                $njOtherTaxFields,
            ),
        ],
    ],
];

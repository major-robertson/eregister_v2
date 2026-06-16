<?php

/**
 * Florida — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/florida/application/` — the DEDUPED UNION of
 * generalQuestions.blade.php (full DR-1 style) and generalQuestions2.blade.php
 * (shorter parallel page), plus organizationInformation / entityQuestions.
 *
 * Collapsed into core: retail sales / temporary events / equipment rental /
 * admissions / lodging / contractor / internet-catalog / seasonal /
 * employees gates (applies_*), FL employee count + wages dates (matrix),
 * fiscal year end + prior business name + predecessor identity (core).
 */
$flGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'FL']];

$flYesNo = function (array $defs, ?array $when = null): array {
    return collect($defs)->map(function ($def) use ($when) {
        $extra = $when ? ['when' => $when] : [];

        return $when
            ? nullableYesNoField($def[0], $def[1], $extra)
            : yesNoField($def[0], $def[1]);
    })->all();
};

return [
    'extends' => 'base',

    'state_steps' => [
        'fl_business_profile' => [
            'title' => 'Florida Business Profile',
            'description' => 'Florida-specific business details.',
            'fields' => [
                'fl_principal_products_or_services' => [
                    'type' => 'text',
                    'label' => 'Principal Products or Services (FL detail)',
                    'rules' => ['required', 'string', 'max:500'],
                    'help' => 'Florida requires this even though we collected a general business description.',
                    'source_name' => 'principalProductsOrServices',
                ],
                'fl_services' => [
                    'type' => 'select',
                    'label' => 'Type of Services Provided',
                    'options' => [
                        'none' => 'None',
                        'administrative' => 'Administrative',
                        'research' => 'Research',
                        'other' => 'Other',
                    ],
                    'rules' => ['required'],
                    'source_name' => 'services',
                ],
            ],
        ],

        'fl_seasonal_and_prior_certificate' => [
            'title' => 'Florida Seasonal & Prior Registration',
            'description' => 'Open season, prior FL certificates, and tax warrants.',
            'groups' => [
                ['title' => 'Seasonal Operation', 'fields' => [
                    ['fl_first_month_of_open_season', 'fl_last_month_of_open_season'],
                ]],
                ['title' => 'Prior FL Certificate (Business)', 'fields' => [
                    'fl_business_ever_issued_certificate', 'fl_entity_prior_legal_name',
                    'fl_entity_prior_certificate_number', 'fl_entity_prior_address',
                ]],
                ['title' => 'Prior FL Certificate (Owner)', 'fields' => [
                    'fl_owner_ever_issued_certificate',
                    ['fl_owner_prior_first_name', 'fl_owner_prior_last_name'],
                    'fl_owner_prior_certificate_number', 'fl_owner_prior_address',
                ]],
                ['title' => 'Tax Warrants', 'fields' => [
                    'fl_business_tax_warrant', 'fl_owner_tax_warrant',
                ]],
            ],
            'fields' => [
                'fl_first_month_of_open_season' => [
                    'type' => 'select',
                    'label' => 'First Month of Open Season',
                    'options' => [
                        '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                        '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                        '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                    ],
                    'rules' => ['nullable'],
                    'when' => $flGate('applies_seasonal'),
                    'source_name' => 'firstMonthOfOpenSeason',
                ],
                'fl_last_month_of_open_season' => [
                    'type' => 'select',
                    'label' => 'Last Month of Open Season',
                    'options' => [
                        '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                        '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                        '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                    ],
                    'rules' => ['nullable'],
                    'when' => $flGate('applies_seasonal'),
                    'source_name' => 'lastMonthOfOpenSeason',
                ],

                'fl_business_ever_issued_certificate' => yesNoField('Has this business entity ever been issued a certificate by the FL Department of Revenue?', 'businessEverIssuedCertificate', ['drives_conditional' => true]),
                'fl_entity_prior_legal_name' => [
                    'type' => 'text',
                    'label' => 'Prior Legal Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                    'source_name' => 'entityPriorLegalName',
                ],
                'fl_entity_prior_certificate_number' => [
                    'type' => 'text',
                    'label' => 'Prior FL Sales Tax Certificate Number',
                    'rules' => ['nullable', 'digits:13'],
                    'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                    'source_name' => 'entityPriorCertificateNumber',
                ],
                'fl_entity_prior_address' => [
                    'type' => 'address',
                    'label' => 'Prior Business Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'fl_business_ever_issued_certificate'], '1']],
                ],

                'fl_owner_ever_issued_certificate' => yesNoField('Has any proprietor, owner, or partner ever been issued a FL DOR certificate for another entity?', 'ownerEverIssuedCertificate', ['drives_conditional' => true]),
                'fl_owner_prior_first_name' => [
                    'type' => 'text',
                    'label' => 'Owner First Name',
                    'rules' => ['nullable', 'string', 'max:60'],
                    'when' => ['==' => [['var' => 'fl_owner_ever_issued_certificate'], '1']],
                    'source_name' => 'ownerPriorFirstName',
                ],
                'fl_owner_prior_last_name' => [
                    'type' => 'text',
                    'label' => 'Owner Last Name',
                    'rules' => ['nullable', 'string', 'max:60'],
                    'when' => ['==' => [['var' => 'fl_owner_ever_issued_certificate'], '1']],
                    'source_name' => 'ownerPriorLastName',
                ],
                'fl_owner_prior_certificate_number' => [
                    'type' => 'text',
                    'label' => "Owner's Prior Certificate Number",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'fl_owner_ever_issued_certificate'], '1']],
                    'source_name' => 'ownerPriorCertificateNumber',
                ],
                'fl_owner_prior_address' => [
                    'type' => 'address',
                    'label' => "Owner's Prior Business Address",
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'fl_owner_ever_issued_certificate'], '1']],
                ],

                'fl_business_tax_warrant' => yesNoField('To your knowledge, has a tax warrant ever been filed against this business entity?', 'businessTaxWarrent'),
                'fl_owner_tax_warrant' => yesNoField('To your knowledge, has a tax warrant ever been filed against any proprietor, owner, or partner?', 'ownerTaxWarrent'),
            ],
        ],

        'fl_sales_and_services' => [
            'title' => 'Florida Sales & Services',
            'description' => 'Florida DR-1 activity questions.',
            'groups' => [
                ['title' => 'Sales Activities', 'fields' => [
                    'fl_sell_wholesale_products', 'fl_sell_serve_or_prepare_food',
                    'fl_repair_equipment', 'fl_construct_offsite', 'fl_purchase_outside_florida',
                    'fl_secondhand_goods', 'fl_salvage_or_scrap_metal',
                ]],
                ['title' => 'Service Activities', 'fields' => [
                    'fl_pest_control_nonresidential', 'fl_interior_cleaning_nonresidential',
                    'fl_detective_services', 'fl_protection_services', 'fl_alarm_monitoring',
                ]],
                ['title' => 'Purchases & Permits', 'fields' => [
                    'fl_purchase_without_fl_sales_tax', 'fl_direct_pay_permit',
                    'fl_applying_to_remit_sales_tax', 'fl_purchase_dyed_fuel',
                ]],
                ['title' => 'Products', 'fields' => [
                    'fl_sell_prepaid_phones', 'fl_long_distance_calling', 'fl_prepaid_wireless',
                    'fl_new_tires', 'fl_lead_acid_batteries', 'fl_car_sharing_membership',
                    'fl_dry_cleaning_plant', 'fl_produce_perchloroethylene',
                ]],
                ['title' => 'Property Rentals & Storage', 'fields' => [
                    'fl_rent_commercial_property', 'fl_manage_commercial_property',
                    'fl_rent_parking_or_storage_vehicles', 'fl_rent_docking_or_storage_boats',
                    'fl_tie_down_storage_aircraft', 'fl_another_party_manage_property',
                ]],
                ['title' => 'Fuel Sales', 'fields' => [
                    'fl_sell_tax_paid_fuel', 'fl_gas_station_only', 'fl_gas_station_convenience_store',
                    'fl_truck_stop', 'fl_marine_fueling', 'fl_aircraft_fueling', 'fl_bulk_fuel_reseller',
                ]],
            ],
            'fields' => array_merge(
                $flYesNo([
                    'fl_sell_wholesale_products' => ['Will you sell products at wholesale?', 'sellWholesaleProducts'],
                    'fl_sell_serve_or_prepare_food' => ['Will you sell, serve, or prepare food products or drinks?', 'sellServeOrPrepareFood'],
                    'fl_repair_equipment' => ['Will you repair or alter consumer products or equipment?', 'repairEquipment'],
                    'fl_construct_offsite' => ['Will you construct, assemble, or fabricate building components at an offsite location?', 'constructOffsite'],
                    'fl_purchase_outside_florida' => ['Will you purchase products or supplies from vendors outside Florida?', 'purchaseOutsideFlorida'],
                    'fl_secondhand_goods' => ['Will you purchase, consign, trade, or sell secondhand goods?', 'secondHandGoods'],
                    'fl_salvage_or_scrap_metal' => ['Will you purchase, gather, obtain, or sell salvage or scrap metal?', 'dealSalvageOrScrapMetal'],
                    'fl_pest_control_nonresidential' => ['Will you provide pest control services for nonresidential buildings?', 'pestControlNonresidential'],
                    'fl_interior_cleaning_nonresidential' => ['Will you provide interior cleaning services for nonresidential buildings?', 'interiorCleaningNonresidential'],
                    'fl_detective_services' => ['Will you provide detective services?', 'detectiveServices'],
                    'fl_protection_services' => ['Will you provide protection services?', 'protectionServices'],
                    'fl_alarm_monitoring' => ['Will you provide security alarm system monitoring services?', 'alarmMonitoringServices'],
                    'fl_purchase_without_fl_sales_tax' => ['Will you purchase items to use in your business without paying FL sales tax?', 'purchaseWithoutFlSalesTax'],
                    'fl_direct_pay_permit' => ['Are you applying for a direct pay permit?', 'directPayPermit'],
                    'fl_applying_to_remit_sales_tax' => ['Are you applying to remit sales tax on purchases made without paying FL sales tax?', 'applyingToRemitSalesTax'],
                    'fl_purchase_dyed_fuel' => ['Will you purchase dyed diesel fuel for off-road purposes?', 'purchaseDyedFuel'],
                    'fl_sell_prepaid_phones' => ['Will you sell prepaid phones, phone cards, or calling arrangements?', 'sellPrepaidPhones'],
                    'fl_long_distance_calling' => ['Will you sell domestic or international long-distance calling?', 'longDistanceCalling'],
                    'fl_prepaid_wireless' => ['Will you sell prepaid wireless services?', 'prepairdWirelessService'],
                    'fl_new_tires' => ['Will you sell (at retail) new tires for motorized vehicles?', 'newTiresForMotorizedVehicles'],
                    'fl_lead_acid_batteries' => ['Will you sell (at retail) new or remanufactured lead-acid batteries?', 'leadAcidBatteries'],
                    'fl_car_sharing_membership' => ['Will you rent, lease, or sell car-sharing membership services?', 'sellCarSellingMembership'],
                    'fl_dry_cleaning_plant' => ['Do you own or operate a dry-cleaning plant or dry drop-off facility?', 'dryCleaningPlant'],
                    'fl_produce_perchloroethylene' => ['Will you produce or import perchloroethylene?', 'producePerchloroethylene'],
                    'fl_rent_commercial_property' => ['Will you rent or lease commercial real property to others?', 'rentOrLeaseProperty'],
                    'fl_manage_commercial_property' => ['Will you manage commercial real property for others?', 'manageCommercialRealProperty'],
                    'fl_rent_parking_or_storage_vehicles' => ['Will you rent or lease parking or storage spaces for motor vehicles?', 'rentOrLeaseParkingOrStorage'],
                    'fl_rent_docking_or_storage_boats' => ['Will you rent or lease docking or storage spaces for boats?', 'rentOrLeaseDockingOrStorage'],
                    'fl_tie_down_storage_aircraft' => ['Will you rent or lease tie-down or storage spaces for aircraft?', 'tieDownStorageSpaces'],
                ]),
                [
                    'fl_another_party_manage_property' => nullableYesNoField('Does another party manage the rental property?', 'anotherPartyManageProperty', [
                        'when' => $flGate('applies_lodging_or_rentals'),
                    ]),
                    'fl_sell_tax_paid_fuel' => yesNoField('Will you sell tax-paid gasoline, diesel fuel, or aviation fuel?', 'sellTaxPaidFuel', ['drives_conditional' => true]),
                ],
                $flYesNo([
                    'fl_gas_station_only' => ['Gas station only?', 'gasStationOnly'],
                    'fl_gas_station_convenience_store' => ['Gas station and convenience store?', 'gasStationConvenienceStore'],
                    'fl_truck_stop' => ['Truck stop?', 'truckStop'],
                    'fl_marine_fueling' => ['Marine fueling?', 'marineFueling'],
                    'fl_aircraft_fueling' => ['Aircraft fueling?', 'aircraftFueling'],
                    'fl_bulk_fuel_reseller' => ['Reseller of fuel in bulk quantities?', 'bulkFuelReseller'],
                ], ['==' => [['var' => 'fl_sell_tax_paid_fuel'], '1']]),
            ),
        ],

        'fl_vending_machines' => [
            'title' => 'Florida Vending & Amusement Machines',
            'description' => 'Shown because vending or coin-operated machines apply to Florida.',
            'fields' => array_merge(
                $flYesNo([
                    'fl_coin_op_machines_other_businesses' => ["Will you place/operate coin-operated amusement machines at other businesses' locations?", 'coinOperatedMachinesOtherBusinesses'],
                    'fl_coin_op_machines_own_business' => ['Will you operate coin-operated amusement machines at your own location?', 'coinOperatedMachinesOwnBusinesses'],
                    'fl_self_operate_amusement_machine' => ['Will you self-operate some or all amusement machines at this location?', 'selfOperateAmusementMachine'],
                    'fl_written_agreement_machines' => ['Do you have a written agreement with the location owner to operate the machines?', 'writtenAgreementToOperateMachines'],
                    'fl_food_vending_machines_other_businesses' => ["Will you place/operate food or beverage vending machines at other businesses' locations?", 'foodVendingMachinesOtherBusinesses'],
                    'fl_vending_machines_own_business' => ['Will you operate vending machines at your own business location?', 'vendingMachinesOwnBusinesses'],
                ], $flGate('applies_vending')),
                [
                    'fl_food_or_beverage_vending_placed' => [
                        'type' => 'text',
                        'label' => 'Food or beverage vending machines placed (count)',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'when' => $flGate('applies_vending'),
                        'source_name' => 'foodOrBeverageVendingMachinesPlaced',
                    ],
                    'fl_nonfood_vending_placed' => [
                        'type' => 'text',
                        'label' => 'Nonfood or nonbeverage vending machines placed (count)',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'when' => $flGate('applies_vending'),
                        'source_name' => 'nonfoodOrNonbeverageVendingMachinesPlaced',
                    ],
                    'fl_food_or_beverage_vending_owned' => [
                        'type' => 'text',
                        'label' => 'Food or beverage vending machines owned (count)',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'when' => $flGate('applies_vending'),
                        'source_name' => 'foodOrBeverageVendingMachineOwned',
                    ],
                    'fl_nonfood_vending_owned' => [
                        'type' => 'text',
                        'label' => 'Nonfood or nonbeverage vending machines owned (count)',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'when' => $flGate('applies_vending'),
                        'source_name' => 'nonFoodNonBeverageOwned',
                    ],
                ],
            ),
        ],

        'fl_employment_and_reemployment' => [
            'title' => 'Florida Employment & Reemployment Tax',
            'description' => 'Shown because employees/payroll applies to Florida.',
            'groups' => [
                ['title' => 'Workers', 'fields' => [
                    'fl_lease_workers', 'fl_use_contractors',
                ]],
                ['title' => 'Reemployment (UT/RT) Account', 'fields' => [
                    'fl_registered_for_reemployment_tax', 'fl_rt_account_number',
                    'fl_reporting_wages_to_florida', 'fl_reactivating_ut_account',
                    'fl_actively_paying_florida_ut',
                ]],
                ['title' => 'Employer Classification', 'fields' => [
                    'fl_employment_type', 'fl_domestic_employer', 'fl_nonprofit_organization',
                    'fl_agricultural_employer',
                ]],
                ['title' => 'Wage Thresholds', 'fields' => [
                    'fl_gross_wages_1500', 'fl_date_reached_1500',
                    'fl_gross_wages_10000', 'fl_date_reached_10000',
                    'fl_one_employee_20_weeks', 'fl_last_day_of_20_week',
                ]],
            ],
            'fields' => array_merge(
                $flYesNo([
                    'fl_lease_workers' => ['Do you lease workers from an employee leasing company?', 'leaseWorkers'],
                    'fl_use_contractors' => ['Do you use the services of persons in Florida whom you treat as independent contractors?', 'useContractors'],
                    'fl_registered_for_reemployment_tax' => ['Is your business already registered for reemployment tax?', 'registeredForReemploymentTax'],
                    'fl_reporting_wages_to_florida' => ['Are you currently reporting wages to the state?', 'reportingWagesToFlorida'],
                    'fl_reactivating_ut_account' => ['Are you reactivating an existing FL UT account?', 'reactivatingUTAccount'],
                    'fl_actively_paying_florida_ut' => ['Are you actively paying Florida UT?', 'activelyPayingFloridaUT'],
                    'fl_domestic_employer' => ['Are you a domestic employer?', 'domesticEmployer'],
                    'fl_nonprofit_organization' => ['Are you a non-profit organization?', 'nonprofitOrganization'],
                    'fl_agricultural_employer' => ['Are you an agricultural employer?', 'agriculturalEmployer'],
                    'fl_gross_wages_1500' => ['Will you pay gross wages of at least $1,500 in a calendar quarter?', 'grossWagesOf1500'],
                    'fl_gross_wages_10000' => ['Will you pay gross wages of at least $10,000 in a calendar quarter?', 'grossWagesOf10000'],
                    'fl_one_employee_20_weeks' => ['Will you have one or more employees for 20 or more weeks in a calendar year?', 'oneEmployee20Weeks'],
                ], $flGate('applies_employees_or_payroll')),
                [
                    'fl_rt_account_number' => [
                        'type' => 'text',
                        'label' => 'FL Reemployment Tax (RT) Account Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => $flGate('applies_employees_or_payroll'),
                        'source_name' => 'rtAccountNumber',
                    ],
                    'fl_employment_type' => [
                        'type' => 'select',
                        'label' => 'Employment Type',
                        'options' => [
                            'regular' => 'Regular Employer',
                            'nonprofit' => 'Nonprofit Organization',
                            'indian_tribe' => 'Indian Tribe or Tribal Unit',
                            'agricultural_noncitrus' => 'Agricultural (noncitrus)',
                            'agricultural_citrus' => 'Agricultural (citrus)',
                            'agricultural_crew_chief' => 'Agricultural (crew chief)',
                        ],
                        'rules' => ['nullable'],
                        'when' => $flGate('applies_employees_or_payroll'),
                        'source_name' => 'employmentType',
                    ],
                    'fl_date_reached_1500' => [
                        'type' => 'date',
                        'label' => 'Date you reached (or will reach) $1,500 in gross wages',
                        'rules' => ['nullable', 'date'],
                        'when' => $flGate('applies_employees_or_payroll'),
                        'source_name' => 'dateReached1500GrossWages',
                    ],
                    'fl_date_reached_10000' => [
                        'type' => 'date',
                        'label' => 'Date you reached (or will reach) $10,000 in gross wages',
                        'rules' => ['nullable', 'date'],
                        'when' => $flGate('applies_employees_or_payroll'),
                        'source_name' => 'dateReached10000Wages',
                    ],
                    'fl_last_day_of_20_week' => [
                        'type' => 'date',
                        'label' => 'Last day of the 20th week of employment',
                        'rules' => ['nullable', 'date'],
                        'when' => $flGate('applies_employees_or_payroll'),
                        'source_name' => 'lastDayOf20Week',
                    ],
                ],
            ),
        ],

        'fl_special_taxes' => [
            'title' => 'Florida Special Taxes',
            'description' => 'Communications services, documentary stamp, gross receipts, and severance taxes.',
            'groups' => [
                ['title' => 'Communications Services', 'fields' => [
                    'fl_sell_communication_services',
                    'fl_comm_telephone', 'fl_comm_video', 'fl_comm_paging', 'fl_comm_satellite',
                    'fl_comm_fax', 'fl_comm_pay_telephone', 'fl_comm_reseller', 'fl_comm_prepaid',
                    'fl_comm_other',
                    'fl_direct_pay_communications',
                ]],
                ['title' => 'Documentary Stamp Tax', 'fields' => [
                    'fl_written_obligations', 'fl_five_or_more_written_obligations',
                ]],
                ['title' => 'Gross Receipts (Power & Gas)', 'fields' => [
                    'fl_utility_distribution_facility', 'fl_utility_electric',
                    'fl_utility_natural_gas', 'fl_import_natural_gas',
                ]],
                ['title' => 'Severance Taxes', 'fields' => [
                    'fl_extract_from_soil_or_water',
                    'fl_extract_oil', 'fl_extract_gas', 'fl_extract_sulfur',
                    'fl_extract_solid_minerals', 'fl_extract_lime_rock',
                ]],
            ],
            'fields' => array_merge(
                [
                    'fl_sell_communication_services' => yesNoField('Will you sell communications services?', 'sellCommunicationServices', ['drives_conditional' => true]),
                ],
                collect([
                    'fl_comm_telephone' => ['Telephone service', 'TelephoneService'],
                    'fl_comm_video' => ['Video service', 'VideoService'],
                    'fl_comm_paging' => ['Paging service', 'PagingService'],
                    'fl_comm_satellite' => ['Direct-to-home satellite service', 'DirectToHomeSatteliteService'],
                    'fl_comm_fax' => ['Fax service', 'FaxService'],
                    'fl_comm_pay_telephone' => ['Pay telephone service', 'PayTelephoneService'],
                    'fl_comm_reseller' => ['Reseller', 'Reseller'],
                    'fl_comm_prepaid' => ['Prepaid calling arrangements', 'PrepaidCallingArrangements'],
                    'fl_comm_other' => ['Other services', 'OtherServices'],
                ])->map(fn ($def) => [
                    'type' => 'checkbox',
                    'label' => $def[0],
                    'when' => ['==' => [['var' => 'fl_sell_communication_services'], '1']],
                    'source_name' => 'CommunicationServices[]',
                    'source_value' => $def[1],
                ])->all(),
                [
                    'fl_direct_pay_communications' => nullableYesNoField('Are you applying for a direct pay permit for communications services tax?', 'applyingForDirectPayPermitCommunicationServices', [
                        'when' => ['==' => [['var' => 'fl_sell_communication_services'], '1']],
                    ]),
                    'fl_written_obligations' => yesNoField('Will you enter into written obligations to pay money that are not recorded with the Clerk of Court?', 'writtenObligationsWithCustomers'),
                    'fl_five_or_more_written_obligations' => yesNoField('Do you anticipate executing five or more written obligations per month?', 'fiveOrMoreWrittenObligations'),
                    'fl_utility_distribution_facility' => yesNoField('Do you own or operate an electric or natural/manufactured gas utility distribution facility?', 'electricOrNaturalGasDistribution', ['drives_conditional' => true]),
                    'fl_utility_electric' => nullableYesNoField('Electric?', 'electric', [
                        'when' => ['==' => [['var' => 'fl_utility_distribution_facility'], '1']],
                    ]),
                    'fl_utility_natural_gas' => nullableYesNoField('Natural or manufactured gas?', 'naturalOrManufacturedGas', [
                        'when' => ['==' => [['var' => 'fl_utility_distribution_facility'], '1']],
                    ]),
                    'fl_import_natural_gas' => yesNoField('Will you import natural or manufactured gas into Florida for your own use?', 'importNaturalOrManufacturedGas'),
                    'fl_extract_from_soil_or_water' => yesNoField('Will you extract oil, gas, sulfur, solid minerals, phosphate rock, lime rock, sand, or heavy minerals?', 'extractFromSoilOrWater', ['drives_conditional' => true]),
                ],
                collect([
                    'fl_extract_oil' => ['Extracting oil for sale', 'ExtractingOilForSale'],
                    'fl_extract_gas' => ['Extracting gas for sale', 'ExtractingGasForSale'],
                    'fl_extract_sulfur' => ['Extracting sulfur for sale', 'ExtractingSulfurForSale'],
                    'fl_extract_solid_minerals' => ['Extracting solid minerals', 'ExtractingSolidMinerals'],
                    'fl_extract_lime_rock' => ['Extracting lime rock', 'ExtractingLimeRock'],
                ])->map(fn ($def) => [
                    'type' => 'checkbox',
                    'label' => $def[0],
                    'when' => ['==' => [['var' => 'fl_extract_from_soil_or_water'], '1']],
                    'source_name' => 'ExtractionActivities[]',
                    'source_value' => $def[1],
                ])->all(),
            ),
        ],

        'fl_acquisition' => [
            'title' => 'Florida Acquisition Details',
            'description' => 'Shown because you purchased an existing business in Florida.',
            'fields' => array_merge(
                [
                    'fl_portion_of_business_acquired' => [
                        'type' => 'radio',
                        'label' => 'What portion of the business was acquired?',
                        'options' => ['all' => 'All', 'part' => 'Part'],
                        'rules' => ['nullable', 'in:all,part'],
                        'when' => $flGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'portionOfBusinessAcquired',
                    ],
                ],
                $flYesNo([
                    'fl_business_operating_when_purchased' => ['Was the business operating when purchased?', 'businessOperatingWhenPurchased'],
                    'fl_business_had_employees_when_purchased' => ['Did the business have employees when purchased?', 'businessHaveEmployeesWhenPurchased'],
                    'fl_acquired_employees' => ['Did you acquire the employees?', 'acquireEmployees'],
                    'fl_share_any_ownership' => ['Do you share any ownership with the previous business?', 'shareAnyOwnership'],
                ], $flGate('applies_purchased_or_acquired_business')),
                [
                    'fl_date_business_closed' => [
                        'type' => 'date',
                        'label' => 'Date the business closed (if not operating)',
                        'rules' => ['nullable', 'date'],
                        'when' => $flGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'dateBusinessClosed',
                    ],
                    'fl_previous_owner_sales_tax_certificate' => [
                        'type' => 'text',
                        'label' => "Previous Owner's FL Sales Tax Certificate Number",
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => $flGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'previousOwnerSalesTaxCertificateNumber',
                    ],
                    'fl_previous_owner_ut_account' => [
                        'type' => 'text',
                        'label' => "Previous Owner's FL UT Account Number",
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => $flGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'previousOwnerUTAccountNumber',
                    ],
                ],
            ),
        ],
    ],
];

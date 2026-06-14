<?php

/**
 * New York — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/newYork/application/` (organizationInformation,
 * primary, businessInformation, entityQuestions, generalQuestions).
 *
 * Collapsed into core: corp shareholder >50% block + LLC tax-matters
 * (entity_extras), publicly traded (corporate_extras), bank details,
 * card acceptance (applies_accepts_cards), fuel/diesel/heating fuel
 * (applies_fuel), passenger car rentals (applies_vehicle_rentals),
 * internet sales + website (applies_internet_or_mail_order), annual
 * sales (matrix_annual_sales — exported as the legacy range bands),
 * multi-location count (derived from locations[]).
 */
// Per-person NY questions, split into two on-screen groups: the role /
// authority questions (what this person does in the business) and the
// background-disclosure questions (legal / compliance history). The group
// label rides along on each field so the modal renders a subheading.
$nyRoleGroup = 'Role & Authority';
$nyBackgroundGroup = 'Background Disclosures';

$nyPersonComplianceQuestions = [
    'ny_actively_operating' => ['Will this person be actively involved in operating this business on a daily basis?', 'primaryContactActivelyOperatingBusiness', $nyRoleGroup],
    'ny_deciding_financial_obligations' => ['Will this person be involved in deciding which financial obligations are paid?', 'primaryContactDecidingFinancialObligations', $nyRoleGroup],
    'ny_personnel_activity' => ['Will this person be involved in personnel activity (such as hiring or firing)?', 'primaryContactPersonnelActivity', $nyRoleGroup],
    'ny_responsible_person' => ['Is this person a Responsible Person?', 'primaryContactResponsiblePerson', $nyRoleGroup],
    'ny_check_signing_authority' => ['Will this person have check signing authority?', 'primaryContactCheckSigningAuthority', $nyRoleGroup],
    'ny_prepare_tax_returns' => ['Will this person prepare tax returns?', 'primaryContactPrepareTaxReturns', $nyRoleGroup],
    'ny_business_decisions' => ['Will this person have authority over business decisions?', 'primaryContactBusinessDecisions', $nyRoleGroup],
    'ny_tax_manager' => ['Is this person a tax manager or general manager?', 'primaryContactTaxManager', $nyRoleGroup],
    'ny_open_liens' => ['Does this person have any open, unsatisfied judgments, injunctions, or liens in effect today?', 'primaryContactOpenLiens', $nyBackgroundGroup],
    'ny_felony_pending' => ['Does this person have any felony, misdemeanor, and/or administrative charges currently pending?', 'primaryContactFelonyPending', $nyBackgroundGroup],
    'ny_liens_past_five_years' => ['In the last five years, have any judgments, injunctions, or liens been issued against this person?', 'primaryContactLiens', $nyBackgroundGroup],
    'ny_permit_terminated' => ['In the last five years, has this person had any permit, license, concession, franchise, or lease terminated for cause or revoked?', 'primaryContactPermitTerminated', $nyBackgroundGroup],
    'ny_investigated' => ['In the last five years, has this person been investigated by any governmental or quasi-governmental agency?', 'primaryContactInvestigated', $nyBackgroundGroup],
    'ny_misdemeanor' => ['In the last five years, has this person been convicted of a misdemeanor or found in violation of any administrative, statutory, or regulatory provisions?', 'primaryContactMisdemeanor', $nyBackgroundGroup],
    'ny_sanction_imposed' => ['In the last five years, has this person had any sanction imposed from a judicial or administrative disciplinary proceeding?', 'primaryContactSanctionImposed', $nyBackgroundGroup],
    'ny_failed_to_file' => ['In the last five years, has this person failed to file any applicable federal, state, or New York City tax return by its due date?', 'primaryContactFailedToFile', $nyBackgroundGroup],
    'ny_failed_to_pay_taxes' => ['In the last five years, has this person failed to pay any applicable taxes or assessed government charges by the due date?', 'primaryContactFailedToPayTaxes', $nyBackgroundGroup],
    'ny_bankruptcy' => ['In the past seven years, has any bankruptcy been initiated by or against this person?', 'primaryContactBankruptcy', $nyBackgroundGroup],
    'ny_felony_business_conduct' => ['In the last ten years, has this person been convicted of a felony or any crime related to truthfulness and/or business conduct?', 'primaryContactFelonyBusinessConduct', $nyBackgroundGroup],
];

return [
    'extends' => 'base',

    'state_steps' => [
        'ny_entity_compliance' => [
            'title' => 'New York Entity Compliance',
            'description' => 'Tax assessment, compliance, and related-entity questions NY requires.',
            'groups' => [
                ['title' => 'Assessments & Compliance', 'fields' => [
                    'ny_owner_tax_assessment_not_paid', 'ny_owner_under_protest',
                    'ny_entity_issued_tax_assessment', 'ny_entity_under_protest',
                    'ny_entity_tax_crime', 'ny_owner_tax_crime', 'ny_entity_certificate_revoked',
                    'ny_previous_sales_tax_certificate',
                ]],
                ['title' => 'Related Entities', 'fields' => [
                    'ny_entity_owned_by_different_entity', 'ny_entity_owning_name',
                    'ny_entity_owning_id_type', 'ny_entity_owning_number', 'ny_entity_owning_address',
                    'ny_different_entity_reports_income', 'ny_different_entity_name', 'ny_different_entity_number',
                ]],
                ['title' => 'Franchise', 'fields' => [
                    'ny_franchise', 'ny_franchisors_name', 'ny_franchisors_number', 'ny_franchisors_address',
                ]],
            ],
            'fields' => [
                'ny_owner_tax_assessment_not_paid' => yesNoField('Has any owner received a sales or use tax assessment that has not been paid in full?', 'ownerTaxAssessmentNotPaid', ['drives_conditional' => true]),
                'ny_owner_under_protest' => nullableYesNoField('Is it currently under protest or being paid through an Installment Payment Agreement (IPA)?', 'ownerUnderProtest', [
                    'when' => ['==' => [['var' => 'ny_owner_tax_assessment_not_paid'], '1']],
                ]),
                'ny_entity_issued_tax_assessment' => yesNoField('Has any tax assessment been issued to the entity that has not been paid in full?', 'entityIssuedTaxAssessment', ['drives_conditional' => true]),
                'ny_entity_under_protest' => nullableYesNoField('Is it currently under protest or being paid through an IPA?', 'entityUnderProtest', [
                    'when' => ['==' => [['var' => 'ny_entity_issued_tax_assessment'], '1']],
                ]),
                'ny_entity_tax_crime' => yesNoField('Has the entity been convicted of any tax crime in the past year?', 'entityTaxCrime'),
                'ny_owner_tax_crime' => yesNoField('Has any owner been convicted of any tax crime during the past year?', 'ownerTaxCrime'),
                'ny_entity_certificate_revoked' => yesNoField('If this entity previously held a sales tax Certificate of Authority, was it revoked or suspended in the last year?', 'entityCertificateRevoked'),
                'ny_previous_sales_tax_certificate' => yesNoField('Has this entity previously held a sales tax Certificate of Authority?', 'previousSalesTaxCertificate'),

                'ny_entity_owned_by_different_entity' => yesNoField('Is the entity applying for the Certificate of Authority owned by a different entity?', 'entityOwnedByDifferentEntity', ['drives_conditional' => true]),
                'ny_entity_owning_name' => [
                    'type' => 'text',
                    'label' => 'Owning Entity Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ny_entity_owned_by_different_entity'], '1']],
                    'source_name' => 'entityOwningName',
                ],
                'ny_entity_owning_id_type' => [
                    'type' => 'select',
                    'label' => 'Owning Entity ID Type',
                    'options' => ['ein' => 'EIN', 'ssn' => 'SSN', 'tin' => 'TIN'],
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ny_entity_owned_by_different_entity'], '1']],
                    'source_name' => 'entityOwningIDType',
                ],
                'ny_entity_owning_number' => [
                    'type' => 'text',
                    'label' => 'Owning Entity ID Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_entity_owned_by_different_entity'], '1']],
                    'sensitive' => true,
                    'source_name' => 'entityOwningNumber',
                ],
                'ny_entity_owning_address' => [
                    'type' => 'address',
                    'label' => 'Owning Entity Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ny_entity_owned_by_different_entity'], '1']],
                ],
                'ny_different_entity_reports_income' => yesNoField('Will a different entity or individual report the income of this business?', 'differentEntityReportIncome', ['drives_conditional' => true]),
                'ny_different_entity_name' => [
                    'type' => 'text',
                    'label' => 'Name of the legal entity or individual reporting the income',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ny_different_entity_reports_income'], '1']],
                    'source_name' => 'differentEntityReportIncomeName',
                ],
                'ny_different_entity_number' => [
                    'type' => 'text',
                    'label' => 'Their EIN or SSN',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_different_entity_reports_income'], '1']],
                    'sensitive' => true,
                    'source_name' => 'differentEntityReportIncomeNumber',
                ],

                'ny_franchise' => yesNoField('Are you a franchisee?', 'franchise', ['drives_conditional' => true]),
                'ny_franchisors_name' => [
                    'type' => 'text',
                    'label' => "Franchisor's Name",
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ny_franchise'], '1']],
                    'source_name' => 'franchisorsName',
                ],
                'ny_franchisors_number' => [
                    'type' => 'text',
                    'label' => "Franchisor's ID Number (EIN or TIN)",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_franchise'], '1']],
                    'source_name' => 'franchisorsNumber',
                ],
                'ny_franchisors_address' => [
                    'type' => 'address',
                    'label' => "Franchisor's Address",
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ny_franchise'], '1']],
                ],
            ],
        ],

        'ny_sales_projections' => [
            'title' => 'New York Sales Projections',
            'description' => 'NY-specific narrative and expected sales tax.',
            'fields' => [
                'ny_describe_your_business' => [
                    'type' => 'textarea',
                    'rows' => 4,
                    'label' => 'Describe Your Business (NY-specific narrative)',
                    'rules' => ['required', 'string', 'min:20', 'max:500'],
                    'help' => 'Prefilled suggestion: your general business description from earlier.',
                    'source_name' => 'describeYourBusiness',
                ],
                'ny_expected_annual_sales_tax' => [
                    'type' => 'select',
                    'label' => 'How much sales tax do you expect to collect annually?',
                    'options' => [
                        '0_3000' => '$0 - $3,000',
                        '3001_300000' => '$3,001 - $300,000',
                        '300001_2000000' => '$300,001 - $2,000,000',
                        '2000001_plus' => '$2,000,001 or higher',
                    ],
                    'rules' => ['required'],
                    'source_name' => 'expectedAnnualSalesTax',
                ],
            ],
        ],

        'ny_licenses_and_localities' => [
            'title' => 'New York Licenses & Local Taxes',
            'description' => 'NY license cross-references and locality-specific activities.',
            'groups' => [
                ['title' => 'Licenses & Registrations', 'fields' => [
                    'ny_licensed_by_sla', 'ny_sla_license_number',
                    'ny_licensed_by_lottery', 'ny_lottery_retailer_number',
                    'ny_registered_with_dmv', 'ny_dmv_facility_number',
                ]],
                ['title' => 'Products & Channels', 'fields' => [
                    'ny_sell_electricity', 'ny_sell_clothing', 'ny_not_make_retail_sales',
                    'ny_flea_markets', 'ny_sidewalk_vendor',
                ]],
                ['title' => 'New York City', 'fields' => [
                    'ny_provide_parking', 'ny_provide_beauty_services',
                    'ny_credit_rating_services', 'ny_hotel_accommodations',
                ]],
                ['title' => 'Nassau & Niagara Counties', 'fields' => [
                    'ny_hotel_in_nassau', 'ny_restaurant_in_nassau', 'ny_sell_admissions_in_niagara',
                ]],
            ],
            'fields' => [
                'ny_licensed_by_sla' => yesNoField('Are you, or do you intend to be, licensed by the NYS Liquor Authority (SLA)?', 'licensedBySLA', ['drives_conditional' => true]),
                'ny_sla_license_number' => [
                    'type' => 'text',
                    'label' => 'SLA License Number (if issued)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_licensed_by_sla'], '1']],
                    'source_name' => 'SLALicenseNumber',
                ],
                'ny_licensed_by_lottery' => yesNoField('Are you, or do you intend to be, licensed by the NYS Lottery?', 'licensedByLottery', ['drives_conditional' => true]),
                'ny_lottery_retailer_number' => [
                    'type' => 'text',
                    'label' => 'Lottery Retailer Number (if issued)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_licensed_by_lottery'], '1']],
                    'source_name' => 'lotteryRetailerNumber',
                ],
                'ny_registered_with_dmv' => yesNoField('Do you, or will you, operate a facility registered with the NYS Department of Motor Vehicles?', 'registeredWithDMV', ['drives_conditional' => true]),
                'ny_dmv_facility_number' => [
                    'type' => 'text',
                    'label' => 'DMV Facility Number (if available)',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_registered_with_dmv'], '1']],
                    'source_name' => 'DMVFacilityNumber',
                ],

                'ny_sell_electricity' => yesNoField('Do you intend to sell electricity or gas?', 'sellElectricity'),
                'ny_sell_clothing' => yesNoField('Do you intend to sell clothing or footwear?', 'sellClothing'),
                'ny_not_make_retail_sales' => yesNoField('Are you a manufacturer or wholesaler that does not make retail sales?', 'notMakeRetailSales'),
                'ny_flea_markets' => yesNoField('Will you participate solely in flea markets, antique shows, or other shows?', 'fleaMarkets'),
                'ny_sidewalk_vendor' => yesNoField('Will you conduct business solely as a sidewalk vendor?', 'sidewalkVendor'),

                'ny_provide_parking' => yesNoField('Do you intend to provide parking or garaging services in New York City?', 'provideParking'),
                'ny_provide_beauty_services' => yesNoField('Do you intend to provide beauty, barbering, or other personal services in New York City?', 'provideBeautyServices'),
                'ny_credit_rating_services' => yesNoField('Do you intend to provide credit rating or reporting services in New York City?', 'creditRatingServices'),
                'ny_hotel_accommodations' => yesNoField('Do you intend to provide hotel, motel, or other accommodations in New York City?', 'hotelAccommodations'),

                'ny_hotel_in_nassau' => yesNoField('Do you intend to provide hotel, motel, or other accommodations in Nassau or Niagara County?', 'hotelInNassau'),
                'ny_restaurant_in_nassau' => yesNoField('Do you intend to provide restaurant or tavern food or drink in Nassau or Niagara County?', 'restaurantInNassau'),
                'ny_sell_admissions_in_niagara' => yesNoField('Do you intend to sell admissions to places of amusement in Niagara County?', 'sellAdmissionsInNiagara'),
            ],
        ],

        'ny_tax_preparer_and_admin' => [
            'title' => 'New York Tax Preparer',
            'description' => 'Tax preparer details NY collects with the registration.',
            'groups' => [
                ['title' => 'Tax Preparer', 'fields' => [
                    'ny_have_tax_preparer', 'ny_tax_preparers_name', 'ny_tax_preparers_ein',
                    'ny_tax_preparers_nytprin', 'ny_tax_preparers_ptin',
                    ['ny_tax_preparers_phone', 'ny_tax_preparers_email'], 'ny_tax_preparers_address',
                ]],
            ],
            'fields' => [
                'ny_have_tax_preparer' => yesNoField('Do you have a tax preparer?', 'haveTaxPreparer', ['drives_conditional' => true]),
                'ny_tax_preparers_name' => [
                    'type' => 'text',
                    'label' => "Tax Preparer's or Firm's Name",
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersName',
                ],
                'ny_tax_preparers_ein' => [
                    'type' => 'text',
                    'label' => "Preparer's or Firm's EIN (if known)",
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersEIN',
                ],
                'ny_tax_preparers_nytprin' => [
                    'type' => 'text',
                    'label' => "Preparer's NYTPRIN (if known)",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersNYTPRIN',
                ],
                'ny_tax_preparers_ptin' => [
                    'type' => 'text',
                    'label' => "Preparer's PTIN (if known)",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersPTIN',
                ],
                'ny_tax_preparers_phone' => [
                    'type' => 'text',
                    'label' => 'Preparer Phone Number',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersPhoneNumber',
                ],
                'ny_tax_preparers_email' => [
                    'type' => 'email',
                    'label' => 'Preparer Email Address',
                    'rules' => ['nullable', 'email', 'max:255'],
                    'placeholder' => 'name@example.com',
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                    'source_name' => 'taxPreparersEmailAddress',
                ],
                'ny_tax_preparers_address' => [
                    'type' => 'address',
                    'label' => "Tax Preparer's Address",
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'ny_have_tax_preparer'], '1']],
                ],
            ],
        ],

        'ny_locations' => [
            'title' => 'New York Locations & Filing',
            'description' => 'How NY returns will be filed across your locations.',
            'fields' => [
                'ny_file_separate_return' => nullableYesNoField('If you have more than one NY location, will you file a separate sales tax return for each location?', 'fileSeparateReturn', [
                    'drives_conditional' => true,
                    'help' => 'Your NY locations were collected on the Business Locations step.',
                ]),
                'ny_one_return_all_locations' => nullableYesNoField('If no, will you file one sales tax return for all locations?', 'oneReturnForAllLocations', [
                    'when' => ['==' => [['var' => 'ny_file_separate_return'], '0']],
                ]),
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => array_merge(
                            [
                                'ny_profit_distribution_percentage' => [
                                    'type' => 'percent',
                                    'label' => 'Profit Distribution % (NY)',
                                    'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                                    'help' => 'Distinct from ownership %; NY collects this for each responsible person.',
                                    'source_name' => 'primaryContactProfitDistributionPercentage',
                                ],
                            ],
                            collect($nyPersonComplianceQuestions)->map(fn ($def) => [
                                'type' => 'radio',
                                'label' => $def[0],
                                'options' => ['1' => 'Yes', '0' => 'No'],
                                'rules' => ['required', 'in:0,1'],
                                'source_name' => $def[1],
                                'group' => $def[2],
                            ])->all(),
                        ),
                    ],
                ],
            ],
        ],
    ],
];

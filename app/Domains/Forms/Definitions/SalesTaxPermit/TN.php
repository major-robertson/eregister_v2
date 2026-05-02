<?php

/**
 * Tennessee — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/tennessee/application/`
 * (primary, organizationInformation, entityQuestions, businessInformation,
 * contactInformation) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Tennessee Identifiers', 'fields' => [
                    'tn_secretary_of_state_number', 'tn_taxpayer_number',
                ]],
                ['title' => 'Sales / Liability (RAP)', 'fields' => [
                    'tn_more_than_200_monthly', 'tn_exceed_4800_annual',
                    'tn_exceed_1200_taxable_services', 'tn_suppliers_do_not_collect_sales_tax',
                    'tn_more_than_500000', 'tn_over_50_affiliate',
                    'tn_only_perishable_grocery_items', 'tn_rap_filing_frequency',
                ]],
                ['title' => 'Manufacturer / Wholesaler / Alcohol', 'fields' => [
                    'tn_manufacturer_alcoholic_beverages', 'tn_distillery_in_tennessee',
                    'tn_manufacturer_or_wholesaler', 'tn_physical_presence',
                    'tn_direct_shipper_of_wine', 'tn_wholesaler_distributor_manufacturer',
                    'tn_sell_beer_or_tobacco', 'tn_food_candy_nonalcoholic',
                ]],
                ['title' => 'Authorized Contact', 'fields' => [
                    'tn_authorized_contact_name', 'tn_authorized_contact_phone',
                    'tn_authorized_contact_email',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── Tennessee-specific identifiers ─────────
                    'tn_secretary_of_state_number' => [
                        'type' => 'text',
                        'label' => 'TN Secretary of State Control Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Required for corporations and LLCs registered with the TN SOS.',
                        'source_name' => 'secretaryOfStateNumber',
                    ],
                    'tn_taxpayer_number' => [
                        'type' => 'text',
                        'label' => 'Tennessee Taxpayer Number (if previously issued)',
                        'rules' => ['nullable', 'digits:11'],
                        'help' => 'Leave blank if you have not been issued one.',
                        'source_name' => 'tennesseeTaxpayerNumber',
                    ],

                    // ───────── Sales / liability questions (RAP) ─────────
                    'tn_more_than_200_monthly' => yesNoField('Will your sales tax liability exceed $200 per month?', 'moreThan200SalesTaxMonthly'),
                    'tn_exceed_4800_annual' => yesNoField('Will your annual gross sales exceed $4,800?', 'exceed4800', ['drives_conditional' => true]),
                    'tn_exceed_1200_taxable_services' => nullableYesNoField('Will your taxable services exceed $1,200 annually?', 'exceed1200', [
                        'when' => ['==' => [['var' => 'tn_exceed_4800_annual'], '0']],
                    ]),
                    'tn_suppliers_do_not_collect_sales_tax' => [
                        // Inverted options (No=1, Yes=0) — keeps the data
                        // model consistent with the "do not collect" framing.
                        'type' => 'radio',
                        'label' => 'Do your suppliers collect Tennessee sales tax?',
                        'options' => ['1' => 'No', '0' => 'Yes'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'suppliersDoNotCollectTnSalesTax',
                    ],
                    'tn_more_than_500000' => yesNoField('Did you have more than $500,000 in TN sales in the last 12 months?', 'moreThan500000'),
                    'tn_over_50_affiliate' => yesNoField('Are you affiliated (>50%) with a TN business?', 'over50'),
                    'tn_only_perishable_grocery_items' => yesNoField('Do you sell only perishable grocery items?', 'onlyPerishableGroceryItems'),
                    'tn_rap_filing_frequency' => [
                        // Custom labels (Monthly / Quarterly) — not yes/no.
                        'type' => 'radio',
                        'label' => 'Filing Frequency',
                        'options' => ['1' => 'Monthly', '0' => 'Quarterly'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'RAPfiling',
                    ],

                    // ───────── Manufacturer / wholesaler / alcohol ─────────
                    'tn_manufacturer_alcoholic_beverages' => yesNoField('Are you a manufacturer of alcoholic beverages?', 'manufacturerAlcoholicBeverages'),
                    'tn_distillery_in_tennessee' => yesNoField('Do you operate a distillery in Tennessee?', 'distillaryInTennessee'),
                    'tn_manufacturer_or_wholesaler' => yesNoField('Are you a manufacturer or wholesaler?', 'manufacturerOrWholesaler', ['drives_conditional' => true]),
                    'tn_physical_presence' => nullableYesNoField('Do you have physical presence in Tennessee?', 'physicalPresence', [
                        'when' => ['==' => [['var' => 'tn_manufacturer_or_wholesaler'], '1']],
                    ]),
                    'tn_direct_shipper_of_wine' => yesNoField('Are you an ABC-licensed direct shipper of wine?', 'directShipperOfWine'),
                    'tn_wholesaler_distributor_manufacturer' => yesNoField('Are you a wholesaler, distributor, or manufacturer?', 'wholesalerDistributorManfacturer'),
                    'tn_sell_beer_or_tobacco' => yesNoField('Will you sell beer or tobacco to retailers?', 'sellBeerTobaccao'),
                    'tn_food_candy_nonalcoholic' => yesNoField('Will you sell food, candy, or non-alcoholic beverages to retailers?', 'foodCandyNonAlcoholicBeverages'),

                    // ───────── Authorized contact ─────────
                    'tn_authorized_contact_name' => [
                        'type' => 'text',
                        'label' => 'Authorized Contact Name',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'authorizedContactName',
                    ],
                    'tn_authorized_contact_phone' => [
                        'type' => 'text',
                        'label' => 'Authorized Contact Phone',
                        'rules' => ['required', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'authorizedContactPhoneNumber',
                    ],
                    'tn_authorized_contact_email' => [
                        'type' => 'email',
                        'label' => 'Authorized Contact Email',
                        'rules' => ['required', 'email', 'max:255'],
                        'placeholder' => 'name@example.com',
                        'source_name' => 'authorizedContactEmailAddress',
                    ],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'tn_middle_name' => [
                                'type' => 'text',
                                'label' => 'Middle Name (Tennessee)',
                                'rules' => ['nullable', 'string', 'max:60'],
                                'source_name' => 'primaryContactMiddleName',
                            ],
                            'tn_id_type' => [
                                'type' => 'select',
                                'label' => 'ID Type',
                                'options' => [
                                    'ssn' => 'Social Security Number',
                                    'itin' => 'Individual Tax Payer Number (ITIN)',
                                ],
                                'rules' => ['required'],
                                'source_name' => 'primaryContactIdType',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

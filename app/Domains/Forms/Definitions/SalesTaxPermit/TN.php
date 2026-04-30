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
                    'tn_more_than_200_monthly' => [
                        'type' => 'radio',
                        'label' => 'Will your sales tax liability exceed $200 per month?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'moreThan200SalesTaxMonthly',
                    ],
                    'tn_exceed_4800_annual' => [
                        'type' => 'radio',
                        'label' => 'Will your annual gross sales exceed $4,800?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'exceed4800',
                    ],
                    'tn_exceed_1200_taxable_services' => [
                        'type' => 'radio',
                        'label' => 'Will your taxable services exceed $1,200 annually?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'tn_exceed_4800_annual'], '0']],
                        'source_name' => 'exceed1200',
                    ],
                    'tn_suppliers_do_not_collect_sales_tax' => [
                        'type' => 'radio',
                        'label' => 'Do your suppliers collect Tennessee sales tax?',
                        'options' => ['1' => 'No', '0' => 'Yes'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'suppliersDoNotCollectTnSalesTax',
                    ],
                    'tn_more_than_500000' => [
                        'type' => 'radio',
                        'label' => 'Did you have more than $500,000 in TN sales in the last 12 months?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'moreThan500000',
                    ],
                    'tn_over_50_affiliate' => [
                        'type' => 'radio',
                        'label' => 'Are you affiliated (>50%) with a TN business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'over50',
                    ],
                    'tn_only_perishable_grocery_items' => [
                        'type' => 'radio',
                        'label' => 'Do you sell only perishable grocery items?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'onlyPerishableGroceryItems',
                    ],
                    'tn_rap_filing_frequency' => [
                        'type' => 'radio',
                        'label' => 'Filing Frequency',
                        'options' => ['1' => 'Monthly', '0' => 'Quarterly'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'RAPfiling',
                    ],

                    // ───────── Manufacturer / wholesaler / alcohol ─────────
                    'tn_manufacturer_alcoholic_beverages' => [
                        'type' => 'radio',
                        'label' => 'Are you a manufacturer of alcoholic beverages?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'manufacturerAlcoholicBeverages',
                    ],
                    'tn_distillery_in_tennessee' => [
                        'type' => 'radio',
                        'label' => 'Do you operate a distillery in Tennessee?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'distillaryInTennessee',
                    ],
                    'tn_manufacturer_or_wholesaler' => [
                        'type' => 'radio',
                        'label' => 'Are you a manufacturer or wholesaler?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'manufacturerOrWholesaler',
                    ],
                    'tn_physical_presence' => [
                        'type' => 'radio',
                        'label' => 'Do you have physical presence in Tennessee?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'tn_manufacturer_or_wholesaler'], '1']],
                        'source_name' => 'physicalPresence',
                    ],
                    'tn_direct_shipper_of_wine' => [
                        'type' => 'radio',
                        'label' => 'Are you an ABC-licensed direct shipper of wine?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'directShipperOfWine',
                    ],
                    'tn_wholesaler_distributor_manufacturer' => [
                        'type' => 'radio',
                        'label' => 'Are you a wholesaler, distributor, or manufacturer?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'wholesalerDistributorManfacturer',
                    ],
                    'tn_sell_beer_or_tobacco' => [
                        'type' => 'radio',
                        'label' => 'Will you sell beer or tobacco to retailers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellBeerTobaccao',
                    ],
                    'tn_food_candy_nonalcoholic' => [
                        'type' => 'radio',
                        'label' => 'Will you sell food, candy, or non-alcoholic beverages to retailers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'foodCandyNonAlcoholicBeverages',
                    ],

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

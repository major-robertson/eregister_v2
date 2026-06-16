<?php

/**
 * Tennessee — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/tennessee/application/` (organizationInformation,
 * primary, businessInformation, contactInformation, entityQuestions).
 *
 * Collapsed into core: DBA (core dba_name), authorized contact (core
 * authorized_contact_*), manufacturer/wholesaler role (core supply-chain
 * role checkboxes), alcohol manufacturer gate (applies_alcohol), physical
 * presence (applies_physical_presence), annual sales (matrix).
 *
 * §3A.2 fix applied: the legacy RAP/survey conditional chain is restored —
 * `exceed1200` shows when `exceed4800 == 1` (v2 had it inverted), and the
 * RAP chain gates each question on the prior answer.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Tennessee Sales Tax Permit Details',
            'description' => 'Tennessee registration and Retail Accountability Program questions.',
            'groups' => [
                ['title' => 'Tennessee Identifiers', 'fields' => [
                    'tn_secretary_of_state_number', 'tn_taxpayer_number',
                ]],
                ['title' => 'Sales & Use Tax Survey', 'fields' => [
                    'tn_more_than_200_monthly', 'tn_exceed_4800_annual', 'tn_exceed_1200_taxable_services',
                    'tn_suppliers_do_not_collect_sales_tax', 'tn_direct_shipper_of_wine',
                ]],
                ['title' => 'Alcohol (TN detail)', 'fields' => ['tn_distillery_in_tennessee']],
                ['title' => 'Retail Accountability Program', 'fields' => [
                    'tn_wholesaler_distributor_manufacturer', 'tn_sell_beer_or_tobacco',
                    'tn_food_candy_nonalcoholic', 'tn_more_than_500000', 'tn_over_50_affiliate',
                    'tn_only_perishable_grocery_items', 'tn_rap_filing_frequency',
                ]],
            ],
            'fields' => [
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

                'tn_more_than_200_monthly' => yesNoField('Do you expect to pay $200 or more in sales tax per month?', 'moreThan200SalesTaxMonthly'),
                // §3A.2.5: legacy chain — exceed1200 shows when exceed4800 == 1.
                'tn_exceed_4800_annual' => yesNoField('Will your gross sales exceed $4,800 per year?', 'exceed4800', ['drives_conditional' => true]),
                'tn_exceed_1200_taxable_services' => nullableYesNoField('Will your taxable services exceed $1,200 per year?', 'exceed1200', [
                    'when' => ['==' => [['var' => 'tn_exceed_4800_annual'], '1']],
                ]),
                'tn_suppliers_do_not_collect_sales_tax' => [
                    // Inverted options preserved from legacy (No = 1, Yes = 0).
                    'type' => 'radio',
                    'label' => 'Do your suppliers collect Tennessee sales tax?',
                    'options' => ['1' => 'No', '0' => 'Yes'],
                    'rules' => ['required', 'in:0,1'],
                    'source_name' => 'suppliersDoNotCollectTnSalesTax',
                ],
                'tn_direct_shipper_of_wine' => nullableYesNoField('Will you be licensed as a direct shipper of wine?', 'directShipperOfWine', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'TN']],
                ]),
                'tn_distillery_in_tennessee' => nullableYesNoField('Are you a distillery located in Tennessee?', 'distillaryInTennessee', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'TN']],
                ]),

                // Legacy RAP chain: each question gates the next.
                'tn_wholesaler_distributor_manufacturer' => yesNoField('Are you a wholesaler, distributor, or manufacturer?', 'wholesalerDistributorManfacturer', ['drives_conditional' => true]),
                'tn_sell_beer_or_tobacco' => nullableYesNoField('Will you sell beer or tobacco products to Tennessee retailers?', 'sellBeerTobaccao', [
                    'when' => ['==' => [['var' => 'tn_wholesaler_distributor_manufacturer'], '1']],
                    'drives_conditional' => true,
                ]),
                'tn_food_candy_nonalcoholic' => nullableYesNoField('Will you sell food, candy, or non-alcoholic beverages to Tennessee retailers that sell beer or tobacco?', 'foodCandyNonAlcoholicBeverages', [
                    'when' => ['==' => [['var' => 'tn_sell_beer_or_tobacco'], '1']],
                    'drives_conditional' => true,
                ]),
                'tn_more_than_500000' => nullableYesNoField('Do you anticipate selling more than $500,000 to Tennessee retailers?', 'moreThan500000', [
                    'when' => ['==' => [['var' => 'tn_food_candy_nonalcoholic'], '1']],
                    'drives_conditional' => true,
                ]),
                'tn_over_50_affiliate' => nullableYesNoField('Will sales be made solely to retailers that are affiliates (over 50% common ownership)?', 'over50', [
                    'when' => ['==' => [['var' => 'tn_more_than_500000'], '1']],
                    'drives_conditional' => true,
                ]),
                'tn_only_perishable_grocery_items' => nullableYesNoField('Will you only be selling perishable grocery items?', 'onlyPerishableGroceryItems', [
                    'when' => ['==' => [['var' => 'tn_over_50_affiliate'], '1']],
                    'drives_conditional' => true,
                ]),
                'tn_rap_filing_frequency' => [
                    'type' => 'radio',
                    'label' => 'You are required to file a RAP report. Will you file monthly or quarterly?',
                    'options' => ['1' => 'Monthly', '0' => 'Quarterly'],
                    'rules' => ['nullable', 'in:0,1'],
                    'when' => ['==' => [['var' => 'tn_only_perishable_grocery_items'], '1']],
                    'source_name' => 'RAPfiling',
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
                                    'itin' => 'Individual Taxpayer Identification Number (ITIN)',
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

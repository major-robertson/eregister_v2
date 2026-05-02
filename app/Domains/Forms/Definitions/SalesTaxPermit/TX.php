<?php

/**
 * Texas — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/texas/application/`
 * (primary, organizationInformation, entityQuestions, contactInformation,
 * businessInformation) plus matching JS validators.
 *
 * NAICS, FEIN, business contact, business address, formation state, and
 * the responsible_people repeater all live in base.php. Only TX-specific
 * questions are added here.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Texas Identifiers', 'fields' => [
                    'tx_taxpayer_number', 'tx_franchise_tax_id', 'tx_sos_file_number',
                    'tx_involved_in_merger', 'tx_business_location_in_texas',
                ]],
                ['title' => 'Industry & Activity', 'fields' => [
                    'tx_alcoholic_beverages', 'tx_alcoholic_beverages_permit',
                    'tx_temporary_locations', 'tx_temporary_location_name', 'tx_period_in_attendance',
                    'tx_mail_order', 'tx_sales_people_other_locations',
                    'tx_taxable_services_at_customer_location', 'tx_health_spa',
                    'tx_winery_outside_texas', 'tx_electronic_cigarettes',
                    'tx_electronic_cigarettes_online', 'tx_prepaid_wireless',
                    'tx_telecommunication_chapter_771', 'tx_sell_fireworks',
                    'tx_diesel_50hp_equipment', 'tx_other_distribution_points',
                    'tx_other_location_address',
                ]],
                ['title' => 'Nexus & Sales', 'fields' => [
                    'tx_exceeds_8k_monthly', 'tx_taking_orders_taxable_items',
                    'tx_receipts_from_personal_property', 'tx_franchisee_in_texas',
                    'tx_electronic_marketplace', 'tx_ownership_in_similar_business',
                    'tx_ownership_business_maintains_location', 'tx_ship_to_other_texas_cities',
                ]],
                ['title' => 'Banking & Payment', 'fields' => [
                    'tx_bank_name', 'tx_personal_bank', 'tx_accept_credit_cards',
                    'tx_payment_processor', 'tx_merchant_identification_number',
                ]],
                ['title' => 'Landlord', 'fields' => ['tx_landlord_owner_name', 'tx_landlord_address']],
                ['title' => 'Contacts', 'fields' => [
                    'tx_br_contact_name', 'tx_br_contact_phone', 'tx_br_contact_email',
                    'tx_alternate_contact', 'tx_alternate_contact_name',
                    'tx_alternate_contact_phone', 'tx_alternate_contact_email',
                ]],
            ]],
            'fields' => [
                'append' => [
                    // ───────── Texas-specific identifiers ─────────
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
                        'help' => 'Leave blank if you do not have a franchise tax ID yet.',
                    ],
                    'tx_sos_file_number' => [
                        'type' => 'text',
                        'label' => 'TX Secretary of State File Number',
                        'rules' => ['nullable', 'digits:10'],
                        'help' => 'Required for corporations, LLCs, LPs, and LLPs. 10 digits.',
                        'source_name' => 'texasFileNumber',
                    ],
                    'tx_involved_in_merger' => yesNoField('Has this entity been involved in a merger?', 'involvedInMerger'),
                    'tx_business_location_in_texas' => [
                        // Custom options (Texas / Another state) so this
                        // one can't use the yesNoField helper.
                        'type' => 'radio',
                        'label' => 'Is the principal place of business located in Texas?',
                        'options' => ['1' => 'Texas', '0' => 'Another state'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'businessLocation',
                    ],

                    // ───────── Industry / activity questions ─────────
                    'tx_alcoholic_beverages' => yesNoField('Will you sell alcoholic beverages?', 'alcoholicBeverages', ['drives_conditional' => true]),
                    'tx_alcoholic_beverages_permit' => [
                        'type' => 'select',
                        'label' => 'Which alcoholic beverages permit will you hold?',
                        'options' => [
                            'mixed_beverage' => 'Mixed Beverage',
                            'beer_and_wine' => 'Beer and Wine',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'tx_alcoholic_beverages'], '1']],
                        'source_name' => 'alcoholicBeveragesPermit',
                    ],
                    'tx_temporary_locations' => yesNoField('Will you sell at temporary locations (fairs, festivals, etc.)?', 'temporaryLocations', ['drives_conditional' => true]),
                    'tx_temporary_location_name' => [
                        'type' => 'text',
                        'label' => 'Temporary Location and/or Event Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'when' => ['==' => [['var' => 'tx_temporary_locations'], '1']],
                        'source_name' => 'temporaryLocationName',
                    ],
                    'tx_period_in_attendance' => [
                        'type' => 'text',
                        'label' => 'Period of Attendance',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'when' => ['==' => [['var' => 'tx_temporary_locations'], '1']],
                        'source_name' => 'periodInAttendance',
                    ],
                    'tx_mail_order' => yesNoField('Do you sell via internet and/or mail order?', 'mailOrder'),
                    'tx_sales_people_other_locations' => yesNoField('Do you have sales people operating in other locations?', 'salesPeople'),
                    'tx_taxable_services_at_customer_location' => yesNoField("Do you provide taxable services at a customer's location?", 'atCustomersLocation'),
                    'tx_health_spa' => yesNoField('Do you sell health spa memberships?', 'healthSpa'),
                    'tx_winery_outside_texas' => yesNoField('Are you a winery located outside Texas shipping wine to Texas customers?', 'wineryOutsideTexas'),
                    'tx_electronic_cigarettes' => yesNoField('Do you sell electronic cigarettes or vaping devices?', 'electronicCigarettes'),
                    'tx_electronic_cigarettes_online' => yesNoField('Do you sell e-cigarettes online or by mail?', 'electronicCigarettesOnline'),
                    'tx_prepaid_wireless' => yesNoField('Do you sell prepaid wireless telecommunications?', 'telecommunicationServices'),
                    'tx_telecommunication_chapter_771' => yesNoField('Do you provide telecommunication services under Tax Code Chapter 771?', 'telecommunicationServicesUnderChapter711'),
                    'tx_sell_fireworks' => yesNoField('Do you sell fireworks?', 'sellFireworks'),
                    'tx_diesel_50hp_equipment' => yesNoField('Do you sell or operate diesel-powered equipment of 50 horsepower or greater?', 'dieselPoweredEquipment'),
                    'tx_other_distribution_points' => yesNoField('Do you have other distribution centers, warehouses, or locations in Texas?', 'otherDistributionPoints', ['drives_conditional' => true]),
                    'tx_other_location_address' => [
                        'type' => 'address',
                        'label' => 'Other Distribution Location Address',
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'tx_other_distribution_points'], '1']],
                    ],

                    // ───────── Nexus / activity ─────────
                    'tx_exceeds_8k_monthly' => yesNoField('Will your monthly taxable sales exceed $8,000?', 'exceed8k'),
                    'tx_taking_orders_taxable_items' => yesNoField('Do representatives take orders for taxable items in Texas?', 'takingOrderTaxableItems'),
                    'tx_receipts_from_personal_property' => yesNoField('Do you have receipts from tangible personal property in Texas?', 'receiptFromPersonalProperty'),
                    'tx_franchisee_in_texas' => yesNoField('Are you a franchisee or licensee operating under a name in Texas?', 'franchiseeOperatingUnderName'),
                    'tx_electronic_marketplace' => yesNoField('Do you operate an electronic or physical marketplace for third-party sellers?', 'electronicOrPhysicalMarketplace'),
                    'tx_ownership_in_similar_business' => yesNoField('Do you have substantial ownership in a similar business?', 'ownershipInSimilarBusiness'),
                    'tx_ownership_business_maintains_location' => yesNoField('Do you have ownership in a business that maintains a location in Texas to promote sales?', 'ownershipInBusinessMaintainsLocation'),
                    'tx_ship_to_other_texas_cities' => yesNoField('Do you deliver or ship items to other cities or counties in Texas?', 'deliverOrShipItemToOtherCitiesInTexas'),

                    // ───────── Banking & payment ─────────
                    'tx_bank_name' => [
                        'type' => 'text',
                        'label' => 'Bank Name',
                        'rules' => ['required', 'string', 'max:100'],
                        'source_name' => 'bankName',
                    ],
                    'tx_personal_bank' => yesNoField('Is this also your personal bank?', 'personalBank'),
                    'tx_accept_credit_cards' => yesNoField('Do you accept credit card payments?', 'creditCardPayments', ['drives_conditional' => true]),
                    'tx_payment_processor' => [
                        'type' => 'text',
                        'label' => 'Online Payment Processor Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'when' => ['==' => [['var' => 'tx_accept_credit_cards'], '1']],
                        'source_name' => 'onlinePaymentProcessor',
                    ],
                    'tx_merchant_identification_number' => [
                        'type' => 'text',
                        'label' => 'Merchant Identification Number (MID)',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'source_name' => 'merchantIdentificationNumber',
                    ],

                    // ───────── Landlord / location ─────────
                    'tx_landlord_owner_name' => [
                        'type' => 'text',
                        'label' => 'Landlord / Property Owner Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'source_name' => 'landlordOwnerName',
                    ],
                    'tx_landlord_address' => [
                        'type' => 'address',
                        'label' => 'Landlord / Property Owner Address',
                        'rules' => ['nullable'],
                    ],

                    // ───────── Business contact / alternate contact ─────────
                    'tx_br_contact_name' => [
                        'type' => 'text',
                        'label' => 'Business Records Contact Name',
                        'rules' => ['required', 'string', 'max:100'],
                        'source_name' => 'brContactName',
                    ],
                    'tx_br_contact_phone' => [
                        'type' => 'text',
                        'label' => 'Business Records Contact Phone',
                        'rules' => ['required', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'brContactPhoneNumber',
                    ],
                    'tx_br_contact_email' => [
                        'type' => 'email',
                        'label' => 'Business Records Contact Email',
                        'rules' => ['required', 'email', 'max:255'],
                        'placeholder' => 'name@example.com',
                        'source_name' => 'brContactEmailAddress',
                    ],
                    'tx_alternate_contact' => yesNoField('Add an alternate contact?', 'brAlternateContact', ['drives_conditional' => true]),
                    'tx_alternate_contact_name' => [
                        'type' => 'text',
                        'label' => 'Alternate Contact Name',
                        'rules' => ['nullable', 'string', 'max:100'],
                        'when' => ['==' => [['var' => 'tx_alternate_contact'], '1']],
                        'source_name' => 'brContactNameAlternate',
                    ],
                    'tx_alternate_contact_phone' => [
                        'type' => 'text',
                        'label' => 'Alternate Contact Phone',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'when' => ['==' => [['var' => 'tx_alternate_contact'], '1']],
                        'source_name' => 'brContactPhoneNumberAlternate',
                    ],
                    'tx_alternate_contact_email' => [
                        'type' => 'email',
                        'label' => 'Alternate Contact Email',
                        'rules' => ['nullable', 'email', 'max:255'],
                        'placeholder' => 'name@example.com',
                        'when' => ['==' => [['var' => 'tx_alternate_contact'], '1']],
                        'source_name' => 'brContactEmailAddressAlternate',
                    ],
                ],
            ],
        ],

        // No state-specific responsible_people fields: driver license
        // state and number are now collected once in the base
        // responsible_people repeater for every entity.
    ],
];

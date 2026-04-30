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
                    'tx_involved_in_merger' => [
                        'type' => 'radio',
                        'label' => 'Has this entity been involved in a merger?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'involvedInMerger',
                    ],
                    'tx_business_location_in_texas' => [
                        'type' => 'radio',
                        'label' => 'Is the principal place of business located in Texas?',
                        'options' => ['1' => 'Texas', '0' => 'Another state'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'businessLocation',
                    ],

                    // ───────── Industry / activity questions ─────────
                    'tx_alcoholic_beverages' => [
                        'type' => 'radio',
                        'label' => 'Will you sell alcoholic beverages?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'alcoholicBeverages',
                    ],
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
                    'tx_temporary_locations' => [
                        'type' => 'radio',
                        'label' => 'Will you sell at temporary locations (fairs, festivals, etc.)?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'temporaryLocations',
                    ],
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
                    'tx_mail_order' => [
                        'type' => 'radio',
                        'label' => 'Do you sell via internet and/or mail order?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'mailOrder',
                    ],
                    'tx_sales_people_other_locations' => [
                        'type' => 'radio',
                        'label' => 'Do you have sales people operating in other locations?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'salesPeople',
                    ],
                    'tx_taxable_services_at_customer_location' => [
                        'type' => 'radio',
                        'label' => 'Do you provide taxable services at a customer\'s location?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'atCustomersLocation',
                    ],
                    'tx_health_spa' => [
                        'type' => 'radio',
                        'label' => 'Do you sell health spa memberships?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'healthSpa',
                    ],
                    'tx_winery_outside_texas' => [
                        'type' => 'radio',
                        'label' => 'Are you a winery located outside Texas shipping wine to Texas customers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'wineryOutsideTexas',
                    ],
                    'tx_electronic_cigarettes' => [
                        'type' => 'radio',
                        'label' => 'Do you sell electronic cigarettes or vaping devices?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'electronicCigarettes',
                    ],
                    'tx_electronic_cigarettes_online' => [
                        'type' => 'radio',
                        'label' => 'Do you sell e-cigarettes online or by mail?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'electronicCigarettesOnline',
                    ],
                    'tx_prepaid_wireless' => [
                        'type' => 'radio',
                        'label' => 'Do you sell prepaid wireless telecommunications?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'telecommunicationServices',
                    ],
                    'tx_telecommunication_chapter_771' => [
                        'type' => 'radio',
                        'label' => 'Do you provide telecommunication services under Tax Code Chapter 771?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'telecommunicationServicesUnderChapter711',
                    ],
                    'tx_sell_fireworks' => [
                        'type' => 'radio',
                        'label' => 'Do you sell fireworks?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellFireworks',
                    ],
                    'tx_diesel_50hp_equipment' => [
                        'type' => 'radio',
                        'label' => 'Do you sell or operate diesel-powered equipment of 50 horsepower or greater?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'dieselPoweredEquipment',
                    ],
                    'tx_other_distribution_points' => [
                        'type' => 'radio',
                        'label' => 'Do you have other distribution centers, warehouses, or locations in Texas?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'otherDistributionPoints',
                    ],
                    'tx_other_location_address' => [
                        'type' => 'address',
                        'label' => 'Other Distribution Location Address',
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'tx_other_distribution_points'], '1']],
                    ],

                    // ───────── Nexus / activity ─────────
                    'tx_exceeds_8k_monthly' => [
                        'type' => 'radio',
                        'label' => 'Will your monthly taxable sales exceed $8,000?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'exceed8k',
                    ],
                    'tx_taking_orders_taxable_items' => [
                        'type' => 'radio',
                        'label' => 'Do representatives take orders for taxable items in Texas?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'takingOrderTaxableItems',
                    ],
                    'tx_receipts_from_personal_property' => [
                        'type' => 'radio',
                        'label' => 'Do you have receipts from tangible personal property in Texas?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'receiptFromPersonalProperty',
                    ],
                    'tx_franchisee_in_texas' => [
                        'type' => 'radio',
                        'label' => 'Are you a franchisee or licensee operating under a name in Texas?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'franchiseeOperatingUnderName',
                    ],
                    'tx_electronic_marketplace' => [
                        'type' => 'radio',
                        'label' => 'Do you operate an electronic or physical marketplace for third-party sellers?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'electronicOrPhysicalMarketplace',
                    ],
                    'tx_ownership_in_similar_business' => [
                        'type' => 'radio',
                        'label' => 'Do you have substantial ownership in a similar business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'ownershipInSimilarBusiness',
                    ],
                    'tx_ownership_business_maintains_location' => [
                        'type' => 'radio',
                        'label' => 'Do you have ownership in a business that maintains a location in Texas to promote sales?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'ownershipInBusinessMaintainsLocation',
                    ],
                    'tx_ship_to_other_texas_cities' => [
                        'type' => 'radio',
                        'label' => 'Do you deliver or ship items to other cities or counties in Texas?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'deliverOrShipItemToOtherCitiesInTexas',
                    ],

                    // ───────── Banking & payment ─────────
                    'tx_bank_name' => [
                        'type' => 'text',
                        'label' => 'Bank Name',
                        'rules' => ['required', 'string', 'max:100'],
                        'source_name' => 'bankName',
                    ],
                    'tx_personal_bank' => [
                        'type' => 'radio',
                        'label' => 'Is this also your personal bank?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'personalBank',
                    ],
                    'tx_accept_credit_cards' => [
                        'type' => 'radio',
                        'label' => 'Do you accept credit card payments?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'creditCardPayments',
                    ],
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
                    'tx_alternate_contact' => [
                        'type' => 'radio',
                        'label' => 'Add an alternate contact?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'brAlternateContact',
                    ],
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

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'tx_driver_license_state' => [
                                'type' => 'select',
                                'label' => 'Texas Driver License Issuing State',
                                'options' => array_combine(
                                    array_keys(config('states')),
                                    array_values(config('states'))
                                ),
                                'rules' => ['required', 'size:2'],
                                'source_name' => 'primaryContactDriverLicenceState',
                            ],
                            'tx_driver_license' => [
                                'type' => 'text',
                                'label' => 'Driver License Number (Texas requires this for each responsible person)',
                                'rules' => ['required', 'string', 'max:20'],
                                'sensitive' => true,
                                'source_name' => 'primaryContactDriverLicenceNumber',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

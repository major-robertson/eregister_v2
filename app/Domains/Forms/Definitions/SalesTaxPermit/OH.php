<?php

/**
 * Ohio — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/ohio/application/`
 * (primary, organizationInformation, locationInformation, businessInformation
 * including beer/liquor section) plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── OH-specific identifiers ─────────
                    'oh_charter_certification_number' => [
                        'type' => 'text',
                        'label' => 'Ohio Charter Certification Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'help' => 'Required for OH-incorporated entities.',
                        'source_name' => 'ohioCharterCertificationNumber',
                    ],
                    'oh_secondary_phone' => [
                        'type' => 'text',
                        'label' => 'Business Secondary Phone',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'businessSecondaryPhoneNumber',
                    ],
                    'oh_business_fax_number' => [
                        'type' => 'text',
                        'label' => 'Business Fax Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'businessFaxNumber',
                    ],
                    'oh_date_started_taxable_sales_at_location' => [
                        'type' => 'date',
                        'label' => 'Date Started Taxable Sales at This Location',
                        'rules' => ['required', 'date'],
                        'source_name' => 'dateStartedTaxableSalesAtThisLocation',
                    ],

                    // ───────── Liquor / beer ─────────
                    'oh_selling_beer_or_liquor' => [
                        'type' => 'radio',
                        'label' => 'Will you sell beer or liquor?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'sellingBeerOrLiquor',
                    ],
                    'oh_liquor_permit_number' => [
                        'type' => 'text',
                        'label' => 'Ohio Liquor Permit Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'oh_selling_beer_or_liquor'], '1']],
                        'source_name' => 'liquorPermitNumber',
                    ],
                    'oh_non_liquor_sales_before_permit' => [
                        'type' => 'radio',
                        'label' => 'Will you have non-liquor sales before the permit is issued?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'oh_selling_beer_or_liquor'], '1']],
                        'source_name' => 'nonLiquorSalesBeforeIssuedPermit',
                    ],

                    // ───────── Previous owner (Vendor License) ─────────
                    'oh_previous_owner_name' => [
                        'type' => 'text',
                        'label' => 'Previous Owner Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'source_name' => 'previousOwnerName',
                    ],
                    'oh_previous_owner_vendor_license_number' => [
                        'type' => 'text',
                        'label' => 'Previous Owner OH Vendor License Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'source_name' => 'previousOwnerVendorLicenseNumber',
                    ],

                    // ───────── Banking ─────────
                    'oh_routing_number' => [
                        'type' => 'text',
                        'label' => 'Bank Routing Number',
                        'rules' => ['nullable', 'digits:9'],
                        'sensitive' => true,
                        'source_name' => 'routingNumber',
                    ],
                    'oh_checking_number' => [
                        'type' => 'text',
                        'label' => 'Bank Account Number',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'sensitive' => true,
                        'source_name' => 'checkingNumber',
                    ],

                    // ───────── Company contact (separate from primary) ─────────
                    'oh_company_contact_first_name' => [
                        'type' => 'text', 'label' => 'Company Contact First Name (for tax returns)',
                        'rules' => ['required', 'string', 'max:60'],
                        'source_name' => 'companyContactFirstName',
                    ],
                    'oh_company_contact_middle_initial' => [
                        'type' => 'text', 'label' => 'Company Contact Middle Initial',
                        'rules' => ['nullable', 'string', 'max:1'],
                        'source_name' => 'companyContactMiddleInitial',
                    ],
                    'oh_company_contact_last_name' => [
                        'type' => 'text', 'label' => 'Company Contact Last Name',
                        'rules' => ['required', 'string', 'max:60'],
                        'source_name' => 'companyContactLastName',
                    ],
                    'oh_company_contact_title' => [
                        'type' => 'text', 'label' => 'Company Contact Title',
                        'rules' => ['required', 'string', 'max:60'],
                        'source_name' => 'companyContactTitle',
                    ],
                    'oh_company_contact_email' => [
                        'type' => 'email', 'label' => 'Company Contact Email',
                        'rules' => ['required', 'email', 'max:255'],
                        'placeholder' => 'name@example.com',
                        'source_name' => 'companyContactEmail',
                    ],
                    'oh_company_contact_phone' => [
                        'type' => 'text', 'label' => 'Company Contact Phone',
                        'rules' => ['required', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'companyContactPhone',
                    ],
                    'oh_company_contact_fax' => [
                        'type' => 'text', 'label' => 'Company Contact Fax',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'companyContactFax',
                    ],
                    'oh_company_contact_ssn' => [
                        'type' => 'text', 'label' => 'Company Contact SSN',
                        'rules' => ['required', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                        'sensitive' => true,
                        'source_name' => 'companyContactSsn',
                    ],
                    'oh_company_contact_address' => [
                        'type' => 'address',
                        'label' => 'Company Contact Address',
                        'rules' => ['required'],
                    ],
                ],
            ],
        ],

        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'oh_middle_initial' => [
                                'type' => 'text',
                                'label' => 'Middle Initial (Ohio)',
                                'rules' => ['nullable', 'string', 'max:1'],
                                'source_name' => 'primaryContactMiddleInitial',
                            ],
                            'oh_fax_number' => [
                                'type' => 'text',
                                'label' => 'Fax Number (Ohio)',
                                'rules' => ['nullable', 'string', 'max:20'],
                                'source_name' => 'primaryContactFaxNumber',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

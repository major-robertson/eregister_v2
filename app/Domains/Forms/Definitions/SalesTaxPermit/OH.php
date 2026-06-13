<?php

/**
 * Ohio — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/ohio/application/`.
 *
 * Collapsed into core: secondary phone + fax (core contacts), first
 * taxable sales date (matrix_first_sales_date), beer/liquor gate
 * (applies_alcohol), previous owner name (core predecessor), bank
 * routing/account (core bank_*), business/mailing county (locations[]).
 *
 * §3A.2 fix applied: the invented oh_company_contact_ssn was DELETED —
 * legacy Ohio never asks the company contact's SSN.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Ohio Sales Tax Permit Details',
            'description' => 'Ohio-specific identifiers, liquor, and contact questions.',
            'groups' => [
                ['title' => 'Ohio Identifiers', 'fields' => ['oh_charter_certification_number']],
                ['title' => 'Liquor / Beer', 'fields' => [
                    'oh_liquor_permit_number', 'oh_non_liquor_sales_before_permit',
                ]],
                ['title' => 'Previous Owner (Vendor License)', 'fields' => [
                    'oh_previous_owner_vendor_license_number',
                ]],
                ['title' => 'Company Contact for Tax Returns', 'fields' => [
                    ['oh_company_contact_first_name', 'oh_company_contact_middle_initial'],
                    ['oh_company_contact_last_name', 'oh_company_contact_title'],
                    ['oh_company_contact_email', 'oh_company_contact_phone'],
                    'oh_company_contact_fax', 'oh_company_contact_address',
                ]],
            ],
            'fields' => [
                'oh_charter_certification_number' => [
                    'type' => 'text',
                    'label' => 'Ohio Charter / Certification Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'help' => 'Required for OH-incorporated entities.',
                    'source_name' => 'ohioCharterCertificationNumber',
                ],
                'oh_liquor_permit_number' => [
                    'type' => 'text',
                    'label' => 'Ohio Liquor Permit Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'OH']],
                    'source_name' => 'liquorPermitNumber',
                ],
                'oh_non_liquor_sales_before_permit' => nullableYesNoField('Will you make non-liquor sales before the permit is issued?', 'nonLiquorSalesBeforeIssuedPermit', [
                    'when' => ['contains' => [['var' => '$root.applies_alcohol.states'], 'OH']],
                ]),
                'oh_previous_owner_vendor_license_number' => [
                    'type' => 'text',
                    'label' => "Previous Owner's OH Vendor's License Number (optional)",
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['contains' => [['var' => '$root.applies_purchased_or_acquired_business.states'], 'OH']],
                    'source_name' => 'previousOwnerVendorLicenseNumber',
                ],

                'oh_company_contact_first_name' => [
                    'type' => 'text',
                    'label' => 'Company Contact First Name',
                    'rules' => ['required', 'string', 'max:60'],
                    'source_name' => 'companyContactFirstName',
                ],
                'oh_company_contact_middle_initial' => [
                    'type' => 'text',
                    'label' => 'Middle Initial',
                    'rules' => ['nullable', 'string', 'max:1'],
                    'source_name' => 'companyContactMiddleInitial',
                ],
                'oh_company_contact_last_name' => [
                    'type' => 'text',
                    'label' => 'Company Contact Last Name',
                    'rules' => ['required', 'string', 'max:60'],
                    'source_name' => 'companyContactLastName',
                ],
                'oh_company_contact_title' => [
                    'type' => 'text',
                    'label' => 'Company Contact Title',
                    'rules' => ['required', 'string', 'max:60'],
                    'source_name' => 'companyContactTitle',
                ],
                'oh_company_contact_email' => [
                    'type' => 'email',
                    'label' => 'Company Contact Email',
                    'rules' => ['required', 'email', 'max:255'],
                    'placeholder' => 'name@example.com',
                    'source_name' => 'companyContactEmail',
                ],
                'oh_company_contact_phone' => [
                    'type' => 'text',
                    'label' => 'Company Contact Phone',
                    'rules' => ['required', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
                    'source_name' => 'companyContactPhoneNumber',
                ],
                'oh_company_contact_fax' => [
                    'type' => 'text',
                    'label' => 'Company Contact Fax (optional)',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
                    'source_name' => 'companyContactFaxNumber',
                ],
                'oh_company_contact_address' => [
                    'type' => 'address',
                    'label' => 'Company Contact Address',
                    'rules' => ['required'],
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
                            'oh_phone_extension' => [
                                'type' => 'text',
                                'label' => 'Phone Extension (Ohio, optional)',
                                'rules' => ['nullable', 'string', 'max:10'],
                                'source_name' => 'primaryContactPhoneNumberExt',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

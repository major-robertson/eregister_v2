<?php

/**
 * Maryland — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/maryland/application/`
 * (primary, address, organizationInformation, generalQuestions, acquisitions)
 * plus matching JS validators.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    // ───────── MD-specific identifiers ─────────
                    'md_primary_id_type' => [
                        'type' => 'select',
                        'label' => 'Primary Contact ID Type',
                        'options' => ['ssn' => 'SSN', 'ein' => 'EIN'],
                        'rules' => ['required'],
                        'source_name' => 'primaryContactIdType',
                    ],
                    'md_business_taxpayer_id' => [
                        'type' => 'text',
                        'label' => 'Maryland Business Taxpayer ID (DA EIN)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'source_name' => 'businessTaxpayerId',
                    ],
                    'md_da_ein' => [
                        'type' => 'text',
                        'label' => 'DA EIN (if assigned)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'source_name' => 'DAEIN',
                    ],
                    'md_owner_name' => [
                        'type' => 'text',
                        'label' => 'Owner Legal Name (for Sole Proprietor)',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'source_name' => 'ownerName',
                    ],
                    'md_owner_ssn' => [
                        'type' => 'text',
                        'label' => 'Owner SSN',
                        'rules' => ['nullable', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'sensitive' => true,
                        'source_name' => 'ownerSSN',
                    ],
                    'md_business_fax_number' => [
                        'type' => 'text',
                        'label' => 'Business Fax Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'placeholder' => '(123) 456-7890',
                        'mask' => '(999) 999-9999',
                        'source_name' => 'businessFaxNumber',
                    ],
                    'md_llc_classified_as_corp' => [
                        'type' => 'radio',
                        'label' => 'Is the LLC classified as a corporation for federal tax purposes?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                        'source_name' => 'llcAsClassifiedAsCorp',
                    ],

                    // ───────── Reasons for applying / business overview ─────────
                    'md_reason_new_business' => ['type' => 'checkbox', 'label' => 'New Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => 'New Business'],
                    'md_reason_reorganization' => ['type' => 'checkbox', 'label' => 'Reorganization', 'source_name' => 'reasonsForApplying[]', 'source_value' => 'Reorganization'],
                    'md_reason_purchased' => ['type' => 'checkbox', 'label' => 'Purchased Existing Business', 'source_name' => 'reasonsForApplying[]', 'source_value' => 'Purchased'],
                    'md_reason_remote_seller' => ['type' => 'checkbox', 'label' => 'Remote Seller', 'source_name' => 'reasonsForApplying[]', 'source_value' => 'Remote Seller'],
                    'md_reason_other' => ['type' => 'checkbox', 'label' => 'Other', 'source_name' => 'reasonsForApplying[]', 'source_value' => 'Other'],

                    'md_type_of_business_overview' => [
                        'type' => 'select',
                        'label' => 'Business Overview Category',
                        'options' => [
                            'food_and_beverage' => 'Food and Beverage',
                            'retail' => 'Retail',
                            'service' => 'Service',
                            'manufacturing' => 'Manufacturing',
                            'construction' => 'Construction',
                            'wholesale' => 'Wholesale',
                            'professional' => 'Professional Services',
                            'misc' => 'Miscellaneous',
                        ],
                        'rules' => ['required'],
                        'drives_conditional' => true,
                        'source_name' => 'typeOfBusinessOverview',
                    ],
                    'md_type_of_business_detail' => [
                        'type' => 'text',
                        'label' => 'Business Detail (specific type within category)',
                        'rules' => ['required', 'string', 'max:120'],
                        'help' => 'Specific business type within the chosen overview category.',
                        'source_name' => 'typeOfBusinessDetail',
                    ],
                    'md_your_situation' => [
                        'type' => 'select',
                        'label' => 'Your Situation',
                        'options' => [
                            'starting_new' => 'I am starting a new business',
                            'opened_recently' => 'I recently opened my business',
                            'existing_no_changes' => 'I have an existing business and nothing has changed',
                            'existing_with_changes' => 'I have an existing business with changes (location, ownership)',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'yourSituration',
                    ],

                    // ───────── Operations ─────────
                    'md_multiple_locations' => [
                        'type' => 'radio',
                        'label' => 'Will you operate multiple locations in Maryland?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'multipleLocations',
                    ],
                    'md_primarily_provide_support' => [
                        'type' => 'radio',
                        'label' => 'Do you primarily provide support services?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'primarilyProvideSupport',
                    ],
                    'md_type_of_service_provided' => [
                        'type' => 'text',
                        'label' => 'Type of Service Provided',
                        'rules' => ['nullable', 'string', 'max:255'],
                        'when' => ['==' => [['var' => 'md_primarily_provide_support'], '1']],
                        'source_name' => 'typeOfServiceProvided',
                    ],

                    // ───────── Employer ─────────
                    'md_pay_wages_in_maryland' => [
                        'type' => 'radio',
                        'label' => 'Will you pay wages in Maryland?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'payWagesInMaryland',
                    ],
                    'md_first_date_paid_wages' => [
                        'type' => 'date',
                        'label' => 'First Date Wages Were Paid',
                        'rules' => ['nullable', 'date'],
                        'when' => ['==' => [['var' => 'md_pay_wages_in_maryland'], '1']],
                        'source_name' => 'firstDatePaidWages',
                    ],
                    'md_number_paid_wages' => [
                        'type' => 'text',
                        'label' => 'Number of MD Employees Paid Wages',
                        'rules' => ['nullable', 'integer', 'min:0'],
                        'when' => ['==' => [['var' => 'md_pay_wages_in_maryland'], '1']],
                        'source_name' => 'numberPaidWages',
                    ],
                    'md_sole_prop_employ_under_21' => [
                        'type' => 'radio',
                        'label' => '(Sole proprietor) Do you employ family members under 21?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'source_name' => 'solePropEmployUnder21',
                    ],
                    'md_partnership_employ_anyone' => [
                        'type' => 'radio',
                        'label' => '(Partnership) Do you employ anyone outside the partnership?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['general_partnership', 'limited_partnership', 'llp']]],
                        'source_name' => 'partnershipEmployAnyone',
                    ],
                    'md_llc_employ_other_members' => [
                        'type' => 'radio',
                        'label' => '(LLC) Does the LLC employ members other than owners?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                        'source_name' => 'llcEmployOtherMembers',
                    ],

                    // ───────── Acquisitions ─────────
                    'md_acquired_business' => [
                        'type' => 'radio',
                        'label' => 'Did you acquire an existing business in Maryland?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'acquireBusiness',
                    ],
                    'md_previous_employer_name' => [
                        'type' => 'text',
                        'label' => 'Previous Employer Name',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                        'source_name' => 'previousEmployerName',
                    ],
                    'md_previous_owner_address' => [
                        'type' => 'address',
                        'label' => 'Previous Owner Business Address',
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                    ],
                    'md_acquired_date' => [
                        'type' => 'date',
                        'label' => 'Acquisition Date',
                        'rules' => ['nullable', 'date', 'before_or_equal:today'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                        'source_name' => 'acquiredDate',
                    ],
                    'md_common_ownership_management' => [
                        'type' => 'radio',
                        'label' => 'Is there common ownership/management with the previous business?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                        'source_name' => 'commonOwnershipManagement',
                    ],
                    'md_percent_acquired' => [
                        'type' => 'percent',
                        'label' => 'Percent of Business Acquired',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                        'source_name' => 'percentAcquired',
                    ],
                    'md_prior_unemployment_insurance_number' => [
                        'type' => 'text',
                        'label' => 'Prior Maryland Unemployment Insurance Number (optional)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => ['==' => [['var' => 'md_acquired_business'], '1']],
                        'source_name' => 'priorUnemploymentInsuranceNumber',
                    ],
                ],
            ],
        ],
    ],
];

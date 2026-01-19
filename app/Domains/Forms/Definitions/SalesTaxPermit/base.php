<?php

return [
    'key' => 'sales_tax_permit',
    'version' => 1,

    'core_steps' => [
        'business' => [
            'title' => 'Business Information',
            'description' => 'Tell us about your business.',
            'fields' => [
                'legal_name' => [
                    'type' => 'text',
                    'label' => 'Legal Business Name',
                    'rules' => ['required', 'string', 'max:120'],
                    'persist_to_business' => true,
                ],
                'dba_name' => [
                    'type' => 'text',
                    'label' => 'DBA / Trade Name (if different)',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'persist_to_business' => true,
                ],
                'entity_type' => [
                    'type' => 'select',
                    'label' => 'Entity Type',
                    'drives_conditional' => true,
                    'options' => [
                        'llc' => 'LLC',
                        'corp' => 'Corporation',
                        'sole_prop' => 'Sole Proprietor',
                        'partnership' => 'Partnership',
                    ],
                    'rules' => ['required'],
                    'persist_to_business' => true,
                ],
                'business_address' => [
                    'type' => 'address',
                    'label' => 'Principal Business Address',
                    'rules' => ['required'],
                    'persist_to_business' => true,
                ],
                'mailing_address_same' => [
                    'type' => 'checkbox',
                    'label' => 'Mailing address is the same as business address',
                    'drives_conditional' => true,
                ],
                'mailing_address' => [
                    'type' => 'address',
                    'label' => 'Mailing Address',
                    'rules' => ['required'],
                    'when' => ['==' => [['var' => 'mailing_address_same'], false]],
                    'persist_to_business' => true,
                ],
            ],
        ],
        'responsible_people' => [
            'title' => 'Responsible People',
            'description' => 'Add all owners, officers, or partners with ownership in the business.',
            'fields' => [
                'responsible_people' => [
                    'type' => 'repeater',
                    'label' => 'Responsible People',
                    'min' => 1,
                    'item_label' => 'Person',
                    'persist_to_business' => true, // Non-sensitive fields only
                    'schema' => [
                        'full_name' => [
                            'type' => 'text',
                            'label' => 'Full Name',
                            'rules' => ['required', 'string', 'max:120'],
                            'persist_to_business' => true,
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => 'Title/Position',
                            'rules' => ['required', 'string', 'max:60'],
                            'persist_to_business' => true,
                        ],
                        'ssn_last4' => [
                            'type' => 'text',
                            'label' => 'SSN (Last 4 digits)',
                            'rules' => ['required', 'digits:4'],
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        'ownership_percent' => [
                            'type' => 'percent',
                            'label' => 'Ownership %',
                            'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                            'persist_to_business' => true,
                        ],
                        'is_authorized_signer' => [
                            'type' => 'checkbox',
                            'label' => 'Authorized to sign on behalf of the business',
                        ],
                    ],
                    'rules' => ['required', 'array', 'min:1'],
                ],
            ],
            'cross_validations' => [
                [
                    'rule' => 'ownership_totals_100',
                    'field' => 'responsible_people',
                    'phase' => 'core',
                ],
            ],
        ],
    ],

    'state_steps' => [
        'state_details' => [
            'title' => '{state_name} - Permit Details',
            'description' => 'Provide details specific to {state_name}.',
            'fields' => [
                'estimated_monthly_sales' => [
                    'type' => 'text',
                    'label' => 'Estimated Monthly Taxable Sales in {state_name}',
                    'rules' => ['required', 'numeric', 'min:0'],
                    'help' => 'Enter your best estimate of monthly taxable sales.',
                ],
                'start_date' => [
                    'type' => 'date',
                    'label' => 'Date You Will Start Selling in {state_name}',
                    'rules' => ['required', 'date'],
                ],
                'business_description' => [
                    'type' => 'text',
                    'label' => 'Describe your products/services sold in {state_name}',
                    'rules' => ['required', 'string', 'max:500'],
                ],
            ],
        ],
        'state_responsible_people' => [
            'title' => '{state_name} - Additional Person Info',
            'description' => 'Provide any additional information required by {state_name} for each responsible person.',
            'fields' => [
                'responsible_people_extra' => [
                    'type' => 'person_state_extra',
                    'label' => '{state_name} Requirements Per Person',
                    'source' => '$core.responsible_people',
                    'schema' => [],
                ],
            ],
        ],
    ],

    'available_states' => array_keys(config('states')),
];

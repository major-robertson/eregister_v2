<?php

/**
 * Sales Tax Permit — base definition (shared across all selectable states).
 *
 * Authored from a one-time port of the legacy TaxResaleCertificate Laravel app.
 * Questions promoted to this file are asked by most states; state-specific
 * additions live in {STATE_CODE}.php files which `extends` this base.
 *
 * @see app/Domains/Forms/Engine/FormRegistry.php for merge semantics
 */
return [
    'key' => 'sales_tax_permit',

    /*
     * Bumped from v1 -> v2 with the TaxResaleCertificate import:
     * - Added FEIN / individual_ssn (conditional on entity type)
     * - Added NAICS, business_phone/email, business_description,
     *   reason_for_applying, business_start_date, formation_state
     * - Expanded entity_type options (LP / LLP / LLC variants / S-corp / etc.)
     * - Split responsible_people.full_name into first_name + last_name
     *   (display logic in repeater.blade.php, person-state-extra.blade.php,
     *   and multi-state-form-runner.blade.php was updated to combine the two)
     * - Replaced ssn_last4 with full ssn (sensitive)
     * - Added per-person dob, phone, email, home_address,
     *   driver_license_state, driver_license_number
     * - Renamed state_steps generic `start_date` to `sales_tax_start_date`
     *   and removed per-state `business_description` (now business-wide)
     * - Added shared state-level questions: home_based_business,
     *   internet_sales/website, purchase_existing_business + previous-owner block
     * Existing applications continue to work via definition_snapshot.
     */
    'version' => 2,

    'core_steps' => [
        'business' => [
            'title' => 'Business Information',
            'description' => 'Tell us about your business. This information is used across every state you selected.',
            'groups' => [
                ['title' => 'Legal Identity', 'fields' => ['legal_name', 'dba_name', 'entity_type']],
                ['title' => 'Federal Tax Identifiers', 'fields' => ['fein', 'individual_ssn']],
                ['title' => 'Business Activity', 'fields' => ['naics_code', 'business_description', 'reason_for_applying']],
                ['title' => 'Business Contact', 'fields' => ['business_phone', 'business_email']],
                ['title' => 'Formation', 'fields' => ['formation_state', 'business_start_date']],
                ['title' => 'Business Address', 'fields' => ['business_address', 'mailing_address_same', 'mailing_address']],
            ],
            'fields' => [
                'legal_name' => [
                    'type' => 'text',
                    'label' => 'Legal Business Name',
                    'rules' => ['required', 'string', 'max:120'],
                    'help' => 'The exact name on file with the IRS or your state of formation.',
                    'persist_to_business' => true,
                ],
                'dba_name' => [
                    'type' => 'text',
                    'label' => 'DBA / Trade Name (if different from legal name)',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'persist_to_business' => true,
                ],
                'entity_type' => [
                    'type' => 'select',
                    'label' => 'Type of Entity',
                    'drives_conditional' => true,
                    'options' => [
                        'sole_prop' => 'Sole Proprietor',
                        'general_partnership' => 'General Partnership',
                        'limited_partnership' => 'Limited Partnership (LP)',
                        'llp' => 'Limited Liability Partnership (LLP)',
                        'llc_single' => 'LLC (Single-Member)',
                        'llc_multi' => 'LLC (Multi-Member)',
                        'c_corp' => 'C Corporation',
                        's_corp' => 'S Corporation',
                        'nonprofit' => 'Non-Profit Corporation',
                        'trust' => 'Trust',
                        'estate' => 'Estate',
                        'government' => 'Government Agency',
                        'other' => 'Other',
                    ],
                    'rules' => ['required'],
                    'persist_to_business' => true,
                ],
                'fein' => [
                    'type' => 'text',
                    'label' => 'Federal Employer Identification Number (FEIN/EIN)',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'help' => 'Format: 12-3456789. Required for entities other than sole proprietors. Apply at https://www.irs.gov/businesses/employer-identification-number',
                    'when' => ['!=' => [['var' => 'entity_type'], 'sole_prop']],
                    'sensitive' => true,
                    'persist_to_business' => true,
                ],
                'individual_ssn' => [
                    'type' => 'text',
                    'label' => 'Owner Social Security Number',
                    'rules' => ['nullable', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                    'help' => 'Sole proprietors only. Format: 123-45-6789. Encrypted at rest.',
                    'when' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'sensitive' => true,
                ],
                'naics_code' => [
                    'type' => 'text',
                    'label' => 'NAICS Code',
                    'rules' => ['required', 'digits:6'],
                    'help' => '6-digit North American Industry Classification System code. Look it up at https://www.census.gov/naics/',
                    'persist_to_business' => true,
                ],
                'business_description' => [
                    'type' => 'text',
                    'label' => 'Description of Business / Principal Products or Services',
                    'rules' => ['required', 'string', 'max:500'],
                    'help' => 'Briefly describe what your business sells or does. Used by every state.',
                    'persist_to_business' => true,
                ],
                'reason_for_applying' => [
                    'type' => 'select',
                    'label' => 'Reason for Applying',
                    'options' => [
                        'new_business' => 'Starting a new business',
                        'adding_location' => 'Adding a new location',
                        'change_in_organization' => 'Change in organization (entity type, ownership, etc.)',
                        'restarting_prior_business' => 'Restarting a prior business',
                        'purchased_existing' => 'Purchased an existing business',
                        'remote_seller' => 'Remote seller / economic nexus',
                        'other' => 'Other',
                    ],
                    'rules' => ['required'],
                ],
                'business_phone' => [
                    'type' => 'text',
                    'label' => 'Business Phone Number',
                    'rules' => ['required', 'string', 'max:20'],
                    'placeholder' => '123-456-7890',
                    'persist_to_business' => true,
                ],
                'business_email' => [
                    'type' => 'email',
                    'label' => 'Business Email Address',
                    'rules' => ['required', 'email', 'max:255'],
                    'persist_to_business' => true,
                ],
                'formation_state' => [
                    'type' => 'select',
                    'label' => 'State of Formation / Registration',
                    'options' => array_combine(
                        array_keys(config('states')),
                        array_values(config('states'))
                    ),
                    'rules' => ['required', 'size:2'],
                    'help' => 'The state where your business was legally formed or registered.',
                    'persist_to_business' => true,
                ],
                'business_start_date' => [
                    'type' => 'date',
                    'label' => 'Date Business Began Operating',
                    'rules' => ['required', 'date', 'before_or_equal:today'],
                    'help' => 'The date your business legally began operating (not when sales tax collection begins — that is asked per state).',
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
                    // Only show / require when the user explicitly toggles the
                    // "different mailing address" switch on (sets value to '0').
                    // Default behavior (null/unset) means mailing == business.
                    'when' => ['==' => [['var' => 'mailing_address_same'], '0']],
                    'persist_to_business' => true,
                ],
            ],
        ],
        'responsible_people' => [
            'title' => 'Responsible People',
            'description' => 'Add every owner, officer, partner, or other person responsible for the business. States generally require this information for tax compliance and to assign personal liability.',
            'fields' => [
                'responsible_people' => [
                    'type' => 'repeater',
                    'label' => 'Responsible People',
                    'min' => 1,
                    'conditional_min' => [
                        'field' => 'entity_type',
                        'values' => [
                            'general_partnership' => 2,
                            'limited_partnership' => 2,
                            'llp' => 2,
                            'llc_multi' => 2,
                        ],
                    ],
                    'item_label' => 'Responsible Person',
                    'persist_to_business' => true, // Non-sensitive subfields only
                    'schema' => [
                        // Split first/last so state PDFs that need separate fields
                        // can read them directly. Repeater list and review screens
                        // combine these via Blade for display.
                        'first_name' => [
                            'type' => 'text',
                            'label' => 'First Name',
                            'rules' => ['required', 'string', 'max:60'],
                            'persist_to_business' => true,
                        ],
                        'last_name' => [
                            'type' => 'text',
                            'label' => 'Last Name',
                            'rules' => ['required', 'string', 'max:60'],
                            'persist_to_business' => true,
                        ],
                        'title' => [
                            'type' => 'text',
                            'label' => 'Title / Position',
                            'rules' => ['required', 'string', 'max:60'],
                            'placeholder' => 'e.g. Owner, President, Member, Partner, Officer',
                            'persist_to_business' => true,
                        ],
                        'phone' => [
                            'type' => 'text',
                            'label' => 'Phone Number',
                            'rules' => ['required', 'string', 'max:20'],
                            'placeholder' => '123-456-7890',
                            'persist_to_business' => true,
                        ],
                        'email' => [
                            'type' => 'email',
                            'label' => 'Email Address',
                            'rules' => ['required', 'email', 'max:255'],
                            'persist_to_business' => true,
                        ],
                        'dob' => [
                            'type' => 'date',
                            'label' => 'Date of Birth',
                            'rules' => ['required', 'date', 'before:today'],
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        'ssn' => [
                            'type' => 'text',
                            'label' => 'Social Security Number',
                            'rules' => ['required', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                            'placeholder' => '123-45-6789',
                            'help' => 'Encrypted at rest.',
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        'driver_license_state' => [
                            'type' => 'select',
                            'label' => 'Driver License State',
                            'options' => array_combine(
                                array_keys(config('states')),
                                array_values(config('states'))
                            ),
                            'rules' => ['nullable', 'size:2'],
                            'persist_to_business' => true,
                        ],
                        'driver_license_number' => [
                            'type' => 'text',
                            'label' => 'Driver License Number',
                            'rules' => ['nullable', 'string', 'max:30'],
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        'home_address' => [
                            'type' => 'address',
                            'label' => 'Home Address',
                            'rules' => ['required'],
                            'persist_to_business' => true,
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
            'title' => '{state_name} Sales Tax Permit Details',
            'description' => 'Provide details specific to {state_name}.',
            'groups' => [
                ['title' => 'Sales Activity', 'fields' => ['sales_tax_start_date', 'estimated_monthly_sales']],
                ['title' => 'Operations', 'fields' => ['home_based_business', 'internet_sales', 'website_address']],
                ['title' => 'Acquisition', 'fields' => ['purchase_existing_business', 'previous_owner_name', 'previous_owner_fein', 'previous_owner_purchase_date', 'previous_owner_address']],
            ],
            'fields' => [
                'sales_tax_start_date' => [
                    'type' => 'date',
                    'label' => 'Date You Will Start Collecting Sales Tax in {state_name}',
                    'rules' => ['required', 'date'],
                    'help' => 'When your obligation to collect {state_name} sales tax begins. May be retroactive if you have past nexus.',
                ],
                'estimated_monthly_sales' => [
                    'type' => 'text',
                    'label' => 'Estimated Monthly Taxable Sales in {state_name} (USD)',
                    'rules' => ['required', 'numeric', 'min:0'],
                    'help' => 'Your best estimate of monthly taxable sales in this state.',
                ],
                'home_based_business' => [
                    'type' => 'radio',
                    'label' => 'Is the business operated from a residence in {state_name}?',
                    'options' => ['1' => 'Yes', '0' => 'No'],
                    'rules' => ['required', 'in:0,1'],
                ],
                'internet_sales' => [
                    'type' => 'radio',
                    'label' => 'Do you make sales over the internet?',
                    'options' => ['1' => 'Yes', '0' => 'No'],
                    'rules' => ['required', 'in:0,1'],
                    'drives_conditional' => true,
                ],
                'website_address' => [
                    'type' => 'text',
                    'label' => 'Website Address',
                    'rules' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'https://example.com',
                    'when' => ['==' => [['var' => 'internet_sales'], '1']],
                ],
                'purchase_existing_business' => [
                    'type' => 'radio',
                    'label' => 'Did you purchase an existing business in {state_name}?',
                    'options' => ['1' => 'Yes', '0' => 'No'],
                    'rules' => ['required', 'in:0,1'],
                    'drives_conditional' => true,
                ],
                'previous_owner_name' => [
                    'type' => 'text',
                    'label' => 'Previous Owner Legal Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'purchase_existing_business'], '1']],
                ],
                'previous_owner_fein' => [
                    'type' => 'text',
                    'label' => 'Previous Owner FEIN',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'placeholder' => '12-3456789',
                    'when' => ['==' => [['var' => 'purchase_existing_business'], '1']],
                    'sensitive' => true,
                ],
                'previous_owner_purchase_date' => [
                    'type' => 'date',
                    'label' => 'Date Business Was Acquired',
                    'rules' => ['nullable', 'date', 'before_or_equal:today'],
                    'when' => ['==' => [['var' => 'purchase_existing_business'], '1']],
                ],
                'previous_owner_address' => [
                    'type' => 'address',
                    'label' => 'Previous Owner Business Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'purchase_existing_business'], '1']],
                ],
            ],
        ],
        'state_responsible_people' => [
            'title' => '{state_name} Per-Person Requirements',
            'description' => 'Provide additional information required by {state_name} for each responsible person.',
            'fields' => [
                'responsible_people_extra' => [
                    'type' => 'person_state_extra',
                    'label' => '{state_name} Requirements Per Person',
                    'source' => '$core.responsible_people',
                    'schema' => [
                        // Empty by default. State files append per-person fields here
                        // (e.g. TX driver license #, CA driver license + expiration).
                        // Per the engine, these render inline in the core
                        // responsible_people repeater modal at runtime.
                    ],
                ],
            ],
        ],
    ],

    'excluded_states' => [
        'DE' => 'No general state sales tax registration required',
        'MT' => 'No general state sales tax registration required',
        'NH' => 'No general state sales tax registration required',
        'OR' => 'No general state sales tax registration required',
    ],

    'available_states' => array_diff(array_keys(config('states')), ['DE', 'MT', 'NH', 'OR']),
];

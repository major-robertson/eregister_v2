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
        'identity' => [
            'title' => 'Business Identity',
            'description' => "Tell us who you legally are. We'll use this on every state filing.",
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
                        'Individual / Sole Owner' => [
                            'sole_prop' => 'Sole Proprietor',
                        ],
                        'LLCs' => [
                            'llc_single' => 'LLC (Single-Member)',
                            'llc_multi' => 'LLC (Multi-Member)',
                        ],
                        'Corporations' => [
                            'corporation' => 'Corporation',
                            's_corp' => 'S Corporation',
                            'nonprofit' => 'Non-Profit Corporation',
                        ],
                        'Partnerships' => [
                            'general_partnership' => 'General Partnership',
                            'limited_partnership' => 'Limited Partnership (LP)',
                            'llp' => 'Limited Liability Partnership (LLP)',
                        ],
                        'Other Organization Types' => [
                            'trust' => 'Trust',
                            'estate' => 'Estate',
                            'government' => 'Government Agency',
                            'other' => 'Other',
                        ],
                    ],
                    'rules' => ['required'],
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
            ],
        ],
        'contact_and_address' => [
            'title' => 'Contact & Address',
            'description' => 'Where the business operates and how states can reach you.',
            'groups' => [
                // Email is intentionally first inside the Contact card so
                // returning users immediately see the prefill from their
                // signed-in user account — a "this form knows me" trust
                // moment before they touch a field.
                ['title' => 'Contact', 'fields' => ['business_email', 'business_phone']],
                ['title' => 'Address', 'fields' => ['business_address', 'mailing_address_same', 'mailing_address']],
            ],
            'fields' => [
                'business_email' => [
                    'type' => 'email',
                    'label' => 'Business Email Address',
                    'rules' => ['required', 'email', 'max:255'],
                    'placeholder' => 'you@example.com',
                    'persist_to_business' => true,
                ],
                'business_phone' => [
                    'type' => 'text',
                    'label' => 'Business Phone Number',
                    'rules' => ['required', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
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
        'activity' => [
            'title' => 'Business Activity',
            'description' => "What you do, why you're registering, and when you started operating.",
            'fields' => [
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
                'business_start_date' => [
                    'type' => 'date',
                    'label' => 'Date Business Began Operating',
                    'rules' => ['required', 'date', 'before_or_equal:today'],
                    'help' => 'The date your business legally began operating (not when sales tax collection begins — we will ask that later).',
                    'persist_to_business' => true,
                ],
                'business_description' => [
                    'type' => 'text',
                    'label' => 'Description of Business / Principal Products or Services',
                    'rules' => ['required', 'string', 'max:500'],
                    'placeholder' => 'Briefly describe what your business sells or does',
                    'persist_to_business' => true,
                ],
                'naics_code' => [
                    'type' => 'text',
                    'label' => 'NAICS Code',
                    'rules' => ['required', 'digits:6'],
                    'help' => 'Find your code here: https://www.census.gov/naics/',
                    'placeholder' => '123456',
                    'mask' => '999999',
                    'persist_to_business' => true,
                ],
            ],
        ],
        'tax_identification' => [
            'title' => 'Tax Identification',
            'description' => "Federal IDs we'll share with state revenue departments. Your data is encrypted at rest.",
            'fields' => [
                // SSN is rendered first because, for sole proprietors,
                // it's the actually-required field of the two — EIN is
                // optional. Non-sole-prop entities don't see SSN at all
                // (the when clause hides it), so this ordering also
                // surfaces EIN as the sole visible field for them.
                'individual_ssn' => [
                    'type' => 'text',
                    'label' => 'Owner Social Security Number',
                    'rules' => ['required', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                    'help' => "Required by every state's revenue department for tax filing.",
                    'placeholder' => '123-45-6789',
                    'mask' => '999-99-9999',
                    'when' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                    'sensitive' => true,
                ],
                'fein' => [
                    'type' => 'text',
                    'label' => 'Federal Employer Identification Number (FEIN/EIN)',
                    // EIN is required for every entity type EXCEPT sole
                    // proprietors, who may optionally have one. The
                    // `{prefix}` token resolves to `coreData.` for per-step
                    // Livewire validation and to `` (empty) for the final
                    // submit-time validation, so `entity_type` is found in
                    // both contexts. `nullable` lets sole props leave the
                    // field blank without tripping the regex check.
                    'rules' => [
                        'nullable',
                        'regex:/^\d{2}-?\d{7}$/',
                        'required_unless:{prefix}entity_type,sole_prop',
                    ],
                    'help' => 'Get an EIN at https://www.irs.gov/businesses/employer-identification-number',
                    // Sole proprietors get a longer note explaining the
                    // optional-with-recommendation framing, since EIN is
                    // an unusual ask for them. Every other entity type
                    // sees the short default — they already know they
                    // need one and don't need a wall of text.
                    'help_when' => [
                        [
                            'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                            'help' => 'You may leave blank, but having an EIN is highly recommended. Get an EIN at https://www.irs.gov/businesses/employer-identification-number',
                        ],
                    ],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
                    // Show an "Optional" badge next to the label only when
                    // the user picked Sole Proprietor. Default state (and
                    // every other entity type) shows nothing — required-ness
                    // is conveyed by the help text + validation, matching
                    // how the rest of the form treats required fields.
                    'badge_when' => [
                        [
                            'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                            'label' => 'Optional',
                            'color' => 'zinc',
                        ],
                    ],
                    'sensitive' => true,
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
                    // Visual sections for the repeater modal so the 13+
                    // base fields don't render as one wall. The
                    // first/last name pair sits side-by-side via the
                    // nested-array row syntax. State-specific fields
                    // (if any) still render as separate sections after.
                    'schema_groups' => [
                        ['title' => 'Identity', 'fields' => [['first_name', 'last_name'], 'title']],
                        ['title' => 'Contact', 'fields' => ['phone', 'email']],
                        ['title' => 'Personal', 'fields' => ['dob', 'ssn']],
                        ['title' => 'Driver License', 'fields' => ['driver_license_state', 'driver_license_number', 'driver_license_expiration']],
                        ['title' => 'Home Address', 'fields' => ['home_address']],
                        ['title' => 'Authorization', 'fields' => ['ownership_percent', 'is_authorized_signer']],
                    ],
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
                            'placeholder' => '(123) 456-7890',
                            'mask' => '(999) 999-9999',
                            'persist_to_business' => true,
                        ],
                        'email' => [
                            'type' => 'email',
                            'label' => 'Email Address',
                            'rules' => ['required', 'email', 'max:255'],
                            'placeholder' => 'name@example.com',
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
                            'help' => 'Required by tax authorities. Encrypted at rest.',
                            'mask' => '999-99-9999',
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        // Full DL block is required for every responsible
                        // person — covers what CA/TX previously asked for
                        // per-state and ensures non-CA/TX state filings
                        // also have complete DL data when they need it.
                        'driver_license_state' => [
                            'type' => 'select',
                            'label' => 'Driver License State',
                            'options' => array_combine(
                                array_keys(config('states')),
                                array_values(config('states'))
                            ),
                            'rules' => ['required', 'size:2'],
                            'persist_to_business' => true,
                        ],
                        'driver_license_number' => [
                            'type' => 'text',
                            'label' => 'Driver License Number',
                            'rules' => ['required', 'string', 'max:30'],
                            'sensitive' => true,
                            'persist_to_business' => false,
                        ],
                        'driver_license_expiration' => [
                            'type' => 'date',
                            'label' => 'Driver License Expiration',
                            // after:today matches CA's old per-state rule
                            // and surfaces the same "license must not be
                            // expired" check for everyone.
                            'rules' => ['required', 'date', 'after:today'],
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
                    'rules' => ['required', 'integer', 'min:0'],
                    'help' => 'Your best estimate of monthly taxable sales in this state. Whole dollars only.',
                    'placeholder' => '10000',
                    // Mask to digits only; the 13-digit pattern is a max
                    // length cap (well above any realistic monthly figure)
                    // and rejects decimals so we get whole dollars in line
                    // with the integer validation rule.
                    'mask' => '9999999999999',
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
                    'mask' => '99-9999999',
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

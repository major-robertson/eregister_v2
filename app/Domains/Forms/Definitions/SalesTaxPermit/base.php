<?php

/**
 * Sales Tax Permit — base definition (shared across all selectable states).
 *
 * v3 — clean rebuild. A user applying in multiple states answers each
 * real-world question exactly once:
 *
 *   - Plain core fields (no prefix)  — business / entity / contact /
 *     bank / processor / predecessor facts asked once.
 *   - locations[] / temporary_events[] repeaters — canonical multi-
 *     location model; per-state location counts are derived, not asked.
 *   - matrix_* fields  — per-state single-cell values (dates, counts,
 *     dollar amounts), one row per applicable∩selected state.
 *   - applies_* fields — yes/no activities with a selected-state
 *     checklist (anywhere_states type). State-only follow-ups gate on
 *     `applies_x.states contains $state.code`.
 *
 * Every matrix and applies field declares `applicable_states` (§1.5
 * applicability rule): '*' = every selected state; an explicit list
 * renders/validates only when intersecting the selection. Question
 * text, options, and conditional chains are ported from the legacy
 * TaxResaleCertificate app, which is authoritative.
 *
 * @see app/Domains/Forms/Engine/FormRegistry.php for merge semantics
 * @see app/Domains/Forms/Engine/Applicability.php for the §1.5 rule
 */
$months = [
    '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
    '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
    '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
];

$stateOptions = array_combine(
    array_keys(config('states')),
    array_values(config('states'))
);

return [
    'key' => 'sales_tax_permit',

    /*
     * v2 -> v3: clean rebuild. No per-state duplicated questions remain;
     * the generic state_steps.state_details questions moved into core
     * matrix, applies, and plain fields. State files now hold ONLY
     * genuinely state-specific questions. No backward compatibility —
     * there are no production users for this form family.
     */
    'version' => 3,

    'core_steps' => [
        /*
        |------------------------------------------------------------------
        | 1. Business identity (incl. federal tax IDs — formerly its own
        |    tax_identification step; merged to cut wizard length)
        |------------------------------------------------------------------
        */
        'identity' => [
            'title' => 'Business Identity',
            'description' => "Tell us who you legally are. We'll use this on every state filing. Tax IDs are encrypted at rest.",
            'groups' => [
                ['title' => 'Business Identity', 'fields' => [
                    'legal_name', 'dba_name', 'entity_type', 'formation_state',
                ]],
                ['title' => 'Tax Identification', 'fields' => [
                    'individual_ssn', 'fein',
                ]],
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
                    'options' => $stateOptions,
                    'rules' => ['required', 'size:2'],
                    'help' => 'The state where your business was legally formed or registered.',
                    // Hidden until an entity type is chosen; sole props
                    // aren't "formed" in a state so they never see it.
                    'when' => ['in' => [['var' => 'entity_type'], [
                        'llc_single', 'llc_multi', 'corporation', 's_corp', 'nonprofit',
                        'general_partnership', 'limited_partnership', 'llp',
                        'trust', 'estate', 'government', 'other',
                    ]]],
                ],
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
                    'rules' => [
                        'nullable',
                        'regex:/^\d{2}-?\d{7}$/',
                        'required_unless:{prefix}entity_type,sole_prop',
                    ],
                    'help' => 'Get an EIN at https://www.irs.gov/businesses/employer-identification-number',
                    'help_when' => [
                        [
                            'condition' => ['==' => [['var' => 'entity_type'], 'sole_prop']],
                            'help' => 'You may leave blank, but having an EIN is highly recommended. Get an EIN at https://www.irs.gov/businesses/employer-identification-number',
                        ],
                    ],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
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

        /*
        |------------------------------------------------------------------
        | 2. Contact & address
        |------------------------------------------------------------------
        */
        'contact_and_address' => [
            'title' => 'Contact & Address',
            'description' => 'Where the business operates and how states can reach you.',
            'groups' => [
                ['title' => 'Contact', 'fields' => ['business_email', 'business_phone']],
                ['title' => 'Address', 'fields' => ['business_address', 'mailing_address_same', 'mailing_address']],
            ],
            'fields' => [
                'business_email' => [
                    'type' => 'email',
                    'label' => 'Business Email Address',
                    'rules' => ['required', 'email', 'max:255'],
                    'placeholder' => 'you@example.com',
                ],
                'business_phone' => [
                    'type' => 'text',
                    'label' => 'Business Phone Number',
                    'rules' => ['required', 'string', 'max:20'],
                    'placeholder' => '(123) 456-7890',
                    'mask' => '(999) 999-9999',
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
                    'when' => ['==' => [['var' => 'mailing_address_same'], '0']],
                    'persist_to_business' => true,
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 3. Business activity
        |------------------------------------------------------------------
        */
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
                ],
                'business_description' => [
                    'type' => 'textarea',
                    'rows' => 4,
                    'label' => 'Description of Business / Principal Products or Services',
                    'rules' => ['required', 'string', 'max:500'],
                    'placeholder' => 'Briefly describe what your business sells or does',
                ],
                'naics_code' => [
                    'type' => 'text',
                    'label' => 'NAICS Code',
                    'rules' => ['required', 'digits:6'],
                    'help' => 'Find your code here: https://www.census.gov/naics/',
                    'placeholder' => '123456',
                    'mask' => '999999',
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 4. Responsible people (asked once per person)
        |------------------------------------------------------------------
        */
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
                    'schema_groups' => [
                        ['title' => 'Basic Details', 'fields' => [['first_name', 'last_name'], 'title']],
                        ['title' => 'Contact', 'fields' => [['email', 'phone']]],
                        ['title' => 'Home Address', 'fields' => ['home_address']],
                        ['title' => 'Verification Details', 'fields' => [['dob', 'ssn']]],
                        ['title' => 'Driver License', 'fields' => [
                            ['driver_license_state', 'driver_license_number'],
                            'driver_license_expiration',
                        ]],
                        ['title' => 'Authorization', 'fields' => ['ownership_percent', 'is_authorized_signer']],
                    ],
                    'schema' => [
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
                        'driver_license_state' => [
                            'type' => 'select',
                            'label' => 'Driver License State',
                            'options' => $stateOptions,
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

        /*
        |------------------------------------------------------------------
        | 5. Locations (canonical multi-location model)
        |------------------------------------------------------------------
        | Per-state location counts (PA establishments, NY multi-location,
        | GA 4+ locations, MD multiple locations, TX distribution points)
        | are DERIVED from these rows — never asked again.
        */
        'locations' => [
            'title' => 'Business Locations',
            'description' => 'Add every physical location where this business operates. Your principal business address should be the first location.',
            'fields' => [
                'locations' => [
                    'type' => 'repeater',
                    'label' => 'Locations',
                    'min' => 1,
                    'item_label' => 'Location',
                    'schema_groups' => [
                        ['title' => 'Location', 'fields' => ['is_principal', 'address', 'county']],
                    ],
                    'schema' => [
                        'is_principal' => [
                            'type' => 'checkbox',
                            'label' => 'This is the principal business location',
                        ],
                        'address' => [
                            'type' => 'address',
                            'label' => 'Location Address',
                            'rules' => ['required'],
                        ],
                        'county' => [
                            'type' => 'select',
                            'label' => 'County',
                            'options' => '<<row_state_counties>>',
                            'rules' => ['nullable'],
                        ],
                    ],
                    'rules' => ['required', 'array', 'min:1'],
                ],
            ],
            'cross_validations' => [
                [
                    'rule' => 'locations_principal_unique_and_matches_business_address',
                    'field' => 'locations',
                    'phase' => 'core',
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 6. State dates & estimates (MATRIX)
        |------------------------------------------------------------------
        */
        'state_dates_and_estimates' => [
            'title' => 'State Dates & Estimates',
            'description' => 'Per-state dates and sales estimates — answer once for each state in your application.',
            'fields' => [
                'matrix_sales_tax_start_date' => [
                    'type' => 'matrix',
                    'label' => 'Date you will start collecting sales tax in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['required', 'date'],
                    'allow_same_for_all' => true,
                    'applicable_states' => '*',
                    'help' => "When your obligation to collect that state's sales tax begins. May be retroactive if you have past nexus.",
                ],
                'matrix_estimated_monthly_taxable_sales' => [
                    'type' => 'matrix',
                    'label' => 'Estimated monthly taxable sales in {state_name} (USD)',
                    'cell_type' => 'text',
                    'cell_rules' => ['required', 'integer', 'min:0'],
                    'cell_mask' => '9999999999999',
                    'allow_same_for_all' => true,
                    'applicable_states' => ['CA', 'TX'],
                    'help' => 'Your best estimate of monthly taxable sales. Whole dollars only.',
                ],
                'matrix_annual_sales' => [
                    'type' => 'matrix',
                    'label' => 'Expected annual sales in {state_name} (USD)',
                    'cell_type' => 'text',
                    'cell_rules' => ['required', 'integer', 'min:0'],
                    'cell_mask' => '9999999999999',
                    'allow_same_for_all' => true,
                    'applicable_states' => ['NY', 'TN', 'IL', 'CA'],
                ],
                'matrix_annual_gross_income' => [
                    'type' => 'matrix',
                    'label' => 'Estimated annual gross income in {state_name} (USD)',
                    'cell_type' => 'text',
                    'cell_rules' => ['required', 'integer', 'min:0'],
                    'cell_mask' => '9999999999999',
                    'applicable_states' => ['WA'],
                ],
                'matrix_employee_count' => [
                    'type' => 'matrix',
                    'label' => 'Number of {state_name} employees',
                    'cell_type' => 'text',
                    'cell_rules' => ['required', 'integer', 'min:0'],
                    'applicable_states' => ['FL', 'MD', 'NJ', 'WA'],
                ],
                'matrix_first_sales_date' => [
                    'type' => 'matrix',
                    'label' => 'Date of first taxable sales / operations in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['required', 'date'],
                    'allow_same_for_all' => true,
                    'applicable_states' => ['OH', 'PA', 'GA', 'OK'],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 7. Sales channels & activities (ANYWHERE_STATES)
        |------------------------------------------------------------------
        */
        'sales_channels_and_activities' => [
            'title' => 'Sales Channels & Activities',
            'description' => 'Answer each question once. If it applies anywhere, pick which of your application states it applies to.',
            'fields' => [
                'applies_internet_or_mail_order' => [
                    'type' => 'anywhere_states',
                    'label' => 'Do you sell over the internet, through websites or marketplaces, or by mail order in any state in this application?',
                    'applicable_states' => ['CA', 'NY', 'IL', 'TX', 'FL'],
                ],
                'website_address' => [
                    'type' => 'text',
                    'label' => 'Website Address',
                    'rules' => ['nullable', 'string', 'max:255'],
                    'placeholder' => 'https://example.com',
                    'when' => ['==' => [['var' => 'applies_internet_or_mail_order.anywhere'], '1']],
                ],
                'applies_retail_sales' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you make retail sales to customers in any state in this application?',
                    'applicable_states' => ['CT', 'FL', 'IL', 'MO', 'OK'],
                ],
                'applies_remote_seller' => [
                    'type' => 'anywhere_states',
                    'label' => 'Are you an out-of-state or remote seller for any state in this application?',
                    'applicable_states' => ['MD', 'MO', 'OK'],
                ],
                'applies_marketplace' => [
                    'type' => 'anywhere_states',
                    'label' => 'Do you operate a marketplace for third-party sellers, or sell only through marketplace facilitators, in any state in this application?',
                    'applicable_states' => ['CT', 'GA', 'TX'],
                ],
                'applies_physical_presence' => [
                    'type' => 'anywhere_states',
                    'label' => 'Do you have physical presence, sales reps, warehouses, inventory, or other operations in any state in this application?',
                    'applicable_states' => ['IL', 'TN', 'TX'],
                ],
                'applies_ship_or_deliver' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you ship or deliver goods to customers in any state in this application?',
                    'applicable_states' => ['CA', 'TX'],
                ],
                'applies_home_or_residence_based' => [
                    'type' => 'anywhere_states',
                    'label' => 'Is the business operated from a residence or home-based location in any state in this application?',
                    'applicable_states' => ['TX', 'OK', 'WA'],
                ],
                'applies_temporary_event_sales' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell at temporary, non-permanent, itinerant, fair, festival, or event locations in any state in this application?',
                    'applicable_states' => ['CA', 'FL', 'TX'],
                ],
                'temporary_events' => [
                    'type' => 'repeater',
                    'label' => 'Temporary Events / Locations',
                    'min' => 0,
                    'item_label' => 'Temporary Event',
                    'when' => ['==' => [['var' => 'applies_temporary_event_sales.anywhere'], '1']],
                    'schema' => [
                        'event_name' => [
                            'type' => 'text',
                            'label' => 'Event / Location Name',
                            'rules' => ['required', 'string', 'max:120'],
                        ],
                        'state_code' => [
                            'type' => 'select',
                            'label' => 'State',
                            'options' => '<<selected_states>>',
                            'rules' => ['required', 'size:2'],
                        ],
                        'period_start' => [
                            'type' => 'date',
                            'label' => 'Period Start',
                            'rules' => ['nullable', 'date'],
                        ],
                        'period_end' => [
                            'type' => 'date',
                            'label' => 'Period End',
                            'rules' => ['nullable', 'date'],
                        ],
                        'address' => [
                            'type' => 'address',
                            'label' => 'Event Address (optional)',
                            'rules' => ['nullable'],
                        ],
                    ],
                    'rules' => ['nullable', 'array'],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 8. Products & services (ANYWHERE_STATES)
        |------------------------------------------------------------------
        */
        'products_and_services' => [
            'title' => 'Products & Services',
            'description' => 'Tell us once about regulated products and services. State-specific follow-ups appear only for the states you pick.',
            'fields' => [
                'applies_alcohol' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell, serve, manufacture, distribute, wholesale, ship, or otherwise handle alcoholic beverages in any state in this application?',
                    'applicable_states' => ['CA', 'IL', 'NJ', 'NY', 'OH', 'OK', 'TN', 'TX'],
                ],
                'applies_tobacco_vape' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell cigarettes, tobacco products, e-cigarettes, or vaping devices in any state in this application?',
                    'applicable_states' => ['CA', 'GA', 'IL', 'MI', 'NJ', 'NY', 'OK', 'PA', 'TX'],
                ],
                'applies_fuel' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell, distribute, consume, or handle motor fuel, diesel, aviation fuel, heating fuel, or other fuel products in any state in this application?',
                    'applicable_states' => ['CA', 'GA', 'IL', 'MI', 'MO', 'NJ', 'NY'],
                ],
                'applies_vending' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell through vending machines, food vending machines, or coin-operated machines in any state in this application?',
                    'applicable_states' => ['CA', 'FL', 'IL', 'OK'],
                ],
                'applies_contractor' => [
                    'type' => 'anywhere_states',
                    'label' => 'Are you a contractor, or will you perform construction or real-property improvement work, in any state in this application?',
                    'applicable_states' => ['CA', 'FL', 'GA', 'OK', 'PA'],
                ],
                'applies_taxable_services' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you provide taxable services in any state in this application?',
                    'applicable_states' => ['CT', 'OK', 'PA', 'TX'],
                ],
                'applies_lodging_or_rentals' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you provide lodging, hotel/motel stays, short-term rentals, room occupancy, or manage rental property in any state in this application?',
                    'applicable_states' => ['CT', 'FL', 'IL', 'NJ', 'OK', 'WI'],
                ],
                'applies_vehicle_rentals' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you rent, lease, or provide passenger cars, motor vehicles, limousines, or vehicle leases in any state in this application?',
                    'applicable_states' => ['CA', 'CT', 'IL', 'MO', 'NJ', 'NY', 'OK', 'WI'],
                ],
                'applies_equipment_rentals' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you rent or lease equipment or other tangible personal property in any state in this application?',
                    'applicable_states' => ['CA', 'CT', 'FL'],
                ],
                'applies_telecom_or_prepaid_wireless' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you provide telecommunications, voice communications, prepaid wireless, or 911-taxable services in any state in this application?',
                    'applicable_states' => ['CT', 'FL', 'GA', 'NJ', 'NY', 'OK', 'TX', 'WI'],
                ],
                'applies_fireworks' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell fireworks in any state in this application?',
                    'applicable_states' => ['GA', 'OK', 'TX'],
                ],
                'applies_cannabis' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you cultivate, process, or dispense medical cannabis in any state in this application?',
                    'applicable_states' => ['IL', 'OK'],
                ],
                'applies_utilities' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you sell or provide utilities (electricity, natural gas, water/sewer) in any state in this application?',
                    'applicable_states' => ['IL', 'MO', 'NJ', 'NY', 'FL'],
                ],
                'applies_hazardous_materials' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you store or use hazardous, flammable, or toxic materials, compressed gases, or similar regulated materials in any state in this application?',
                    'applicable_states' => ['NJ', 'WA'],
                ],
                'applies_admissions_entertainment' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you charge admissions or operate entertainment, recreation, amusement, or social/athletic club activities in any state in this application?',
                    'applicable_states' => ['CT', 'FL', 'NY'],
                ],
                'applies_seasonal' => [
                    'type' => 'anywhere_states',
                    'label' => 'Is the business seasonal, open only during certain months, or one-time in any state in this application?',
                    'applicable_states' => '*',
                ],
                'seasonal_open_month' => [
                    'type' => 'select',
                    'label' => 'First Month of Open Season',
                    'options' => $months,
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'applies_seasonal.anywhere'], '1']],
                ],
                'seasonal_close_month' => [
                    'type' => 'select',
                    'label' => 'Last Month of Open Season',
                    'options' => $months,
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'applies_seasonal.anywhere'], '1']],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 9. Employees & payroll
        |------------------------------------------------------------------
        */
        'employees_and_payroll' => [
            'title' => 'Employees & Payroll',
            'description' => 'Employment, withholding, and unemployment registration — asked once, applied to the states you pick.',
            'fields' => [
                'applies_employees_or_payroll' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you have employees, pay wages or labor, register for withholding, or register for unemployment/reemployment tax in any state in this application?',
                    'applicable_states' => ['CT', 'FL', 'GA', 'IL', 'MD', 'MI', 'MO', 'NJ', 'OK', 'TX'],
                ],
                'applies_payroll_service_or_peo' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will you use a payroll service, PEO, or employee leasing arrangement in any state in this application?',
                    'applicable_states' => ['CT', 'MI', 'NJ'],
                ],
                'matrix_payroll_begin_date' => [
                    'type' => 'matrix',
                    'label' => 'Payroll begin / first pay date in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['nullable', 'date'],
                    'applicable_states' => ['IL', 'NJ', 'CT'],
                    'when' => ['==' => [['var' => 'applies_employees_or_payroll.anywhere'], '1']],
                ],
                'matrix_first_hire_date' => [
                    'type' => 'matrix',
                    'label' => 'Date of first hire in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['nullable', 'date'],
                    'applicable_states' => ['NJ'],
                    'when' => ['==' => [['var' => 'applies_employees_or_payroll.anywhere'], '1']],
                ],
                'matrix_first_wages_paid_date' => [
                    'type' => 'matrix',
                    'label' => 'Date wages first paid (or anticipated) in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['nullable', 'date'],
                    'applicable_states' => ['MD', 'FL'],
                    'when' => ['==' => [['var' => 'applies_employees_or_payroll.anywhere'], '1']],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 10. Acquisition & business history
        |------------------------------------------------------------------
        */
        'acquisition_and_history' => [
            'title' => 'Acquisition & Business History',
            'description' => 'Prior registrations, prior names, and any business you purchased or acquired.',
            'groups' => [
                ['title' => 'Prior Registrations & Names', 'fields' => [
                    'ever_issued_tax_certificate', 'prior_certificate_state',
                    'was_known_by_another_name', 'prior_business_name',
                ]],
                ['title' => 'Entity History', 'fields' => [
                    'entity_involved_in_merger', 'entity_legal_structure_change',
                    'entity_underwent_restructuring', 'entity_currently_forming_or_acquiring',
                    'entity_currently_incorporating_existing',
                ]],
                ['title' => 'Purchased / Acquired Business', 'fields' => [
                    'applies_purchased_or_acquired_business',
                    'predecessor_legal_name', 'predecessor_fein', 'predecessor_address',
                    'matrix_acquisition_date',
                    'predecessor_acquired_51_pct_any_class', 'predecessor_acquired_51_pct_total_assets',
                    'predecessor_ceased_paying_wages', 'predecessor_ceased_operations',
                ]],
            ],
            'fields' => [
                'ever_issued_tax_certificate' => yesNoField('Has this business ever been issued a certificate of registration, certificate number, or tax account number in any state?', '', ['drives_conditional' => true]),
                'prior_certificate_state' => [
                    'type' => 'select',
                    'label' => 'State the certificate was issued in',
                    'options' => $stateOptions,
                    'rules' => ['nullable', 'size:2'],
                    'when' => ['==' => [['var' => 'ever_issued_tax_certificate'], '1']],
                ],
                'was_known_by_another_name' => yesNoField('Has this business ever been known by another name?', '', ['drives_conditional' => true]),
                'prior_business_name' => [
                    'type' => 'text',
                    'label' => 'Previous Business Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'was_known_by_another_name'], '1']],
                ],

                'entity_involved_in_merger' => yesNoField('Has this entity been involved in a merger within the last seven years?'),
                'entity_legal_structure_change' => yesNoField('Did the business result from a change in legal structure?'),
                'entity_underwent_restructuring' => yesNoField('Did the business undergo a merger, consolidation, dissolution, or other restructuring?'),
                'entity_currently_forming_or_acquiring' => yesNoField('Are you currently forming or acquiring a business?'),
                'entity_currently_incorporating_existing' => yesNoField('Are you currently incorporating an existing business entity?'),

                'applies_purchased_or_acquired_business' => [
                    'type' => 'anywhere_states',
                    'label' => 'Did you purchase or acquire all or part of an existing business in any state in this application?',
                    'applicable_states' => ['CA', 'TX', 'NY', 'FL', 'MD', 'NJ', 'PA', 'MI', 'OH', 'OK'],
                ],
                'predecessor_legal_name' => [
                    'type' => 'text',
                    'label' => 'Previous Owner / Predecessor Legal Name',
                    'rules' => ['nullable', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ],
                'predecessor_fein' => [
                    'type' => 'text',
                    'label' => 'Previous Owner / Predecessor FEIN',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                    'sensitive' => true,
                ],
                'predecessor_address' => [
                    'type' => 'address',
                    'label' => 'Previous Owner Business Address',
                    'rules' => ['nullable'],
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ],
                'matrix_acquisition_date' => [
                    'type' => 'matrix',
                    'label' => 'Date the business was acquired in {state_name}',
                    'cell_type' => 'date',
                    'cell_rules' => ['nullable', 'date', 'before_or_equal:today'],
                    'applicable_states' => ['CA', 'TX', 'NY', 'FL', 'MD', 'NJ', 'PA', 'MI', 'OH', 'OK'],
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ],
                'predecessor_acquired_51_pct_any_class' => nullableYesNoField('Did you acquire 51% or more of any class of the predecessor\'s stock or assets?', '', [
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ]),
                'predecessor_acquired_51_pct_total_assets' => nullableYesNoField('Did you acquire 51% or more of the predecessor\'s total assets?', '', [
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ]),
                'predecessor_ceased_paying_wages' => nullableYesNoField('Has the predecessor ceased paying wages?', '', [
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ]),
                'predecessor_ceased_operations' => nullableYesNoField('Has the predecessor ceased operations?', '', [
                    'when' => ['==' => [['var' => 'applies_purchased_or_acquired_business.anywhere'], '1']],
                ]),
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 11. Bank account (legacy: standard NV/IN/AZ/WA agreements +
        |     CA/CT/NY/OH/OK/TX/WI blocks)
        |------------------------------------------------------------------
        */
        'bank' => [
            'title' => 'Business Bank Account',
            'description' => 'Some states collect business banking details with the registration.',
            'fields' => [
                'has_business_bank_account' => yesNoField('Does the business have a bank account?', '', [
                    'drives_conditional' => true,
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ]),
                'bank_name' => [
                    'type' => 'text',
                    'label' => 'Bank Name',
                    'rules' => ['nullable', 'string', 'max:100'],
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ],
                'bank_account_type' => [
                    'type' => 'radio',
                    'label' => 'Type of Account',
                    'options' => ['1' => 'Checking', '0' => 'Savings'],
                    'rules' => ['nullable', 'in:0,1'],
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ],
                'bank_routing_number' => [
                    'type' => 'text',
                    'label' => 'Bank Routing Number',
                    'rules' => ['nullable', 'digits:9'],
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'sensitive' => true,
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ],
                'bank_account_number' => [
                    'type' => 'text',
                    'label' => 'Bank Account Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'sensitive' => true,
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ],
                'bank_is_foreign' => nullableYesNoField('Is this a foreign bank?', '', [
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'applicable_states' => ['AZ', 'CA', 'CT', 'IN', 'NV', 'NY', 'OH', 'OK', 'TX', 'WA', 'WI'],
                ]),
                'bank_branch_location' => [
                    'type' => 'text',
                    'label' => 'Bank Branch Location (optional)',
                    'rules' => ['nullable', 'string', 'max:100'],
                    'when' => ['==' => [['var' => 'has_business_bank_account'], '1']],
                    'applicable_states' => ['CA'],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 12. Payment processing (CA/NY/OK/TX)
        |------------------------------------------------------------------
        */
        'payment_processor' => [
            'title' => 'Card Payments',
            'description' => 'Card acceptance and payment processor details.',
            'fields' => [
                'applies_accepts_cards' => [
                    'type' => 'anywhere_states',
                    'label' => 'Will the business accept credit or debit card payments?',
                    'applicable_states' => ['CA', 'NY', 'OK', 'TX'],
                ],
                'payment_processor_name' => [
                    'type' => 'text',
                    'label' => 'Payment Processor Name',
                    'rules' => ['nullable', 'string', 'max:100'],
                    'when' => ['==' => [['var' => 'applies_accepts_cards.anywhere'], '1']],
                    'applicable_states' => ['CA', 'NY', 'OK', 'TX'],
                ],
                'payment_processor_merchant_id' => [
                    'type' => 'text',
                    'label' => 'Merchant Identification Number (MID)',
                    'rules' => ['nullable', 'string', 'max:100'],
                    'when' => ['==' => [['var' => 'applies_accepts_cards.anywhere'], '1']],
                    'applicable_states' => ['CA', 'NY', 'OK', 'TX'],
                ],
                'payment_processor_taxpayer_id_on_file' => [
                    'type' => 'text',
                    'label' => 'SSN or FEIN on file with the payment processor',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => 'applies_accepts_cards.anywhere'], '1']],
                    'sensitive' => true,
                    'applicable_states' => ['OK'],
                ],
            ],
        ],

        /*
        |------------------------------------------------------------------
        | 13. Entity extras (legacy standard flow asks these for EVERY
        |     state — corp shareholder block, LLC tax matters, unitary,
        |     disregarded, supply-chain role + corporate details, which
        |     was formerly its own step)
        |------------------------------------------------------------------
        */
        'entity_extras' => [
            'title' => 'Ownership & Classification',
            'description' => 'Ownership structure, classification, and incorporation details states commonly require.',
            'groups' => [
                ['title' => 'Majority Shareholder', 'fields' => [
                    'shareholder_owns_more_than_50',
                    ['majority_shareholder_first_name', 'majority_shareholder_last_name'],
                    'majority_shareholder_other_corp_tax_owed', 'majority_shareholder_tax_crime',
                ]],
                ['title' => 'LLC / Partnership', 'fields' => [
                    'llc_member_responsible_for_tax', 'llc_member_owns_more_than_50',
                    'entity_is_disregarded', 'entity_is_disregarded_owner_fein',
                ]],
                ['title' => 'Corporate Filing Group', 'fields' => [
                    'entity_files_unitary', 'unitary_filing_agent_fein',
                ]],
                // Formerly the standalone "Corporate Details" step — both
                // steps are entity-classification questions that skip for
                // sole props, so they share one wizard stop now.
                ['title' => 'Corporate Details', 'fields' => [
                    'state_registration_date',
                    'incorporation_country',
                    'publicly_traded', 'ticker_symbol',
                    ['fiscal_year_end_month', 'fiscal_year_end_day'],
                ]],
                ['title' => 'Role in Supply Chain', 'fields' => [
                    'supply_chain_role_manufacturer', 'supply_chain_role_wholesaler',
                    'supply_chain_role_distributor', 'supply_chain_role_retailer',
                ]],
            ],
            'fields' => [
                'shareholder_owns_more_than_50' => nullableYesNoField('Does any shareholder own more than 50% of the voting stock of the corporation?', '', [
                    'when' => ['in' => [['var' => 'entity_type'], ['corporation', 's_corp']]],
                    'drives_conditional' => true,
                ]),
                'majority_shareholder_first_name' => [
                    'type' => 'text',
                    'label' => 'First Name of >50% Shareholder',
                    'rules' => ['nullable', 'string', 'max:60'],
                    'when' => ['==' => [['var' => 'shareholder_owns_more_than_50'], '1']],
                ],
                'majority_shareholder_last_name' => [
                    'type' => 'text',
                    'label' => 'Last Name of >50% Shareholder',
                    'rules' => ['nullable', 'string', 'max:60'],
                    'when' => ['==' => [['var' => 'shareholder_owns_more_than_50'], '1']],
                ],
                'majority_shareholder_other_corp_tax_owed' => nullableYesNoField('Did this shareholder own more than 50% of a different corporation that owes unpaid sales tax?', '', [
                    'when' => ['==' => [['var' => 'shareholder_owns_more_than_50'], '1']],
                ]),
                'majority_shareholder_tax_crime' => nullableYesNoField('Has this shareholder been convicted of a tax crime in the past year?', '', [
                    'when' => ['==' => [['var' => 'shareholder_owns_more_than_50'], '1']],
                ]),

                'llc_member_responsible_for_tax' => nullableYesNoField('Has any member been designated as the tax matters partner or person responsible for tax issues?', '', [
                    'when' => ['in' => [['var' => 'entity_type'], ['llc_single', 'llc_multi', 'llp', 'limited_partnership']]],
                ]),
                'llc_member_owns_more_than_50' => nullableYesNoField('Does any member own more than 50% of the company?', '', [
                    'when' => ['in' => [['var' => 'entity_type'], ['llc_single', 'llc_multi', 'llp', 'limited_partnership']]],
                ]),
                'entity_is_disregarded' => nullableYesNoField('Is this business a disregarded entity for federal income tax purposes?', '', [
                    'when' => ['==' => [['var' => 'entity_type'], 'llc_single']],
                    'drives_conditional' => true,
                ]),
                'entity_is_disregarded_owner_fein' => [
                    'type' => 'text',
                    'label' => 'FEIN of the tax-reporting entity (disregarded entity owner)',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
                    'when' => ['==' => [['var' => 'entity_is_disregarded'], '1']],
                    'sensitive' => true,
                ],

                'entity_files_unitary' => nullableYesNoField('Are you part of a unitary filing group for income tax purposes?', '', [
                    'when' => ['in' => [['var' => 'entity_type'], ['corporation', 's_corp']]],
                    'drives_conditional' => true,
                ]),
                'unitary_filing_agent_fein' => [
                    'type' => 'text',
                    'label' => 'Unitary Filing Agent FEIN',
                    'rules' => ['nullable', 'regex:/^\d{2}-?\d{7}$/'],
                    'placeholder' => '12-3456789',
                    'mask' => '99-9999999',
                    'when' => ['==' => [['var' => 'entity_files_unitary'], '1']],
                    'sensitive' => true,
                ],

                'state_registration_date' => [
                    'type' => 'date',
                    'label' => 'Date of Formation / Incorporation',
                    'help' => 'The date your business was incorporated or registered with its state of formation.',
                    'rules' => ['nullable', 'date', 'before_or_equal:today'],
                    'when' => ['!=' => [['var' => 'entity_type'], 'sole_prop']],
                ],
                'incorporation_country' => [
                    'type' => 'text',
                    'label' => 'Country of Incorporation',
                    'rules' => ['nullable', 'string', 'max:60'],
                    'placeholder' => 'United States',
                    'when' => ['in' => [['var' => 'entity_type'], ['corporation', 's_corp', 'nonprofit']]],
                ],
                'publicly_traded' => nullableYesNoField('Is the entity a publicly traded corporation?', '', [
                    'when' => ['in' => [['var' => 'entity_type'], ['corporation', 's_corp']]],
                    'drives_conditional' => true,
                ]),
                'ticker_symbol' => [
                    'type' => 'text',
                    'label' => 'Stock Ticker Symbol',
                    'rules' => ['nullable', 'string', 'max:10'],
                    'when' => ['==' => [['var' => 'publicly_traded'], '1']],
                ],
                'fiscal_year_end_month' => [
                    'type' => 'select',
                    'label' => 'Fiscal Year Ending Month',
                    'options' => $months,
                    'rules' => ['nullable'],
                    'when' => ['!=' => [['var' => 'entity_type'], 'sole_prop']],
                ],
                'fiscal_year_end_day' => [
                    'type' => 'text',
                    'label' => 'Fiscal Year Ending Day (1-31)',
                    'rules' => ['nullable', 'integer', 'min:1', 'max:31'],
                    'when' => ['!=' => [['var' => 'entity_type'], 'sole_prop']],
                ],

                'supply_chain_role_manufacturer' => ['type' => 'checkbox', 'label' => 'Manufacturer'],
                'supply_chain_role_wholesaler' => ['type' => 'checkbox', 'label' => 'Wholesaler'],
                'supply_chain_role_distributor' => ['type' => 'checkbox', 'label' => 'Distributor'],
                'supply_chain_role_retailer' => ['type' => 'checkbox', 'label' => 'Retailer'],
            ],
        ],
    ],

    /*
     * Base state steps are intentionally empty: every generic per-state
     * question moved into the core matrix, applies, and plain fields.
     * State override files append their genuinely state-specific steps;
     * states without an override file skip the states phase content
     * entirely (the runner auto-skips empty steps).
     */
    'state_steps' => [
        'state_details' => [
            'title' => '{state_name} Sales Tax Permit Details',
            'description' => 'Provide details specific to {state_name}.',
            'fields' => [],
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
                        // Empty by default. State files append per-person fields.
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

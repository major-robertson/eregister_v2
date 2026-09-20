<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    |
    | The core document types supported by the lien filing system.
    | These are seeded into the lien_document_types table.
    |
    */
    'document_types' => [
        'prelim_notice' => [
            'name' => 'Preliminary Notice',
            'slug' => 'prelim_notice',
            'description' => 'Notice to preserve lien rights, required in most states within a set time from first furnishing.',
        ],
        'noi' => [
            'name' => 'Notice of Intent to Lien',
            'slug' => 'noi',
            'description' => 'Warning notice sent before filing a mechanics lien, typically 10-30 days before lien filing.',
        ],
        'mechanics_lien' => [
            'name' => 'Mechanics Lien',
            'slug' => 'mechanics_lien',
            'description' => 'Legal claim against property for unpaid construction work or materials.',
        ],
        'lien_release' => [
            'name' => 'Lien Release',
            'slug' => 'lien_release',
            'description' => 'Document releasing a previously filed lien after payment is received.',
        ],
        'demand_letter' => [
            'name' => 'Payment Demand Letter',
            'slug' => 'demand_letter',
            'description' => 'Formal demand for payment sent to the debtor. Can be sent at any time.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pricing
    |--------------------------------------------------------------------------
    |
    | Pricing in cents for each document type and service level.
    |
    */
    'pricing' => [
        'prelim_notice' => [
            'self_serve' => 2900,
            'full_service' => 4900,
        ],
        'noi' => [
            'self_serve' => 4900,
            'full_service' => 9900,
        ],
        'mechanics_lien' => [
            'self_serve' => 9900,
            'full_service' => 29900,
        ],
        'lien_release' => [
            'self_serve' => 4900,
            'full_service' => 9900,
        ],
        'demand_letter' => [
            'self_serve' => 2900,
            'full_service' => 4900,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | State-Specific Pricing Overrides
    |--------------------------------------------------------------------------
    |
    | Per-state overrides that take precedence over the default `pricing`
    | above, keyed by uppercase 2-letter state code, then document type, then
    | service level (cents). Only the listed service levels are overridden;
    | unlisted ones fall back to the default price. This is the single source
    | of truth for state pricing: the wizard display reads it directly, and
    | PriceSeeder derives the corresponding DB rows from it (see
    | Price::resolveLien / variant_key "{STATE}_{service_level}").
    |
    */
    'state_pricing' => [
        'NJ' => [
            'mechanics_lien' => ['full_service' => 89900], // $899
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stripe Price IDs
    |--------------------------------------------------------------------------
    |
    | Stripe price IDs for each document type and service level.
    | These should be configured in your .env file.
    |
    */
    'stripe_prices' => [
        'prelim_notice_self' => env('STRIPE_PRICE_PRELIM_SELF'),
        'prelim_notice_full' => env('STRIPE_PRICE_PRELIM_FULL'),
        'noi_self' => env('STRIPE_PRICE_NOI_SELF'),
        'noi_full' => env('STRIPE_PRICE_NOI_FULL'),
        'mechanics_lien_self' => env('STRIPE_PRICE_LIEN_SELF'),
        'mechanics_lien_full' => env('STRIPE_PRICE_LIEN_FULL'),
        'lien_release_self' => env('STRIPE_PRICE_RELEASE_SELF'),
        'lien_release_full' => env('STRIPE_PRICE_RELEASE_FULL'),
        'demand_letter_self' => env('STRIPE_PRICE_DEMAND_LETTER_SELF'),
        'demand_letter_full' => env('STRIPE_PRICE_DEMAND_LETTER_FULL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Attorney Referral States (Mechanics Lien)
    |--------------------------------------------------------------------------
    |
    | States where mechanics liens cannot be filed online and require an
    | attorney to file directly with the court (e.g. Hawaii, Maryland,
    | Delaware). The wizard will block self-serve / full-service checkout
    | and surface the attorney-referral CTA for these states.
    |
    */
    'attorney_referral_states' => ['HI', 'MD', 'DE'],

    /*
    |--------------------------------------------------------------------------
    | Upload Constraints
    |--------------------------------------------------------------------------
    |
    | Limits for file uploads on filings.
    |
    */
    'uploads' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'max_files_per_filing' => 10,
        'allowed_mimes' => ['application/pdf', 'image/jpeg', 'image/png'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for deadline reminder notifications.
    |
    */
    'notifications' => [
        // Kill switch for lien:send-deadline-reminders.
        'reminders_enabled' => env('LIEN_DEADLINE_REMINDERS_ENABLED', true),

        // Days before the due date (0 = due today). Each fires on its exact
        // day only; a day that is missed is skipped, never caught up.
        'reminder_intervals' => [14, 7, 3, 1, 0],

        // Business-local hour from which reminders go out (the command runs
        // hourly from midnight; nobody wants a deadline email at 12:05am).
        'send_from_hour' => 8,

        // A single run that would send more than this sends nothing and
        // reports an error. Production ran at 2-10 emails a day in 2026-09,
        // all in one 8am Eastern run; the backlog this guards against was 345.
        'max_emails_per_run' => env('LIEN_DEADLINE_REMINDERS_MAX_PER_RUN', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Reason Messages
    |--------------------------------------------------------------------------
    |
    | Maps machine-readable status_reason codes to user-friendly messages.
    | These are used by LienProjectDeadline::getFriendlyStatusReason().
    |
    */
    'status_reasons' => [
        'no_lien_rights_for_claimant' => 'Your claimant type does not have lien rights in this state.',
        'missing_anchor_date' => 'Missing required date information.',
        'noc_requires_prior_prelim' => 'NOC was filed without a prior preliminary notice.',
        'tenant_project_not_allowed' => 'Tenant improvements are not lienable in this state.',
        'tenant_project_restrictions' => 'Tenant lien restrictions apply.',
        'owner_occupied_restrictions' => 'Owner-occupied property restrictions apply.',
        'unknown_property_type' => 'Confirm property type - restrictions may apply.',
        'purchase_conflict' => 'This step is locked due to a purchase conflict.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Missing Field Labels
    |--------------------------------------------------------------------------
    |
    | Maps field names to human-readable labels for the "deadline unknown" message.
    |
    */
    'missing_field_labels' => [
        'first_furnish_date' => 'first furnish date',
        'last_furnish_date' => 'last furnish date',
        'completion_date' => 'completion date',
        'noc_recorded_date' => 'NOC recorded date',
        'noc_filed_date' => 'NOC filed date',
        'contract_date' => 'contract date',
        'lien_recorded_date' => 'lien recorded date',
        'lien_filing_date' => 'lien filing date',
        'prelim_sent_date' => 'prelim notice sent date',
    ],
];

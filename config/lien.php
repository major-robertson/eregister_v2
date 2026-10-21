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
    | Recording Lead Times
    |--------------------------------------------------------------------------
    |
    | Business days we need before a deadline to get the document to the
    | county: `erecord` for e-recording counties (and counties that take
    | either), `mail` for mail-in counties. The wizard tells the customer we
    | must submit by the deadline minus this lead, and stores that date on the
    | filing at checkout. Major sets these.
    |
    */
    'recording_lead_business_days' => [
        'erecord' => 1,
        'mail' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rush Fees
    |--------------------------------------------------------------------------
    |
    | A full-service order placed close to its deadline pays a rush fee on
    | top of the service price. The tier comes from the business days left
    | before the submit-by date (App\Domains\Lien\Services\RushTier):
    | `rush` inside its min_days to max_days; `expedite` at or below its
    | max_days, including 0 and negative while the deadline itself is still
    | ahead (its min_days is not read). No fee once the deadline has passed.
    | The fees (cents) seed the `prices` rows lien/rush_fee/{tier}, which
    | checkout charges: change an amount with a new price row, not here
    | alone. `rush_fee_documents` lists the documents that pay one; Major
    | can add 'prelim_notice' and 'noi' (their wording is already in place).
    | Outside the required tiers (7 or more business days left, or no
    | deadline known) a full-service customer may add Rush on the review
    | step for the same `rush` fee.
    |
    */
    // Proposed; Major sets the final amounts.
    'rush_fees' => [
        'rush' => 7500,
        'expedite' => 15000,
    ],

    'rush_tiers' => [
        'rush' => ['min_days' => 4, 'max_days' => 6],
        'expedite' => ['min_days' => 1, 'max_days' => 3],
    ],

    'rush_fee_documents' => ['mechanics_lien'],

    /*
    |--------------------------------------------------------------------------
    | Generated Lien Documents
    |--------------------------------------------------------------------------
    |
    | Facts printed on every generated lien document (see
    | App\Domains\Lien\Documents). `preparer` is the "prepared by and return
    | to" block on recordable instruments; leave address_lines empty and the
    | CAN-SPAM postal address (MAIL_POSTAL_ADDRESS) is used instead.
    | `server_state` is where staff sign proofs of service, so it is the
    | perjury jurisdiction on staff-executed declarations. Values are
    | recorded with the instrument: check them before the first PDF ships.
    |
    */
    'documents' => [
        'preparer' => [
            'name' => 'eRegister',
            'attention' => null,
            'address_lines' => ['4869 Brownsboro Rd STE 101-E', 'Louisville, KY 40207'],
            'phone' => null,
            'email' => null,
        ],
        'server_state' => 'KY',
        'return_label_lines' => ['eRegister', '4869 Brownsboro Rd STE 101-E', 'Louisville, KY 40207'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recording Turnaround
    |--------------------------------------------------------------------------
    |
    | How long recording typically takes, by RecordingMethod value. The
    | "Submitted for Recording" email says "Recording typically takes {words}."
    | (App\Mail\FilingMilestone). Major sets the words. A county data file
    | (database/data/lien_counties/{xx}/{county}.php) can override them with a
    | `recording.turnaround` key: one string, or strings keyed by method. A
    | method without words here gets no turnaround sentence.
    |
    */
    'recording_turnaround' => [
        'erecord' => '1 to 5 business days',
        'mail' => '2 to 6 weeks',
    ],

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

        // Internal inbox for lien ops notices such as a signed demand letter.
        // Leave blank to send none.
        'ops_email' => env('LIEN_OPS_EMAIL', 'liens@eregister.com'),
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

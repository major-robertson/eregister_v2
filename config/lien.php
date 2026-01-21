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
            'self_serve' => 4900,
            'full_service' => 9900,
        ],
        'noi' => [
            'self_serve' => 4900,
            'full_service' => 9900,
        ],
        'mechanics_lien' => [
            'self_serve' => 9900,
            'full_service' => 19900,
        ],
        'lien_release' => [
            'self_serve' => 2900,
            'full_service' => 4900,
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
        'reminder_intervals' => [14, 7, 3, 1, 0], // Days before due date (0 = overdue)
    ],
];

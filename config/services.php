<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_API_KEY'),
    ],

    'postgrid' => [
        'api_key' => env('POSTGRID_API_KEY'), // Active key (test or live based on environment)
        'base_url' => env('POSTGRID_BASE_URL', 'https://api.postgrid.com/print-mail/v1'),
        'webhook_secret' => env('POSTGRID_WEBHOOK_SECRET'),
        'webhook_tolerance_seconds' => env('POSTGRID_WEBHOOK_TOLERANCE', 300), // 5 minutes
        'from' => [
            'company_name' => env('POSTGRID_FROM_COMPANY', 'eRegister'),
            'address_line_1' => env('POSTGRID_FROM_ADDRESS', '123 Main St'),
            'city' => env('POSTGRID_FROM_CITY', 'Louisville'),
            'state' => env('POSTGRID_FROM_STATE', 'KY'),
            'zip' => env('POSTGRID_FROM_ZIP', '40202'),
            'country' => env('POSTGRID_FROM_COUNTRY', 'US'),
        ],
    ],

];

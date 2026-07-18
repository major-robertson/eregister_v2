<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    |
    | Lien waivers are a Cashier subscription on the Business (like resale
    | certs): $99/mo or $990/yr, no pay-per-waiver. Price rows live in the
    | prices table under product_family "lien" / product_key "lien_waiver"
    | with variant_key "monthly" / "yearly".
    |
    */
    'subscription_type' => 'lien_waiver',

    'prices' => [
        'monthly' => [
            'amount_cents' => 9900,
            'stripe_price_id_test' => env('STRIPE_PRICE_LIEN_WAIVER_MONTHLY'),
            'stripe_price_id_live' => env('STRIPE_PRICE_LIEN_WAIVER_MONTHLY_LIVE'),
        ],
        'yearly' => [
            'amount_cents' => 99000,
            'stripe_price_id_test' => env('STRIPE_PRICE_LIEN_WAIVER_YEARLY'),
            'stripe_price_id_live' => env('STRIPE_PRICE_LIEN_WAIVER_YEARLY_LIVE'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Free tier
    |--------------------------------------------------------------------------
    |
    | The free tier gets the full product — download, e-signature
    | send/collect, reminders, signed-copy storage — for up to this many
    | waivers per calendar month (each waiver auto-saves when the wizard
    | reaches review, which is what consumes a slot). The subscription
    | removes the monthly cap.
    |
    */
    'free_saved_waivers_per_month' => 3,

    /*
    |--------------------------------------------------------------------------
    | Signature reminders
    |--------------------------------------------------------------------------
    |
    | Days after the invitation (or previous reminder) at which the signer is
    | nudged again. Each reminder re-issues a fresh signed link so a reminder
    | never points at an expired URL.
    |
    */
    'reminder_intervals_days' => [3, 7, 12],

];

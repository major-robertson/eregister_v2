<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription
    |--------------------------------------------------------------------------
    |
    | Lien waivers are a Cashier subscription on the Business (like resale
    | certs): $49/mo or $490/yr per seat, no pay-per-waiver. The Stripe recurring
    | Price IDs live in the prices table (seeded by LienWaiverPriceSeeder)
    | under product_family "lien" / product_key "lien_waiver" with
    | variant_key "monthly" / "yearly" — never in env. Only the display
    | amounts are mirrored here for the pricing/upsell copy.
    |
    */
    'subscription_type' => 'lien_waiver',

    'prices' => [
        'monthly' => ['amount_cents' => 4900],
        'yearly' => ['amount_cents' => 49000],
    ],

    /*
    |--------------------------------------------------------------------------
    | Free tier
    |--------------------------------------------------------------------------
    |
    | Free to create, pay to sign. The free tier creates and saves up to this
    | many waivers per calendar month (each waiver auto-saves when the wizard
    | reaches review, which is what consumes a slot), downloads the unsigned
    | PDF with no watermark, and can upload a copy signed on paper. A Pro seat
    | removes the cap and adds e-signature: signing your own waivers, sending
    | and collecting signatures, reminders, and signed-copy storage.
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

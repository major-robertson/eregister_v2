<?php

return [
    'sales_tax_permit' => [
        'name' => 'Sales & Use Tax Permit',
        'billing_type' => 'one_time_per_state', // qty = state count
        'stripe_price_id' => env('STRIPE_PRICE_SALES_TAX'),
        'state_mode' => 'multi', // multi, single, none
        'max_states' => 40,
        'definition_dir' => 'SalesTaxPermit',
    ],
    'llc' => [
        'name' => 'LLC Formation',
        'billing_type' => 'subscription',
        'stripe_price_id' => env('STRIPE_PRICE_LLC'),
        'subscription_name' => 'llc', // Cashier subscription name
        'subscription_interval' => 'yearly',
        'state_mode' => 'single', // user picks one state of incorporation
        'max_states' => 1,
        'definition_dir' => 'LLC',
    ],
];

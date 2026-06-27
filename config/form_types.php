<?php

return [
    'sales_tax_permit' => [
        'name' => 'Sales & Use Tax Permit',
        'billing_type' => 'one_time_per_state', // qty = state count
        'stripe_price_id' => env('STRIPE_PRICE_SALES_TAX'),
        'state_mode' => 'multi', // multi, single, none
        'max_states' => null,
        'definition_dir' => 'SalesTaxPermit',
    ],
    'llc' => [
        'name' => 'LLC Formation',
        'billing_type' => 'subscription',
        'stripe_price_id' => env('STRIPE_PRICE_LLC'), // $299/yr recurring membership price
        'subscription_name' => 'llc', // Cashier subscription name
        'subscription_interval' => 'yearly',
        // Billed as the $299/yr membership PLUS a one-time state filing fee
        // (charged together at checkout). FormationCheckout reads this flag to
        // add the per-state fee line item; billing is satisfied per-application
        // by paid_at (each LLC pays its own state fee), not by a global sub.
        'one_time_state_fee' => true,
        // A company (Business) can form at most one LLC. The start flow blocks
        // a second once one is paid/submitted; a user with multiple Businesses
        // can form one LLC per Business.
        'one_per_business' => true,
        'state_mode' => 'single', // user picks one state of incorporation
        'max_states' => 1,
        'definition_dir' => 'LLC',
    ],
];

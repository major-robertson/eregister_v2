<?php

/*
|--------------------------------------------------------------------------
| Formation ongoing (year 2+) state fees
|--------------------------------------------------------------------------
|
| Per-state recurring state fees (annual report / franchise) charged on the
| LLC membership's renewal invoice, in addition to the $299/yr membership.
| Year-1 formation fees live in the Price catalog (FormationFeeSeeder); this
| file covers ONLY the recurring fees.
|
| Each state maps to a list of fee components:
|   - component_key   stable id, unique within a state (used in the renewal
|                     ledger unique key + Stripe idempotency key)
|   - label           shown on the customer's Stripe invoice line item
|   - amount_cents    the fee (null for manual/income-based)
|   - interval_years  charge every N renewal cycles (1 = annual, 2 = biennial)
|   - first_cycle_due first renewal cycle it is owed (1 = ~12 months after
|                     formation, 2 = ~24 months)
|   - charge_mode     'auto' (charged) or 'manual' (informational only — e.g.
|                     income-threshold franchise taxes we don't auto-charge)
|
| A state with no recurring fee maps to an empty list. See FormationFeeSchedule.
|
*/

return [

    'AL' => [
        ['component_key' => 'business_privilege_tax', 'label' => 'Alabama business privilege tax', 'amount_cents' => null, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'manual'],
    ],
    'AK' => [
        ['component_key' => 'biennial_report', 'label' => 'Alaska biennial report fee', 'amount_cents' => 10000, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'AZ' => [],
    'AR' => [
        ['component_key' => 'annual_franchise_tax', 'label' => 'Arkansas annual franchise tax', 'amount_cents' => 15000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'CA' => [
        ['component_key' => 'franchise_tax_min', 'label' => 'California minimum franchise tax', 'amount_cents' => 80000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
        ['component_key' => 'statement_of_information', 'label' => 'California Statement of Information', 'amount_cents' => 2000, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'CO' => [
        ['component_key' => 'annual_report', 'label' => 'Colorado periodic report fee', 'amount_cents' => 2500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'CT' => [
        ['component_key' => 'annual_report', 'label' => 'Connecticut annual report fee', 'amount_cents' => 8000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'DE' => [
        ['component_key' => 'annual_franchise_tax', 'label' => 'Delaware annual franchise tax', 'amount_cents' => 30000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'FL' => [
        ['component_key' => 'annual_report', 'label' => 'Florida annual report fee', 'amount_cents' => 13875, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'GA' => [
        ['component_key' => 'annual_report', 'label' => 'Georgia annual registration fee', 'amount_cents' => 6000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'HI' => [
        ['component_key' => 'annual_report', 'label' => 'Hawaii annual report fee', 'amount_cents' => 1250, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'ID' => [],
    'IL' => [
        ['component_key' => 'annual_report', 'label' => 'Illinois annual report fee', 'amount_cents' => 7500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'IN' => [
        ['component_key' => 'biennial_report', 'label' => 'Indiana biennial report fee', 'amount_cents' => 3200, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'IA' => [
        ['component_key' => 'biennial_report', 'label' => 'Iowa biennial report fee', 'amount_cents' => 3000, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'KS' => [
        ['component_key' => 'biennial_report', 'label' => 'Kansas biennial report fee', 'amount_cents' => 9000, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'KY' => [
        ['component_key' => 'annual_report', 'label' => 'Kentucky annual report fee', 'amount_cents' => 1500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'LA' => [
        ['component_key' => 'annual_report', 'label' => 'Louisiana annual report fee', 'amount_cents' => 3000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'ME' => [
        ['component_key' => 'annual_report', 'label' => 'Maine annual report fee', 'amount_cents' => 8500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'MD' => [
        ['component_key' => 'annual_report', 'label' => 'Maryland annual report fee', 'amount_cents' => 30000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'MA' => [
        ['component_key' => 'annual_report', 'label' => 'Massachusetts annual report fee', 'amount_cents' => 50000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'MI' => [
        ['component_key' => 'annual_report', 'label' => 'Michigan annual report fee', 'amount_cents' => 2500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'MN' => [],
    'MS' => [],
    'MO' => [],
    'MT' => [],
    'NE' => [
        ['component_key' => 'biennial_report', 'label' => 'Nebraska biennial report fee', 'amount_cents' => 2500, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'NV' => [
        ['component_key' => 'annual_report', 'label' => 'Nevada annual list & business license fee', 'amount_cents' => 35000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'NH' => [
        ['component_key' => 'annual_report', 'label' => 'New Hampshire annual report fee', 'amount_cents' => 10000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'NJ' => [
        ['component_key' => 'annual_report', 'label' => 'New Jersey annual report fee', 'amount_cents' => 7500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'NM' => [],
    'NY' => [
        ['component_key' => 'biennial_report', 'label' => 'New York biennial statement fee', 'amount_cents' => 900, 'interval_years' => 2, 'first_cycle_due' => 2, 'charge_mode' => 'auto'],
    ],
    'NC' => [
        ['component_key' => 'annual_report', 'label' => 'North Carolina annual report fee', 'amount_cents' => 20300, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'ND' => [
        ['component_key' => 'annual_report', 'label' => 'North Dakota annual report fee', 'amount_cents' => 5000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'OH' => [],
    'OK' => [
        ['component_key' => 'annual_report', 'label' => 'Oklahoma annual certificate fee', 'amount_cents' => 2500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'OR' => [
        ['component_key' => 'annual_report', 'label' => 'Oregon annual report fee', 'amount_cents' => 10000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'PA' => [
        ['component_key' => 'annual_report', 'label' => 'Pennsylvania annual report fee', 'amount_cents' => 700, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'RI' => [
        ['component_key' => 'annual_report', 'label' => 'Rhode Island annual report fee', 'amount_cents' => 5000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'SC' => [],
    'SD' => [
        ['component_key' => 'annual_report', 'label' => 'South Dakota annual report fee', 'amount_cents' => 5500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'TN' => [
        ['component_key' => 'annual_report', 'label' => 'Tennessee annual report fee (minimum)', 'amount_cents' => 30000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'TX' => [
        ['component_key' => 'franchise_tax', 'label' => 'Texas franchise tax', 'amount_cents' => null, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'manual'],
    ],
    'UT' => [
        ['component_key' => 'annual_report', 'label' => 'Utah annual report fee', 'amount_cents' => 1800, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'VT' => [
        ['component_key' => 'annual_report', 'label' => 'Vermont annual report fee', 'amount_cents' => 4500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'VA' => [
        ['component_key' => 'annual_report', 'label' => 'Virginia annual registration fee', 'amount_cents' => 5000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'WA' => [
        ['component_key' => 'annual_report', 'label' => 'Washington annual report fee', 'amount_cents' => 7000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'WV' => [
        ['component_key' => 'annual_report', 'label' => 'West Virginia annual report fee', 'amount_cents' => 2500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'WI' => [
        ['component_key' => 'annual_report', 'label' => 'Wisconsin annual report fee', 'amount_cents' => 2500, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],
    'WY' => [
        ['component_key' => 'annual_report', 'label' => 'Wyoming annual report fee (minimum)', 'amount_cents' => 6000, 'interval_years' => 1, 'first_cycle_due' => 1, 'charge_mode' => 'auto'],
    ],

];

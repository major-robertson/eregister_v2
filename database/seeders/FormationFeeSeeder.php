<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

/**
 * Formation pricing catalog.
 *
 * LLC formation is billed as a $299/yr membership subscription (yearly
 * filings, registered agent, formation, EIN, etc.) PLUS a one-time state
 * filing fee that varies by state. Each row here is the source of truth for
 * the charge amount; the per-state fees are charged via an inline-amount
 * Stripe Checkout line item (no per-state Stripe Product/Price objects), so
 * their stripe_price_id_* columns stay null. Only the membership has a real
 * recurring Stripe Price (set STRIPE_PRICE_LLC in .env).
 */
class FormationFeeSeeder extends Seeder
{
    /**
     * One-time state filing fees in whole USD ("Form LLC once").
     *
     * @var array<string, int>
     */
    private const STATE_FILING_FEES = [
        'AL' => 200, 'AK' => 250, 'AZ' => 50, 'AR' => 45, 'CA' => 70,
        'CO' => 50, 'CT' => 120, 'DE' => 110, 'FL' => 125, 'GA' => 100,
        'HI' => 51, 'ID' => 100, 'IL' => 150, 'IN' => 75, 'IA' => 50,
        'KS' => 85, 'KY' => 40, 'LA' => 100, 'ME' => 175, 'MD' => 100,
        'MA' => 500, 'MI' => 50, 'MN' => 155, 'MS' => 50, 'MO' => 50,
        'MT' => 35, 'NE' => 100, 'NV' => 425, 'NH' => 100, 'NJ' => 125,
        'NM' => 50, 'NY' => 200, 'NC' => 125, 'ND' => 135, 'OH' => 99,
        'OK' => 100, 'OR' => 100, 'PA' => 125, 'RI' => 150, 'SC' => 110,
        'SD' => 150, 'TN' => 300, 'TX' => 300, 'UT' => 59, 'VT' => 155,
        'VA' => 100, 'WA' => 200, 'WV' => 100, 'WI' => 130, 'WY' => 100,
    ];

    public function run(): void
    {
        // Membership: $299/yr recurring. amount_cents is for display; the
        // recurring charge uses the real Stripe Price below (test vs live
        // selected by the active key via Price::stripePriceId()).
        Price::updateOrCreate(
            [
                'product_family' => 'formation',
                'product_key' => 'llc',
                'variant_key' => 'membership',
                'billing_type' => 'subscription',
            ],
            [
                'amount_cents' => 29900,
                'currency' => 'usd',
                'interval' => 'year',
                'interval_count' => 1,
                'stripe_price_id_test' => 'price_1TmeLHCWSBPRiUNwXcXq2Q1R',
                'stripe_price_id_live' => 'price_1TmeLxCWSBPRiUNwUla8HGZk',
                'active' => true,
            ]
        );

        // One-time state filing fees: one row per state, inline-amount charge.
        foreach (self::STATE_FILING_FEES as $stateCode => $dollars) {
            Price::updateOrCreate(
                [
                    'product_family' => 'formation',
                    'product_key' => 'llc',
                    'variant_key' => $stateCode,
                    'billing_type' => 'one_time',
                ],
                [
                    'amount_cents' => $dollars * 100,
                    'currency' => 'usd',
                    'active' => true,
                ]
            );
        }
    }
}

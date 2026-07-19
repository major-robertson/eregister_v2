<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class LienWaiverPriceSeeder extends Seeder
{
    /**
     * Lien Waiver Pro: $99/month or $990/year subscription (two months free
     * on yearly), unlimited waivers. Recurring subscriptions require real
     * Stripe recurring Price IDs — Price::stripePriceId() picks test vs live
     * from the Stripe secret-key prefix at checkout time.
     */
    public function run(): void
    {
        $prices = [
            'monthly' => [
                'amount_cents' => 9900,
                'interval' => 'month',
                'stripe_price_id_test' => 'price_1TuzJ9CWSBPRiUNwflSmXKDP',
                'stripe_price_id_live' => 'price_1TuzJeCWSBPRiUNwRqbKd1VI',
            ],
            'yearly' => [
                'amount_cents' => 99000,
                'interval' => 'year',
                'stripe_price_id_test' => 'price_1TuzJ9CWSBPRiUNwSkcStRZX',
                'stripe_price_id_live' => 'price_1TuzJeCWSBPRiUNwppS4jfV2',
            ],
        ];

        foreach ($prices as $variant => $price) {
            Price::updateOrCreate(
                [
                    'product_family' => 'lien',
                    'product_key' => 'lien_waiver',
                    'variant_key' => $variant,
                    'billing_type' => 'subscription',
                ],
                [
                    'amount_cents' => $price['amount_cents'],
                    'currency' => 'usd',
                    'interval' => $price['interval'],
                    'interval_count' => 1,
                    'stripe_price_id_test' => $price['stripe_price_id_test'],
                    'stripe_price_id_live' => $price['stripe_price_id_live'],
                    'active' => true,
                ]
            );
        }
    }
}

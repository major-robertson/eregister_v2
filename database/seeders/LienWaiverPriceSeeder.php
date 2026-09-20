<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class LienWaiverPriceSeeder extends Seeder
{
    /**
     * Lien Waiver Pro: $49/month or $490/year per seat (two months free on
     * yearly). Recurring subscriptions require real Stripe recurring Price
     * IDs — Price::stripePriceId() picks test vs live from the Stripe
     * secret-key prefix at checkout time.
     *
     * The launch prices ($99 / $990) stay as inactive rows: checkout only
     * resolves active rows, but a subscription still billed on a launch price
     * needs its row so the seat manager quotes what it actually pays, the
     * admin MRR maps its Stripe price, and its renewals find a price row.
     */
    public function run(): void
    {
        $prices = [
            'monthly' => [
                'amount_cents' => 4900,
                'interval' => 'month',
                'stripe_price_id_test' => 'price_1UHkvVCWSBPRiUNwx4OvfQi4',
                'stripe_price_id_live' => 'price_1UHkwpCWSBPRiUNwlTmD7jdG',
                'active' => true,
            ],
            'yearly' => [
                'amount_cents' => 49000,
                'interval' => 'year',
                'stripe_price_id_test' => 'price_1UHkvjCWSBPRiUNwBbqttnxf',
                'stripe_price_id_live' => 'price_1UHkxLCWSBPRiUNwDz8OF8SM',
                'active' => true,
            ],
            'monthly_launch_99' => [
                'amount_cents' => 9900,
                'interval' => 'month',
                'stripe_price_id_test' => 'price_1TuzJ9CWSBPRiUNwflSmXKDP',
                'stripe_price_id_live' => 'price_1TuzJeCWSBPRiUNwRqbKd1VI',
                'active' => false,
            ],
            'yearly_launch_990' => [
                'amount_cents' => 99000,
                'interval' => 'year',
                'stripe_price_id_test' => 'price_1TuzJ9CWSBPRiUNwSkcStRZX',
                'stripe_price_id_live' => 'price_1TuzJeCWSBPRiUNwppS4jfV2',
                'active' => false,
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
                    'active' => $price['active'],
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prices = [
            [
                'product_key' => 'prelim_notice',
                'variant_key' => 'self_serve',
                'amount_cents' => 2900,
                'stripe_price_id_test' => 'price_1StA1DCWSBPRiUNwKIz7q3PS',
                'stripe_price_id_live' => 'price_1StA3qCWSBPRiUNwvKuGcBMR',
            ],
            [
                'product_key' => 'prelim_notice',
                'variant_key' => 'full_service',
                'amount_cents' => 4900,
                'stripe_price_id_test' => 'price_1StA1uCWSBPRiUNwe5VGMj17',
                'stripe_price_id_live' => 'price_1StA3qCWSBPRiUNwFFjhTZPX',
            ],
            [
                'product_key' => 'noi',
                'variant_key' => 'self_serve',
                'amount_cents' => 4900,
                'stripe_price_id_test' => 'price_1StA2bCWSBPRiUNwgyE977OV',
                'stripe_price_id_live' => 'price_1StA3XCWSBPRiUNw8CCcRinM',
            ],
            [
                'product_key' => 'noi',
                'variant_key' => 'full_service',
                'amount_cents' => 9900,
                'stripe_price_id_test' => 'price_1StA3RCWSBPRiUNw3pxkVW3s',
                'stripe_price_id_live' => 'price_1StA3XCWSBPRiUNw5iTTsRzo',
            ],
            [
                'product_key' => 'mechanics_lien',
                'variant_key' => 'self_serve',
                'amount_cents' => 9900,
                'stripe_price_id_test' => 'price_1StA4OCWSBPRiUNwBTqnqrri',
                'stripe_price_id_live' => 'price_1StA4yCWSBPRiUNwg8mpkH8y',
            ],
            [
                'product_key' => 'mechanics_lien',
                'variant_key' => 'full_service',
                'amount_cents' => 29900,
                'stripe_price_id_test' => 'price_1StA4rCWSBPRiUNwpclMp59I',
                'stripe_price_id_live' => 'price_1StA4yCWSBPRiUNwyWx1BZuC',
            ],
            [
                'product_key' => 'lien_release',
                'variant_key' => 'self_serve',
                'amount_cents' => 4900,
                'stripe_price_id_test' => 'price_1StA5NCWSBPRiUNwYn33l1hH',
                'stripe_price_id_live' => 'price_1StA5sCWSBPRiUNwp1jssCoU',
            ],
            [
                'product_key' => 'lien_release',
                'variant_key' => 'full_service',
                'amount_cents' => 9900,
                'stripe_price_id_test' => 'price_1StA5nCWSBPRiUNw34sAWKlm',
                'stripe_price_id_live' => 'price_1StA5sCWSBPRiUNwSmr6LOTO',
            ],
        ];

        foreach ($prices as $price) {
            Price::updateOrCreate(
                [
                    'product_family' => 'lien',
                    'product_key' => $price['product_key'],
                    'variant_key' => $price['variant_key'],
                    'billing_type' => 'one_time',
                ],
                [
                    'amount_cents' => $price['amount_cents'],
                    'currency' => 'usd',
                    'stripe_price_id_test' => $price['stripe_price_id_test'],
                    'stripe_price_id_live' => $price['stripe_price_id_live'],
                    'active' => true,
                ]
            );
        }
    }
}

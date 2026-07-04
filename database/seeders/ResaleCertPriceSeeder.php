<?php

namespace Database\Seeders;

use App\Models\Price;
use Illuminate\Database\Seeder;

class ResaleCertPriceSeeder extends Seeder
{
    /**
     * Resale Certificate Generator: $297/year subscription, unlimited
     * certificate generation. The Stripe recurring Price is REQUIRED for
     * live checkout (subscriptions can't charge an inline amount) — these
     * ids are the original TaxResaleCertificate product's prices, which
     * live in the same Stripe account.
     */
    public function run(): void
    {
        Price::updateOrCreate(
            [
                'product_family' => 'resale_cert',
                'product_key' => 'resale_cert_generator',
                'variant_key' => 'default',
                'billing_type' => 'subscription',
            ],
            [
                'amount_cents' => 29700,
                'currency' => 'usd',
                'interval' => 'year',
                'interval_count' => 1,
                'stripe_price_id_test' => 'price_1SbSAXCWSBPRiUNwd1jbZzxz',
                'stripe_price_id_live' => 'price_1SbS9uCWSBPRiUNw3wZ7vOsv',
                'active' => true,
            ]
        );
    }
}

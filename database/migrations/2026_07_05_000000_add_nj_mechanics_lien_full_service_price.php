<?php

use App\Models\Price;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Land the New Jersey full-service mechanics-lien price ($899) in the
     * `prices` table. Charged via inline-amount PaymentIntent, so no Stripe
     * Price ID is needed. Idempotent (updateOrCreate on the composite unique
     * key); PriceSeeder derives the same row from config for fresh installs.
     */
    public function up(): void
    {
        Price::updateOrCreate(
            [
                'product_family' => 'lien',
                'product_key' => 'mechanics_lien',
                'variant_key' => 'NJ_full_service',
                'billing_type' => 'one_time',
            ],
            [
                'amount_cents' => 89900,
                'currency' => 'usd',
                'active' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'mechanics_lien')
            ->where('variant_key', 'NJ_full_service')
            ->where('billing_type', 'one_time')
            ->delete();
    }
};

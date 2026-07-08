<?php

use App\Models\Price;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Land the lien-waiver subscription prices ($99/mo, $990/yr, i.e. two
     * months free on yearly) in the `prices` table. Recurring subscriptions require
     * real Stripe Price IDs, sourced from env via config/lien_waivers.php
     * (nullable so keyless local dev still boots; checkout throws a clear
     * error when they are missing in a keyed environment). Idempotent
     * (updateOrCreate on the composite unique key).
     */
    public function up(): void
    {
        foreach (['monthly' => 'month', 'yearly' => 'year'] as $variant => $interval) {
            $config = config("lien_waivers.prices.{$variant}");

            Price::updateOrCreate(
                [
                    'product_family' => 'lien',
                    'product_key' => 'lien_waiver',
                    'variant_key' => $variant,
                    'billing_type' => 'subscription',
                ],
                [
                    'amount_cents' => $config['amount_cents'],
                    'currency' => 'usd',
                    'interval' => $interval,
                    'interval_count' => 1,
                    'stripe_price_id_test' => $config['stripe_price_id_test'] ?? null,
                    'stripe_price_id_live' => $config['stripe_price_id_live'] ?? null,
                    'active' => true,
                ]
            );
        }
    }

    /**
     * Deactivate (don't delete): Payments hold price_id references once
     * anyone has checked out, so the rows must survive a rollback.
     */
    public function down(): void
    {
        Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->update(['active' => false]);
    }
};

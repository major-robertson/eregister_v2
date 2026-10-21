<?php

use App\Models\Price;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The overnight original fee (EREG-142): a full-service lien or release
     * in a county that needs the original signed document pays it on top of
     * the service price, in the same inline-amount PaymentIntent (no Stripe
     * Price ID). The amount comes from config lien.overnight_original_fee_cents.
     * Idempotent; PriceSeeder carries the same row.
     */
    public function up(): void
    {
        Price::updateOrCreate(
            [
                'product_family' => 'lien',
                'product_key' => 'overnight_original',
                'variant_key' => 'standard',
                'billing_type' => 'one_time',
            ],
            [
                'amount_cents' => (int) config('lien.overnight_original_fee_cents', 2500),
                'currency' => 'usd',
                'active' => true,
            ]
        );
    }

    public function down(): void
    {
        Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'overnight_original')
            ->where('billing_type', 'one_time')
            ->delete();
    }
};

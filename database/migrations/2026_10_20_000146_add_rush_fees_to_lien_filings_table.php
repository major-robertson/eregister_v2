<?php

use App\Models\Price;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rush fees for full-service lien orders close to their deadline
     * (EREG-146). The filing keeps the tier it was charged (null, rush or
     * expedite), whether the customer added Rush by choice outside the
     * required tiers, and when the customer acknowledged the expedite terms; the
     * two fees land in the catalog as lien/rush_fee/{tier}, charged on top of
     * the service price in the same inline-amount PaymentIntent (no Stripe
     * Price ID). Idempotent; PriceSeeder carries the same rows.
     */
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->string('rush_tier', 16)->nullable()->after('deadline_block_shown_at'); // rush, expedite
            $table->boolean('rush_elected')->default(false)->after('rush_tier'); // the customer added Rush outside the required tiers
            $table->timestamp('rush_acknowledged_at')->nullable()->after('rush_elected');
        });

        foreach (config('lien.rush_fees', []) as $tier => $amountCents) {
            Price::updateOrCreate(
                [
                    'product_family' => 'lien',
                    'product_key' => 'rush_fee',
                    'variant_key' => $tier,
                    'billing_type' => 'one_time',
                ],
                [
                    'amount_cents' => $amountCents,
                    'currency' => 'usd',
                    'active' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn(['rush_tier', 'rush_elected', 'rush_acknowledged_at']);
        });

        Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'rush_fee')
            ->where('billing_type', 'one_time')
            ->delete();
    }
};

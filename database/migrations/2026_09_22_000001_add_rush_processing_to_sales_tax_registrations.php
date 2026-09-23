<?php

use App\Models\Price;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rush processing for sales tax registrations: the customer can pay $99
     * on top of the per-state fee to have the registration filed within
     * 2 business days instead of 5. The choice is stamped on the application
     * (admin board + receipt read it) and the price lands in the catalog.
     * Charged as part of the inline-amount PaymentIntent, so no Stripe Price
     * ID is needed. Idempotent; PriceSeeder carries the same row.
     */
    public function up(): void
    {
        Schema::table('form_applications', function (Blueprint $table) {
            $table->timestamp('rush_requested_at')->nullable()->after('paid_at');
        });

        Price::updateOrCreate(
            [
                'product_family' => 'tax',
                'product_key' => 'sales_tax_permit',
                'variant_key' => 'rush',
                'billing_type' => 'one_time',
            ],
            [
                'amount_cents' => 9900,
                'currency' => 'usd',
                'active' => true,
            ]
        );
    }

    public function down(): void
    {
        Schema::table('form_applications', function (Blueprint $table) {
            $table->dropColumn('rush_requested_at');
        });

        Price::query()
            ->where('product_family', 'tax')
            ->where('product_key', 'sales_tax_permit')
            ->where('variant_key', 'rush')
            ->where('billing_type', 'one_time')
            ->delete();
    }
};

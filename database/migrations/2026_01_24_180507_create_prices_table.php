<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->string('product_family', 50);       // 'lien', 'llc', 'tax', 'saas'
            $table->string('product_key', 100);         // 'prelim_notice', 'llc_formation'
            $table->string('variant_key', 50)->default('default'); // 'self_serve', 'full_service'
            $table->string('billing_type', 20)->default('one_time'); // 'one_time', 'subscription'
            $table->unsignedInteger('amount_cents')->nullable(); // Nullable for subscriptions
            $table->string('currency', 3)->default('usd');

            // Subscription interval fields (nullable for one-time prices)
            $table->string('interval', 10)->nullable();          // 'month', 'year'
            $table->unsignedSmallInteger('interval_count')->nullable(); // 1, 3, 12, etc.

            $table->boolean('active')->default(true);
            $table->string('stripe_price_id_test')->nullable();
            $table->string('stripe_price_id_live')->nullable();
            $table->string('stripe_lookup_key')->nullable();
            $table->json('meta')->nullable();           // Future flexibility
            $table->timestamps();

            $table->unique(['product_family', 'product_key', 'variant_key', 'billing_type'], 'prices_composite_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};

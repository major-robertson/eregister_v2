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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            // Polymorphic (creates purchasable_type, purchasable_id + index automatically)
            $table->morphs('purchasable');

            // Link to Price catalog (snapshot for audit)
            $table->foreignId('price_id')->nullable()->constrained('prices');

            // Payment provider (future-proofing)
            $table->string('provider', 20)->default('stripe');
            $table->boolean('livemode')->default(false); // sk_live_ vs sk_test_

            // Stripe identifiers
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_subscription_id')->nullable(); // Future
            $table->string('stripe_invoice_id')->nullable();      // Future

            // Payment details (snapshot at creation time)
            $table->unsignedBigInteger('amount_cents'); // NOT NULL - for subscriptions, each row = invoice payment
            $table->string('currency', 3)->default('usd');
            $table->string('status', 30)->default('initiated');
            $table->string('billing_type', 20)->default('one_time'); // 'one_time', 'subscription'

            // Error handling
            $table->text('error_message')->nullable();
            $table->boolean('requires_manual_review')->default(false);

            // Metadata
            $table->json('meta')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Note: morphs() already creates index on (purchasable_type, purchasable_id)
            $table->index('business_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

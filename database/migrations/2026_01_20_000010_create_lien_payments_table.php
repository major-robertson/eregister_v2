<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filing_id')->constrained('lien_filings')->cascadeOnDelete();

            // Stripe identifiers
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable();

            // Payment details
            $table->unsignedBigInteger('amount_cents');
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('pending'); // pending, paid, failed, refunded

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index('filing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger of recurring (year 2+) state fee items added to LLC membership
     * renewal invoices. The unique key bridges the gap between the
     * invoice.upcoming and invoice.created webhooks (which can be days apart,
     * beyond Stripe's ~24h idempotency window) so a fee is added at most once
     * per subscription/cycle/component.
     */
    public function up(): void
    {
        Schema::create('llc_renewal_fee_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('form_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_subscription_id');
            $table->string('state', 2)->nullable();
            $table->unsignedInteger('cycle_number');
            $table->string('component_key', 64);
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('usd');
            $table->string('stripe_invoice_item_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();
            $table->string('status', 20)->default('pending'); // pending, added, paid
            $table->timestamp('charged_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['stripe_subscription_id', 'cycle_number', 'component_key'],
                'llc_renewal_fee_items_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llc_renewal_fee_items');
    }
};

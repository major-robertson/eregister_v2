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
        Schema::create('marketing_mailings', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('lead_campaign_id')->constrained('marketing_lead_campaigns')->cascadeOnDelete();
            $table->foreignId('campaign_step_id')->constrained('marketing_campaign_steps')->cascadeOnDelete();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('provider')->nullable(); // postgrid, lob, etc.
            $table->string('provider_id')->nullable(); // Letter/postcard ID from provider
            $table->string('provider_status')->nullable(); // ready, printing, processed_for_delivery, completed, cancelled
            $table->json('provider_payload')->nullable(); // Raw response snapshot
            $table->timestamp('executed_at')->nullable(); // When provider order was created
            $table->timestamp('delivered_at')->nullable(); // From provider status updates
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['lead_campaign_id', 'campaign_step_id']);
            $table->index(['provider', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_mailings');
    }
};

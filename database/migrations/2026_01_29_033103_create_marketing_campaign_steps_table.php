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
        Schema::create('marketing_campaign_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->string('type'); // letter, postcard (later: email, sms, audience_sync)
            $table->unsignedInteger('delay_days')->default(0); // Days after previous step executed
            $table->string('template_key')->nullable(); // Internal identifier (e.g., liens_letter_v1)
            $table->json('provider_template_ref')->nullable(); // Provider-specific template references
            $table->json('mailpiece_options')->nullable(); // Provider-specific options (e.g., size for postcards)
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_campaign_steps');
    }
};

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
        Schema::create('marketing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->nullable()->constrained('marketing_visits')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->nullOnDelete();
            $table->foreignId('campaign_step_id')->nullable()->constrained('marketing_campaign_steps')->nullOnDelete();
            $table->foreignId('mailing_id')->nullable()->constrained('marketing_mailings')->nullOnDelete();
            $table->string('event_type'); // cta_click, call_click, form_submit, etc.
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['lead_id', 'occurred_at']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_events');
    }
};

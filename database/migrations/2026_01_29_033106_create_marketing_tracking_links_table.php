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
        Schema::create('marketing_tracking_links', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('token', 20)->unique(); // 10-16 chars base62
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('marketing_campaigns')->nullOnDelete();
            $table->foreignId('campaign_step_id')->nullable()->constrained('marketing_campaign_steps')->nullOnDelete();
            $table->foreignId('mailing_id')->nullable()->constrained('marketing_mailings')->nullOnDelete();
            $table->string('destination_type'); // lead_landing, url
            $table->string('destination'); // Slug or full URL
            $table->string('qr_code_path')->nullable(); // S3 path to SVG
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('mailing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_tracking_links');
    }
};

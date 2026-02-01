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
        Schema::create('marketing_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tracking_link_id')->nullable()->constrained('marketing_tracking_links')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('marketing_leads')->nullOnDelete();
            $table->foreignId('mailing_id')->nullable()->constrained('marketing_mailings')->nullOnDelete();
            $table->string('source'); // qr_scan, direct, referral
            $table->string('ip_address', 45)->nullable(); // IPv6 compatible
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['lead_id', 'visited_at']);
            $table->index(['tracking_link_id', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_visits');
    }
};

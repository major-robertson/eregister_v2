<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Repair path: an earlier run of this migration failed adding the
        // unique index (auto-generated name exceeded MySQL's 64-char limit),
        // leaving the table without it. Fresh databases skip straight to create.
        if (Schema::hasTable('lien_waiver_notification_logs')) {
            Schema::table('lien_waiver_notification_logs', function (Blueprint $table) {
                $table->unique(['lien_waiver_id', 'type', 'interval_days'], 'lien_waiver_notification_logs_dedup_unique');
            });

            return;
        }

        Schema::create('lien_waiver_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('lien_waiver_id')->constrained('lien_waivers')->cascadeOnDelete();
            // e.g. "signature_reminder"; interval_days keys the reminder step.
            $table->string('type');
            $table->unsignedInteger('interval_days')->nullable();
            $table->timestamp('sent_at');

            $table->unique(['lien_waiver_id', 'type', 'interval_days'], 'lien_waiver_notification_logs_dedup_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_waiver_notification_logs');
    }
};

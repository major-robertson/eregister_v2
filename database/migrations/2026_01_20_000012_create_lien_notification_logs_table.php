<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_deadline_id')->constrained('lien_project_deadlines')->cascadeOnDelete();
            $table->integer('interval_days'); // 14, 7, 3, 1, 0
            $table->timestamp('sent_at');

            // Prevent duplicate sends
            $table->unique(['project_deadline_id', 'interval_days'], 'lien_notification_logs_unique');
            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_notification_logs');
    }
};

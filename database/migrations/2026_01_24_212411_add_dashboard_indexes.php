<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add indexes to optimize dashboard queries.
     */
    public function up(): void
    {
        // Index for activity feed ordering on filing events
        Schema::table('lien_filing_events', function (Blueprint $table) {
            $table->index('created_at');
        });

        // Index for activity feed ordering on notification logs
        Schema::table('lien_notification_logs', function (Blueprint $table) {
            $table->index('sent_at');
        });

        // Index for deadline due_date filtering (existing index is status,due_date composite)
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_filing_events', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('lien_notification_logs', function (Blueprint $table) {
            $table->dropIndex(['sent_at']);
        });

        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
        });
    }
};

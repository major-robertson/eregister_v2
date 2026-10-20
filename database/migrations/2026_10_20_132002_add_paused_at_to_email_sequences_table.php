<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_sequences', function (Blueprint $table) {
            // A paused sequence sends nothing (email:process-sequences skips
            // it) but is not over: resume() or a restart picks it up again.
            // First use: a filing's reminders while the customer's email
            // waits on us (EREG-132).
            $table->timestamp('paused_at')->nullable()->after('suppression_reason');
            $table->string('pause_reason')->nullable()->after('paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_sequences', function (Blueprint $table) {
            $table->dropColumn(['paused_at', 'pause_reason']);
        });
    }
};

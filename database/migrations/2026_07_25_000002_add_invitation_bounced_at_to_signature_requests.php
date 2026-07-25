<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guest signers (waiver counterparties) have no user account, so a
     * bounced invitation can't be flagged on `users`. Flag the request
     * itself: reminders stop, the waiver page warns the owner, and the
     * audit timeline records the bounce.
     */
    public function up(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->timestamp('invitation_bounced_at')->nullable()->after('invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->dropColumn('invitation_bounced_at');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When Postmark hard-bounces or spam-flags an address it lands on their
     * suppression list and every future send 406s. Record that here so the
     * app stops queueing mail to the address and the portal can prompt the
     * user to fix it. Cleared automatically when the user changes their email.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_bounced_at')->nullable()->after('unsubscribed_from_all_emails_at');
            $table->string('email_bounce_reason')->nullable()->after('email_bounced_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_bounced_at', 'email_bounce_reason']);
        });
    }
};

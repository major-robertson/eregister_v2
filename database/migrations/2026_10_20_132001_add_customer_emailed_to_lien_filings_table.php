<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            // The customer emailed us about this filing (Front webhook,
            // EREG-132): when, and the Front conversation to read it in.
            // Cleared by any status change and by "Still waiting". The
            // message itself stays in Front.
            $table->timestamp('customer_emailed_at')->nullable()->after('client_request_json');
            $table->string('customer_emailed_front_url')->nullable()->after('customer_emailed_at');
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn(['customer_emailed_at', 'customer_emailed_front_url']);
        });
    }
};

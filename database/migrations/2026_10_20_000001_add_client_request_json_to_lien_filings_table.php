<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            // The latest request we emailed the customer when the filing moved
            // to Awaiting Client / E-Signature / Notary: {text, requested_at,
            // requested_by_user_id, attachment_media_id, status, resolved_at}.
            // resolved_at is set when the filing leaves that status; the text
            // stays for history. Earlier requests live on the filing events.
            $table->json('client_request_json')->nullable()->after('document_details_json');
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn('client_request_json');
        });
    }
};

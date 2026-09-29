<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            // Per-document facts the generated lien documents need but the
            // application never asked for: signer and title, license number,
            // contract date and type, prior-notice service date and method,
            // months of work (Texas), owner interest and block/lot (New York),
            // the recorded lien a release refers to. Admin-edited on the filing
            // page and audited; never part of payload_json, so editing it does
            // not re-sync the fulfillment snapshot.
            $table->json('document_details_json')->nullable()->after('parties_snapshot_json');
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn('document_details_json');
        });
    }
};

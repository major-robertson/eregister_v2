<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Property identification (EREG-136). A mechanics lien or lien release
     * needs the legal description or parcel number; a customer who chose
     * "I need help finding it" in the wizard pays without it. The filing
     * keeps when they chose it, so payment moves the filing to Awaiting
     * Client and the filing page asks them to enter it.
     */
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->timestamp('property_help_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn('property_help_requested_at');
        });
    }
};

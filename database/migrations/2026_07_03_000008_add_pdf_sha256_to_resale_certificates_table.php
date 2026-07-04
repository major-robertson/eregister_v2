<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resale_certificates', function (Blueprint $table) {
            // Integrity evidence: hash of the stored PDF bytes, recorded at
            // generation time and echoed into the audit trail.
            $table->string('pdf_sha256', 64)->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('resale_certificates', function (Blueprint $table) {
            $table->dropColumn('pdf_sha256');
        });
    }
};

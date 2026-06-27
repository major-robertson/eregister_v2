<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist the two role-capture answers ("what did you provide" and
     * "who hired you") as form facts. `claimant_type` stays the canonical
     * value used by all lien logic and is re-derived from these on every
     * save; these columns preserve the exact wizard answers for edit
     * round-tripping, auditability, and future document generation.
     * Nullable: legacy rows leave them null and reverse-derive on edit.
     */
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('lien_projects', 'provided_type')) {
                $table->string('provided_type')->nullable()->after('claimant_type');
            }
            if (! Schema::hasColumn('lien_projects', 'hired_by')) {
                $table->string('hired_by')->nullable()->after('provided_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn(['provided_type', 'hired_by']);
        });
    }
};

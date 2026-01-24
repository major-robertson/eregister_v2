<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            // Add noc_status enum with default 'unknown'
            $table->string('noc_status', 20)->default('unknown')->after('noc_recorded_date');

            // Rename noc_recorded_date to noc_recorded_at for consistency
            $table->renameColumn('noc_recorded_date', 'noc_recorded_at');

            // Drop contract_date column
            $table->dropColumn('contract_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            // Restore contract_date
            $table->date('contract_date')->nullable()->after('uncompleted_work_cents');

            // Rename noc_recorded_at back to noc_recorded_date
            $table->renameColumn('noc_recorded_at', 'noc_recorded_date');

            // Drop noc_status
            $table->dropColumn('noc_status');
        });
    }
};

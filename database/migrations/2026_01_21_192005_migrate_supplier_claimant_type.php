<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Convert legacy 'supplier' claimant_type to 'supplier_to_contractor' as default.
     */
    public function up(): void
    {
        DB::table('lien_projects')
            ->where('claimant_type', 'supplier')
            ->update(['claimant_type' => 'supplier_to_contractor']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lien_projects')
            ->where('claimant_type', 'supplier_to_contractor')
            ->update(['claimant_type' => 'supplier']);
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mapping from old abbreviated values to new enum values.
     */
    private const VALUE_MAPPINGS = [
        'sub' => 'subcontractor',
        'subsub' => 'sub_sub_contractor',
        'supplier_owner' => 'supplier_to_owner',
        'supplier_gc' => 'supplier_to_contractor',
        'supplier_sub' => 'supplier_to_subcontractor',
        // 'gc' and 'other' remain unchanged
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::VALUE_MAPPINGS as $oldValue => $newValue) {
            DB::table('lien_deadline_rules')
                ->where('claimant_type', $oldValue)
                ->update(['claimant_type' => $newValue]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (self::VALUE_MAPPINGS as $oldValue => $newValue) {
            DB::table('lien_deadline_rules')
                ->where('claimant_type', $newValue)
                ->update(['claimant_type' => $oldValue]);
        }
    }
};

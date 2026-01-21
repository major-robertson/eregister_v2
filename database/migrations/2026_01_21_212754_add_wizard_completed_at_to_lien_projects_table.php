<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->timestamp('wizard_completed_at')->nullable()->after('noc_recorded_date');
        });

        // Mark all existing projects as completed
        DB::table('lien_projects')->update(['wizard_completed_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn('wizard_completed_at');
        });
    }
};

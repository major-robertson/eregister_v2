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
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dateTime('completed_externally_at')->nullable()->after('completed_filing_id');
            $table->date('external_filed_at')->nullable()->after('completed_externally_at');
            $table->string('external_completion_note', 500)->nullable()->after('external_filed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropColumn([
                'completed_externally_at',
                'external_filed_at',
                'external_completion_note',
            ]);
        });
    }
};

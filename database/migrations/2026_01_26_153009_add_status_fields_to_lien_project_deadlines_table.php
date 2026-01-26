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
            $table->string('status_reason')->nullable()->after('status');
            $table->json('status_meta')->nullable()->after('status_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropColumn(['status_reason', 'status_meta']);
        });
    }
};

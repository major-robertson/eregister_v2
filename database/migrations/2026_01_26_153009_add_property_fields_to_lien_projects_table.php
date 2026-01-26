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
            $table->timestamp('noc_filed_date')->nullable()->after('noc_recorded_at');
            $table->timestamp('prelim_notice_sent_at')->nullable()->after('noc_filed_date');
            $table->string('property_context')->default('unknown')->after('prelim_notice_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn(['noc_filed_date', 'prelim_notice_sent_at', 'property_context']);
        });
    }
};

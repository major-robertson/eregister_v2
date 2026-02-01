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
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('landing_key')->default('liens')->after('status');
        });

        // Increase token column size to accommodate slugs (up to 80 chars)
        Schema::table('marketing_tracking_links', function (Blueprint $table) {
            $table->string('token', 80)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn('landing_key');
        });

        Schema::table('marketing_tracking_links', function (Blueprint $table) {
            $table->string('token')->change();
        });
    }
};

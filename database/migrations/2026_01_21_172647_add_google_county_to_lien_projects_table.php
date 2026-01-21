<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            // Store the county as returned by Google Maps (immutable reference)
            $table->string('jobsite_county_google')->nullable()->after('jobsite_county');
        });
    }

    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn('jobsite_county_google');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Google Ads click id (?gclid=) captured first-touch like the UTM
            // columns. With the campaign / ad group / keyword UTMs it ties a
            // paying customer back to the ad click that brought them, which
            // Google Ads itself only reports in aggregate.
            $table->string('signup_gclid')->nullable()->after('signup_oppref');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_gclid');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reddit Ads click id (?rdt_cid=) captured first-touch like the
            // UTM columns; attached to Conversions API events for attribution.
            $table->string('signup_rdt_cid')->nullable()->after('signup_utm_content');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_rdt_cid');
        });
    }
};

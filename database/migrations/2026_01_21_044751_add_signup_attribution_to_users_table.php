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
        Schema::table('users', function (Blueprint $table) {
            $table->string('signup_landing_path')->nullable();
            $table->string('signup_referrer')->nullable();
            $table->string('signup_utm_source')->nullable();
            $table->string('signup_utm_medium')->nullable();
            $table->string('signup_utm_campaign')->nullable();
            $table->string('signup_utm_term')->nullable();
            $table->string('signup_utm_content')->nullable();
            $table->string('signup_ip', 45)->nullable();
            $table->string('signup_user_agent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'signup_landing_path',
                'signup_referrer',
                'signup_utm_source',
                'signup_utm_medium',
                'signup_utm_campaign',
                'signup_utm_term',
                'signup_utm_content',
                'signup_ip',
                'signup_user_agent',
            ]);
        });
    }
};

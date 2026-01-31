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
            $table->text('signup_landing_url')->nullable()->change();
            $table->text('signup_referrer')->nullable()->change();
            $table->text('signup_user_agent')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('signup_landing_url')->nullable()->change();
            $table->string('signup_referrer')->nullable()->change();
            $table->string('signup_user_agent')->nullable()->change();
        });
    }
};

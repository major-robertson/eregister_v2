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
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('mailing_address');
            $table->string('state_of_incorporation', 2)->nullable()->after('phone');
            $table->string('contractor_license_number')->nullable()->after('state_of_incorporation');
            $table->timestamp('lien_onboarding_completed_at')->nullable()->after('onboarding_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'state_of_incorporation',
                'contractor_license_number',
                'lien_onboarding_completed_at',
            ]);
        });
    }
};

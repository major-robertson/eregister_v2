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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();

            // Business profile data (shared across applications)
            $table->string('legal_name')->nullable();
            $table->string('dba_name')->nullable();
            $table->string('entity_type')->nullable();
            $table->json('business_address')->nullable();
            $table->json('mailing_address')->nullable();
            $table->json('responsible_people')->nullable(); // Non-sensitive fields only

            $table->timestamp('onboarding_completed_at')->nullable();

            // Laravel Cashier billable columns
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};

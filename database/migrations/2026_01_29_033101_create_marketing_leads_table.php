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
        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('slug')->unique();

            // Mailing address
            $table->string('mailing_address')->nullable();
            $table->string('mailing_address_2')->nullable();
            $table->string('mailing_city')->nullable();
            $table->string('mailing_state', 2)->nullable();
            $table->string('mailing_zip', 10)->nullable();

            // Business info
            $table->string('business_name')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();

            // Permit/record info
            $table->string('permit_or_external_id')->nullable();
            $table->string('record_id')->nullable();
            $table->date('record_date')->nullable();
            $table->string('raw_category')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();

            // Property address
            $table->string('property_address')->nullable();
            $table->string('property_address_2')->nullable();
            $table->string('property_city')->nullable();
            $table->string('property_state', 2)->nullable();
            $table->string('property_zip', 10)->nullable();

            // Meta
            $table->text('source_url')->nullable();
            $table->string('role')->nullable();

            $table->timestamps();

            $table->index('record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_leads');
    }
};

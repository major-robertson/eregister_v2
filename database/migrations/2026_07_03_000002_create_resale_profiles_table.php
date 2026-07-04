<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->unique()->constrained()->cascadeOnDelete();
            // Printed verbatim on certificates as the resale property description.
            $table->string('products_description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('mtc_enabled')->default(false);
            $table->string('default_expiration_rule', 30)->default('end_of_current_year');
            // Per-state overrides: {"TX": "never", "CA": "1_year_from_issue"}
            $table->json('state_expiration_rules')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_profiles');
    }
};

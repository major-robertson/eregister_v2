<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_tax_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('state_code', 2);
            // Encrypted at rest (model cast), so text — ciphertext outgrows the raw id.
            $table->text('tax_id');
            $table->boolean('is_home_state')->default(false);
            $table->timestamps();

            $table->unique(['business_id', 'state_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_tax_registrations');
    }
};

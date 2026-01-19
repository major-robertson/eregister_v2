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
        Schema::create('form_application_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_application_id')->constrained()->cascadeOnDelete();
            $table->string('state_code', 2);
            $table->string('status')->default('pending'); // pending, complete
            $table->string('current_step_key')->nullable();
            $table->json('data')->nullable(); // Only state-unique answers (encrypted sensitive fields)
            $table->string('data_hash')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['form_application_id', 'state_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_application_states');
    }
};

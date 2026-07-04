<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_state_rules', function (Blueprint $table) {
            $table->id();
            // 'MTC' and 'SST' uniform certificates live here as pseudo-states,
            // so the column is 3 chars, not 2.
            $table->string('state_code', 3)->unique();
            $table->string('state_name');
            $table->boolean('accepts_mtc')->default(false);
            $table->boolean('accepts_sst')->default(false);
            $table->boolean('accepts_out_of_state')->default(false);
            $table->boolean('allows_blanket')->default(true);
            $table->text('default_blanket_text')->nullable();
            $table->unsignedSmallInteger('expiration_months')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_state_rules');
    }
};

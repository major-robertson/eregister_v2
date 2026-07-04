<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // PNG on the resale-cert disk, drawn on a 500x100 canvas (the 5:1
            // ratio is baked into PDF stamping).
            $table->string('image_path');
            // Raw stroke coordinates captured at draw time (audit trail).
            $table->json('strokes_json')->nullable();
            $table->string('created_ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('is_current')->default(false);
            $table->boolean('agreed_to_terms')->default(false);
            $table->timestamp('agreed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_signatures');
    }
};

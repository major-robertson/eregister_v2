<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_signature_events', function (Blueprint $table) {
            $table->id();
            // Chain scope: one hash chain per business.
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('event_type', 40);
            // Referents are plain ids (no FKs): audit rows must outlive the
            // records they describe, and FK side effects (cascade/null-on-
            // delete) would mutate or destroy hashed rows.
            $table->unsignedBigInteger('resale_signature_id')->nullable();
            $table->unsignedBigInteger('resale_certificate_id')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at');
            $table->json('metadata_json')->nullable();
            $table->string('previous_event_hash', 64)->nullable();
            $table->string('event_hash', 64);
            $table->timestamp('created_at')->nullable();

            $table->index(['business_id', 'id']);
            $table->index('resale_certificate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_signature_events');
    }
};

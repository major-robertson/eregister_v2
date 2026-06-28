<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only, hash-chained audit log. Rows are never updated or deleted
        // (the model throws on both). Chain order is by id within a request.
        Schema::create('signature_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signature_request_id')->constrained('signature_requests')->cascadeOnDelete();
            $table->foreignId('signature_document_id')->nullable()->constrained('signature_documents')->nullOnDelete();

            $table->string('event_type');
            $table->string('actor_type')->nullable(); // signer | admin | system
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            // UTC. Second precision keeps the hashed serialization reproducible
            // across DB round-trips; chain order is by id, not by this column.
            $table->timestamp('occurred_at');
            $table->json('metadata_json')->nullable();

            // Hash chain. previous_event_hash is null only for the genesis event.
            $table->string('previous_event_hash', 64)->nullable();
            $table->string('event_hash', 64);

            // Append-only: created_at only, no updated_at.
            $table->timestamp('created_at')->nullable();

            $table->index(['signature_request_id', 'id']);
            $table->index('event_type');
            $table->unique('event_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_events');
    }
};

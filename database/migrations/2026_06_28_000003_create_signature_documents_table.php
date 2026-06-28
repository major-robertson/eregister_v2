<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_documents', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('signature_request_id')->constrained('signature_requests')->cascadeOnDelete();

            // Human Document ID shown to the signer, e.g. "DL-1001".
            $table->string('document_identifier');
            // "Demand Letter to Acme Corp (Owner)".
            $table->string('label');
            // Adapter handle back to the source record (e.g. a LienParty id).
            $table->string('recipient_ref')->nullable();
            // Full render-input snapshot at lock time — proves exactly what was
            // rendered even after the filing / parties / template later change.
            $table->json('document_snapshot_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);

            // sha256 of the locked (unsigned) PDF bytes.
            $table->string('locked_document_hash', 64)->nullable();
            $table->timestamp('locked_at')->nullable();

            // sha256 of the final signed PDF bytes.
            $table->string('signed_document_hash', 64)->nullable();
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();

            $table->unique(['signature_request_id', 'document_identifier'], 'sig_docs_request_identifier_unique');
            $table->index('signature_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_documents');
    }
};

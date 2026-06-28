<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            // Polymorphic target of the signature (alias e.g. "lien_filing").
            $table->morphs('signable');
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->foreignId('signer_user_id')->constrained('users')->cascadeOnDelete();
            // The document-type policy key (config esign.document_types.<key>).
            $table->string('document_signing_policy_key');

            // State machine. "Active" = pending|locking_documents|awaiting_signature|signing.
            $table->string('status')->default('pending');
            $table->text('failure_reason')->nullable();

            // Signer identity snapshots (captured at invite time).
            $table->string('signer_name_snapshot')->nullable();
            $table->string('signer_email_snapshot')->nullable();
            $table->string('signer_phone_snapshot')->nullable();

            // Intent + all legally-meaningful UI text the signer saw, plus the
            // exact list of letters they signed (document_list_snapshot).
            $table->text('intent_statement')->nullable();
            $table->json('presented_text_json')->nullable();

            $table->string('signature_method')->default('typed_name');
            $table->string('adopted_name')->nullable();
            // Snapshot of the signer's email_verified_at at completion time.
            $table->timestamp('email_verified_at_sign')->nullable();

            $table->foreignId('consent_id')->nullable()->constrained('esign_consents')->nullOnDelete();

            // Lifecycle stamps.
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('first_opened_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Active-session lookup for a given signable.
            $table->index(['signable_type', 'signable_id', 'status']);
            $table->index('signer_user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_filing_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filing_id')->constrained('lien_filings')->cascadeOnDelete();
            $table->foreignId('party_id')->nullable()->constrained('lien_parties')->nullOnDelete();

            // Delivery method
            $table->string('delivery_method')->nullable(); // certified_mail, registered_mail, personal_service

            // Address snapshot (immutable)
            $table->json('address_snapshot_json');

            // Tracking
            $table->string('tracking_number')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();

            $table->index('business_id');
            $table->index('filing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_filing_recipients');
    }
};

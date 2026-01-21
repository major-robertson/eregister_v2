<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_filing_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filing_id')->constrained('lien_filings')->cascadeOnDelete();
            $table->string('event_type'); // status_changed, note_added, document_uploaded, etc.
            $table->json('payload_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('business_id');
            $table->index('filing_id');
            $table->index(['filing_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_filing_events');
    }
};

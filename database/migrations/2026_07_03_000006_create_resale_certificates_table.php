<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resale_vendor_id')->constrained('resale_vendors')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            // 2-letter state, or 'MTC'/'SST' for uniform multi-state forms.
            $table->string('state_code', 3);
            $table->boolean('is_blanket')->default(true);
            $table->text('item_description')->nullable();
            // Frozen at generation time so the PDF and record never drift from
            // what was issued (includes per-state tax ids for uniform certs).
            $table->json('business_snapshot');
            $table->json('vendor_snapshot');
            $table->date('issue_date');
            $table->date('expiration_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_id', 'state_code']);
            $table->index(['business_id', 'expiration_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_certificates');
    }
};

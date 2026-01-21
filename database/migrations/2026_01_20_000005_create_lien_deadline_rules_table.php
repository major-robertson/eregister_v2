<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_deadline_rules', function (Blueprint $table) {
            $table->id();
            $table->string('state', 2);
            $table->foreignId('document_type_id')->constrained('lien_document_types')->cascadeOnDelete();
            $table->string('claimant_type')->nullable(); // null = applies to all
            $table->string('trigger_event'); // first_furnish_date, last_furnish_date, etc.
            $table->integer('offset_days'); // positive = after, negative = before
            $table->boolean('is_required')->default(true);
            $table->boolean('is_placeholder')->default(true); // true = rules not yet verified
            $table->json('conditions_json')->nullable(); // for future complex rules
            $table->text('notes')->nullable(); // internal documentation
            $table->timestamps();

            $table->index('state');
            $table->index(['state', 'document_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_deadline_rules');
    }
};

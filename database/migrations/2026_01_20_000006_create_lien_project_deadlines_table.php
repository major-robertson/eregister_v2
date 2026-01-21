<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_project_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('lien_projects')->cascadeOnDelete();
            $table->foreignId('deadline_rule_id')->constrained('lien_deadline_rules')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('lien_document_types')->cascadeOnDelete();

            $table->date('due_date')->nullable();
            $table->date('computed_from_date')->nullable(); // The anchor date used
            $table->json('missing_fields_json')->nullable(); // Array of missing anchor field names
            $table->string('status')->default('pending'); // pending, completed, missed, not_applicable

            // Link to the filing that completed this deadline
            $table->unsignedBigInteger('completed_filing_id')->nullable();

            $table->timestamps();

            // Unique constraint for upserts
            $table->unique(['project_id', 'deadline_rule_id'], 'lien_project_deadlines_unique');
            $table->index('business_id');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_project_deadlines');
    }
};

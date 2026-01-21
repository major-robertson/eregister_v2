<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('lien_document_types')->cascadeOnDelete();
            $table->string('state', 2)->nullable(); // null = generic fallback
            $table->string('county')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->date('effective_date');
            $table->json('schema_json'); // Wizard fields + validation rules
            $table->string('blade_view')->nullable(); // e.g., 'lien.templates.ca-prelim'
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['document_type_id', 'state', 'county', 'version'], 'lien_doc_templates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_document_templates');
    }
};

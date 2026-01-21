<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_projects', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Project identification
            $table->string('name');
            $table->string('job_number')->nullable();

            // Claimant type for this project
            $table->string('claimant_type'); // subcontractor, supplier, gc, other

            // Jobsite address
            $table->string('jobsite_address1')->nullable();
            $table->string('jobsite_address2')->nullable();
            $table->string('jobsite_city')->nullable();
            $table->string('jobsite_state', 2)->nullable();
            $table->string('jobsite_zip', 10)->nullable();
            $table->string('jobsite_county')->nullable();

            // Legal description
            $table->text('legal_description')->nullable();
            $table->string('apn')->nullable(); // Assessor Parcel Number

            // Project type (for future rule variations)
            $table->string('project_type')->nullable(); // private, public, residential, commercial

            // Anchor dates for deadline calculations
            $table->date('contract_date')->nullable();
            $table->date('first_furnish_date')->nullable();
            $table->date('last_furnish_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('noc_recorded_date')->nullable(); // Notice of Completion recorded

            $table->timestamps();

            $table->index('business_id');
            $table->index('jobsite_state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_projects');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('lien_projects')->cascadeOnDelete();

            // Party role
            $table->string('role'); // claimant, customer, owner, gc, lender, other

            // Party information
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->timestamps();

            $table->index('business_id');
            $table->index('project_id');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_parties');
    }
};

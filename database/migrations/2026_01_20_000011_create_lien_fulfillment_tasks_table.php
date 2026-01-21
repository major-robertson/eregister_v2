<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_fulfillment_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('filing_id')->constrained('lien_filings')->cascadeOnDelete();

            $table->string('status')->default('queued'); // queued, in_progress, waiting_on_customer, done
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('due_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index('business_id');
            $table->index('filing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_fulfillment_tasks');
    }
};

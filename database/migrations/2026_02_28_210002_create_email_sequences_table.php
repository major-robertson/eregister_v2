<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('sequence_type');
            $table->string('sequenceable_type')->nullable();
            $table->unsignedBigInteger('sequenceable_id')->nullable();
            $table->string('customer_type')->nullable();
            $table->text('resume_url')->nullable();
            $table->timestamp('next_send_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('suppressed_at')->nullable();
            $table->string('suppression_reason')->nullable();
            $table->timestamps();

            $table->unique(
                ['sequence_type', 'sequenceable_type', 'sequenceable_id'],
                'email_seq_type_unique'
            );
            $table->index(
                ['suppressed_at', 'completed_at', 'next_send_at'],
                'email_seq_processing'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sequences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('email_type');
            $table->string('emailable_type')->nullable();
            $table->unsignedBigInteger('emailable_id')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->unique(['email_type', 'emailable_type', 'emailable_id'], 'sent_emails_idempotency');
            $table->index(['user_id', 'email_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};

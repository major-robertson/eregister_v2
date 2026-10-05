<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per customer invited to Happy Websites by email
     * (email:send-websites-intro). The row holds what the two emails promise
     * and need: the date the spot is held through, the first email's
     * Message-ID so the reminder lands in the same thread, and whether the
     * customer has answered.
     */
    public function up(): void
    {
        Schema::create('website_invitations', function (Blueprint $table) {
            $table->id();
            // Unique: a person is invited once, ever.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('message_id');
            $table->timestamp('invited_at');
            // An Eastern calendar date: "I can hold a spot for you through ...".
            $table->date('deadline_on');
            $table->timestamp('reminded_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index('deadline_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_invitations');
    }
};

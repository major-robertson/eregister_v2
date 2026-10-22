<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per email the app sends, written by LogSentEmail (EREG-154).
        // sent_emails stays the duplicate guard for sequences; this is the
        // record of what went out and what it said.
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            // The Mailable or Notification class; null for Mail::raw().
            $table->string('mailable')->nullable();
            $table->string('subject', 500)->nullable();
            $table->string('from_address')->nullable();
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            // The account of the first To address that has one.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // The filing, project, application or waiver the email is about
            // (EmailLog::about() in the mailable's envelope).
            $table->nullableMorphs('related');
            // Postmark's MessageID: the delivery and bounce webhooks match on it.
            $table->string('message_id')->nullable()->index();
            $table->string('status', 20)->default('sent');
            $table->string('status_detail')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->mediumText('html_body')->nullable();
            // Only when there is no HTML part (Mail::raw()).
            $table->mediumText('text_body')->nullable();
            // Sign-in codes and reset links are logged without their body.
            $table->boolean('body_withheld')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};

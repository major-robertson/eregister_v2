<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resale_certificate_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resale_certificate_id')->constrained('resale_certificates')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('severity', 10)->default('info');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'is_read']);
            // Dedup window lookups: "already notified for this cert recently?"
            $table->index(['resale_certificate_id', 'type', 'created_at'], 'resale_cert_notif_dedup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resale_certificate_notifications');
    }
};

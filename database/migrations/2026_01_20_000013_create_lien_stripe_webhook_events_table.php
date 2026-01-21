<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_stripe_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_event_id')->unique();
            $table->string('type');
            $table->json('payload_json')->nullable();
            $table->timestamp('received_at');

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_stripe_webhook_events');
    }
};

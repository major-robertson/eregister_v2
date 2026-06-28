<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esign_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // The identified category of records this consent covers (e.g.
            // "demand_letters"). A different scope or version forces fresh consent.
            $table->string('consent_scope');
            $table->string('version');
            // The full consent + disclosure text shown, stored verbatim.
            $table->longText('disclosure_text');
            // Each labeled piece the user saw (heading, checkbox, withdrawal, etc.).
            $table->json('disclosure_snapshot_json')->nullable();
            $table->boolean('hardware_software_ack')->default(false);
            $table->string('consented_ip', 45)->nullable();
            $table->text('consented_user_agent')->nullable();
            $table->timestamp('consented_at');
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consent_scope', 'version']);
            $table->index(['user_id', 'withdrawn_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esign_consents');
    }
};

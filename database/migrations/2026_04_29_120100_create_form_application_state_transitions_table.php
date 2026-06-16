<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_application_state_transitions', function (Blueprint $table) {
            $table->id();

            // Explicit short index/FK names: the auto-generated names
            // `form_application_state_transitions_..._foreign/_index`
            // exceed MySQL's 64-char identifier limit. Pinning the names
            // keeps schema consistent across environments.
            $table->unsignedBigInteger('form_application_state_id');
            $table->foreign('form_application_state_id', 'fast_state_fk')
                ->references('id')
                ->on('form_application_states')
                ->cascadeOnDelete();

            $table->string('from_status')->nullable(); // null on the very first transition
            $table->string('to_status');

            $table->unsignedBigInteger('changed_by_user_id')->nullable();
            $table->foreign('changed_by_user_id', 'fast_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->text('comment')->nullable();
            $table->timestamps();

            // Composite index for the common audit-log query (newest
            // transitions per state). Explicit name for the same length
            // reason as the FKs above.
            $table->index(['form_application_state_id', 'created_at'], 'fast_state_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_application_state_transitions');
    }
};

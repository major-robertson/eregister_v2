<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('form_type'); // sales_tax_permit, llc
            $table->unsignedInteger('definition_version')->default(1);
            $table->json('definition_snapshot')->nullable();
            $table->json('selected_states'); // ["CA", "TX", "NY"]
            $table->string('status')->default('draft'); // draft, in_progress, submitted
            $table->string('current_phase')->default('core'); // core, states, review
            $table->string('current_step_key')->nullable();
            $table->unsignedInteger('current_state_index')->default(0);
            $table->json('core_data')->nullable(); // Answers asked once (encrypted sensitive fields)
            $table->string('core_data_hash')->nullable(); // For dirty detection
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();

            // Stripe reference columns for debugging/tracing
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'form_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_applications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_filings', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('lien_projects')->cascadeOnDelete();
            $table->foreignId('document_type_id')->constrained('lien_document_types')->cascadeOnDelete();

            // Link to the deadline this filing fulfills
            $table->foreignId('project_deadline_id')->nullable()->constrained('lien_project_deadlines')->nullOnDelete();

            // Service and status
            $table->string('service_level')->default('self_serve'); // self_serve, full_service
            $table->string('status')->default('draft'); // draft, awaiting_payment, paid, in_fulfillment, mailed, recorded, complete, canceled

            // Jurisdiction
            $table->string('jurisdiction_state', 2)->nullable();
            $table->string('jurisdiction_county')->nullable();

            // Filing details
            $table->unsignedBigInteger('amount_claimed_cents')->nullable();
            $table->text('description_of_work')->nullable();

            // Snapshots (immutable after payment)
            $table->json('payload_json')->nullable(); // All form data at time of payment
            $table->json('parties_snapshot_json')->nullable(); // Party data at time of payment

            // Stripe
            $table->string('stripe_checkout_session_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();

            // Lifecycle timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('mailed_at')->nullable();
            $table->string('mailing_tracking_number')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('business_id');
            $table->index('status');
            $table->index(['project_id', 'status']);
        });

        // Add foreign key for completed_filing_id now that lien_filings exists
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->foreign('completed_filing_id')
                ->references('id')
                ->on('lien_filings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropForeign(['completed_filing_id']);
        });

        Schema::dropIfExists('lien_filings');
    }
};

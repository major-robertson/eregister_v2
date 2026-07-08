<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_waivers', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('lien_projects')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // provide = user signs their own waiver; collect = counterparty signs.
            $table->string('direction');
            // One of the four canonical kinds; the state registry maps it to the
            // state-correct document (e.g. GA interim, WY single form).
            $table->string('kind');
            $table->string('status')->default('draft');
            // generated = built by our engine; uploaded = an outside waiver the
            // user stored for tracking.
            $table->string('source')->default('generated');

            // Denormalized from the project at creation so a later project edit
            // can't silently change which state's form a waiver claims to be.
            $table->string('state', 2);
            $table->string('template_key')->nullable();
            $table->unsignedInteger('template_version')->nullable();

            $table->bigInteger('amount_cents')->nullable();
            $table->date('through_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('check_maker')->nullable();
            $table->string('check_number')->nullable();
            // Fill-in exceptions (disputed claims, extras) printed on the form.
            $table->text('exceptions')->nullable();

            // Counterparty: the customer receiving the waiver (provide) or the
            // vendor signing it (collect). Snapshotted so directory edits don't
            // rewrite history; lien_contact_id links back to the directory.
            $table->foreignId('lien_contact_id')->nullable()->constrained('lien_contacts')->nullOnDelete();
            $table->string('counterparty_company')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_email')->nullable();
            $table->string('counterparty_phone')->nullable();

            // Who signs. provide: the business's own user; collect: the vendor.
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signer_title')->nullable();

            // Frozen render payload from generation time; the signed variant is
            // rendered from this, never from live project data.
            $table->json('render_snapshot_json')->nullable();

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            // GA (90 days) / MS (60 days): the date a signed-but-unpaid waiver
            // becomes conclusively effective unless preserved.
            $table->date('deemed_effective_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'created_at']);
            $table->index('project_id');
            $table->index('signer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_waivers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lien_state_rules', function (Blueprint $table) {
            $table->char('state', 2)->primary();
            $table->boolean('pre_notice_required')->default(false);
            $table->string('pre_notice_required_for')->default('none'); // none, subs, suppliers, everyone
            $table->unsignedSmallInteger('noi_lead_time_days')->nullable();
            $table->boolean('post_lien_notice_required')->default(false);
            $table->unsignedSmallInteger('post_lien_notice_days')->nullable();
            $table->boolean('efile_allowed')->default(true);
            $table->boolean('notarization_required')->default(false);
            $table->string('wrongful_lien_penalty')->default('none'); // none, fees, damages, criminal
            $table->boolean('owner_occupied_special_rules')->default(false);
            $table->unsignedSmallInteger('enforcement_deadline_days');
            $table->decimal('enforcement_deadline_months', 6, 3)->nullable();
            $table->string('enforcement_deadline_trigger')->default('lien_recorded_date');
            $table->json('statute_references')->nullable();
            $table->string('data_source')->default('csv_v1');
            $table->timestamps();

            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lien_state_rules');
    }
};

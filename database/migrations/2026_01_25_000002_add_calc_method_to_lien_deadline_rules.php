<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_deadline_rules', function (Blueprint $table) {
            // Calculation method: days_after_date, months_after_date, month_day_after_month_of_date, days_after_end_of_month_of_date
            $table->string('calc_method')->default('days_after_date')->after('trigger_event');
            $table->unsignedTinyInteger('offset_months')->nullable()->after('offset_days');
            $table->unsignedTinyInteger('day_of_month')->nullable()->after('offset_months');
            $table->string('effective_scope')->default('both')->after('is_required'); // residential, commercial, both
            $table->string('data_source')->default('csv_v1')->after('notes');
        });

        // Update existing claimant_type from null to 'any'
        \DB::table('lien_deadline_rules')
            ->whereNull('claimant_type')
            ->update(['claimant_type' => 'any']);

        // Make claimant_type non-nullable with default 'any'
        Schema::table('lien_deadline_rules', function (Blueprint $table) {
            $table->string('claimant_type')->default('any')->change();
        });
    }

    public function down(): void
    {
        Schema::table('lien_deadline_rules', function (Blueprint $table) {
            $table->dropColumn(['calc_method', 'offset_months', 'day_of_month', 'effective_scope', 'data_source']);
        });
    }
};

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
        Schema::table('lien_projects', function (Blueprint $table) {
            // Owner vs Tenant distinction
            $table->boolean('owner_is_tenant')->default(false)->after('project_type');

            // Written contract question
            $table->boolean('has_written_contract')->nullable()->after('owner_is_tenant');

            // Financial breakdown (all optional)
            $table->bigInteger('base_contract_amount_cents')->nullable()->after('has_written_contract');
            $table->bigInteger('change_orders_cents')->nullable()->after('base_contract_amount_cents');
            $table->bigInteger('credits_deductions_cents')->nullable()->after('change_orders_cents');
            $table->bigInteger('payments_received_cents')->nullable()->after('credits_deductions_cents');
            $table->bigInteger('uncompleted_work_cents')->nullable()->after('payments_received_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn([
                'owner_is_tenant',
                'has_written_contract',
                'base_contract_amount_cents',
                'change_orders_cents',
                'credits_deductions_cents',
                'payments_received_cents',
                'uncompleted_work_cents',
            ]);
        });
    }
};

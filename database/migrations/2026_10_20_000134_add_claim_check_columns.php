<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The filing wizard's money step (EREG-134). The project keeps the
     * customer's reason when the amount claimed differs from the balance due
     * the contract figures give; the filing keeps when the customer attested
     * that the claim holds no fees, interest or penalties.
     */
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->text('claim_difference_reason')->nullable()->after('uncompleted_work_cents');
        });

        Schema::table('lien_filings', function (Blueprint $table) {
            $table->timestamp('claim_attested_at')->nullable()->after('amount_claimed_cents');
        });
    }

    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn('claim_difference_reason');
        });

        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn('claim_attested_at');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FEIN/EIN is sensitive PII. Persisted on the business profile so a
     * returning user doesn't have to re-enter it on every new sales-tax
     * application. Encrypted at rest via Laravel's `encrypted` cast on
     * the Business model. Column is plain text large enough to hold the
     * encrypted blob (Laravel encryption ~ several hundred bytes).
     */
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            if (! Schema::hasColumn('businesses', 'fein')) {
                $table->text('fein')->nullable()->after('entity_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('fein');
        });
    }
};

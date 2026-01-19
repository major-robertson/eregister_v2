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
        Schema::table('businesses', function (Blueprint $table) {
            // Business profile data for form pre-fill
            if (! Schema::hasColumn('businesses', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('businesses', 'dba_name')) {
                $table->string('dba_name')->nullable()->after('legal_name');
            }
            if (! Schema::hasColumn('businesses', 'entity_type')) {
                $table->string('entity_type')->nullable()->after('dba_name');
            }
            if (! Schema::hasColumn('businesses', 'business_address')) {
                $table->json('business_address')->nullable()->after('entity_type');
            }
            if (! Schema::hasColumn('businesses', 'mailing_address')) {
                $table->json('mailing_address')->nullable()->after('business_address');
            }
            if (! Schema::hasColumn('businesses', 'responsible_people')) {
                $table->json('responsible_people')->nullable()->after('mailing_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'legal_name',
                'dba_name',
                'entity_type',
                'business_address',
                'mailing_address',
                'responsible_people',
            ]);
        });
    }
};

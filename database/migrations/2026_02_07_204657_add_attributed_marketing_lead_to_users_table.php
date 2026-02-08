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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('attributed_marketing_lead_id')
                ->nullable()
                ->after('signup_user_agent')
                ->constrained('marketing_leads')
                ->nullOnDelete();
            $table->dateTime('attributed_at')->nullable()->after('attributed_marketing_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['attributed_marketing_lead_id']);
            $table->dropColumn(['attributed_marketing_lead_id', 'attributed_at']);
        });
    }
};

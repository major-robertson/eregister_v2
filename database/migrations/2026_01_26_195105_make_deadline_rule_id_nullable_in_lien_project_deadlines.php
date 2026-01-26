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
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropForeign(['deadline_rule_id']);
        });

        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->unsignedBigInteger('deadline_rule_id')->nullable()->change();
        });

        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->foreign('deadline_rule_id')
                ->references('id')
                ->on('lien_deadline_rules')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->dropForeign(['deadline_rule_id']);
        });

        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->unsignedBigInteger('deadline_rule_id')->nullable(false)->change();
        });

        Schema::table('lien_project_deadlines', function (Blueprint $table) {
            $table->foreign('deadline_rule_id')
                ->references('id')
                ->on('lien_deadline_rules')
                ->cascadeOnDelete();
        });
    }
};

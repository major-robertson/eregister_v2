<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_deadline_rules', function (Blueprint $table) {
            $table->smallInteger('offset_days')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lien_deadline_rules', function (Blueprint $table) {
            $table->smallInteger('offset_days')->nullable(false)->default(0)->change();
        });
    }
};

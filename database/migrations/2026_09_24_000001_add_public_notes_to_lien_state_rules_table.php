<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `notes` column carries the data author's working commentary (field
 * names, proxies, placeholders) and must never be rendered publicly. The
 * "/liens/{state}" pages read `public_notes`, a reviewed plain-English
 * version, instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_state_rules', function (Blueprint $table) {
            $table->text('public_notes')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('lien_state_rules', function (Blueprint $table) {
            $table->dropColumn('public_notes');
        });
    }
};

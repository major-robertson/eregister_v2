<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_contacts', function (Blueprint $table) {
            $table->string('county')->nullable()->after('state');
        });

        Schema::table('lien_parties', function (Blueprint $table) {
            $table->string('county')->nullable()->after('state');
        });

        Schema::table('lien_waivers', function (Blueprint $table) {
            // Only collected when the state's statutory form demands a formal
            // legal description (MO § 429.016.27); snapshot lives on the waiver
            // so later project edits can't change an issued document's meaning.
            $table->text('legal_description')->nullable()->after('exceptions');
        });
    }

    public function down(): void
    {
        Schema::table('lien_contacts', function (Blueprint $table) {
            $table->dropColumn('county');
        });

        Schema::table('lien_parties', function (Blueprint $table) {
            $table->dropColumn('county');
        });

        Schema::table('lien_waivers', function (Blueprint $table) {
            $table->dropColumn('legal_description');
        });
    }
};

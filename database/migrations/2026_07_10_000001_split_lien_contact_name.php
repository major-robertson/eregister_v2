<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_contacts', function (Blueprint $table) {
            // Split the single contact_name into first/last, and let company be
            // optional — a contact needs a company OR a person name, not both.
            $table->string('first_name')->nullable()->after('company_name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('company_name')->nullable()->change();
            $table->dropColumn('contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('lien_contacts', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('company_name');
            $table->string('company_name')->nullable(false)->change();
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};

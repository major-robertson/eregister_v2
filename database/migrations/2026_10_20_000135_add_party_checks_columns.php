<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The filing wizard's Parties step (EREG-135). A party keeps whether the
     * customer confirmed that the jobsite address really is the general
     * contractor's, subcontractor's or customer's mailing address, and
     * whether the customer confirmed an address whose ZIP code and state
     * don't seem to agree; the project keeps the customer's answer to "Is the
     * property leased to someone else?".
     */
    public function up(): void
    {
        Schema::table('lien_parties', function (Blueprint $table) {
            $table->boolean('address_same_as_jobsite_confirmed')->default(false)->after('zip');
            $table->boolean('address_confirmed_by_customer')->nullable()->after('address_same_as_jobsite_confirmed');
        });

        Schema::table('lien_projects', function (Blueprint $table) {
            $table->boolean('property_leased')->nullable()->after('owner_is_tenant');
        });
    }

    public function down(): void
    {
        Schema::table('lien_parties', function (Blueprint $table) {
            $table->dropColumn(['address_same_as_jobsite_confirmed', 'address_confirmed_by_customer']);
        });

        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn('property_leased');
        });
    }
};

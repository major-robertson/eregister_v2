<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->string('jobsite_place_id')->nullable()->after('jobsite_county');
            $table->string('jobsite_formatted_address')->nullable()->after('jobsite_place_id');
            $table->decimal('jobsite_lat', 10, 7)->nullable()->after('jobsite_formatted_address');
            $table->decimal('jobsite_lng', 10, 7)->nullable()->after('jobsite_lat');
        });
    }

    public function down(): void
    {
        Schema::table('lien_projects', function (Blueprint $table) {
            $table->dropColumn([
                'jobsite_place_id',
                'jobsite_formatted_address',
                'jobsite_lat',
                'jobsite_lng',
            ]);
        });
    }
};

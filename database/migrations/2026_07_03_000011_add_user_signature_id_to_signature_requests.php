<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            // Which adopted signature (drawn/typed image) was applied at
            // signing. Plain id, no FK: the request's evidence must not be
            // mutated by signature-row lifecycle.
            $table->unsignedBigInteger('user_signature_id')->nullable()->after('signature_method');
        });
    }

    public function down(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->dropColumn('user_signature_id');
        });
    }
};

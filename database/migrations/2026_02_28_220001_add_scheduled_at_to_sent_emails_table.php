<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->nullable()->after('emailable_id');
            $table->timestamp('sent_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
            $table->timestamp('sent_at')->nullable(false)->change();
        });
    }
};

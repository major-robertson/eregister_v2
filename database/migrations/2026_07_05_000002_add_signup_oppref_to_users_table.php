<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // OpenAI/ChatGPT Ads click token (?oppref=) captured first-touch
            // like the UTM columns; attached to Conversions API events for
            // attribution when the browser pixel can't fire (webhooks).
            $table->string('signup_oppref')->nullable()->after('signup_rdt_cid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_oppref');
        });
    }
};

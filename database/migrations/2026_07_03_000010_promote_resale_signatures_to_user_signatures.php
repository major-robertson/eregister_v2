<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The adopted signature is a site-wide asset (used by resale certs and
     * lien esign alike), so the table sheds its resale prefix and learns the
     * typed-name method alongside drawn.
     */
    public function up(): void
    {
        Schema::rename('resale_signatures', 'user_signatures');

        Schema::table('user_signatures', function (Blueprint $table) {
            $table->string('method', 20)->default('drawn')->after('user_id');
            $table->string('typed_name')->nullable()->after('strokes_json');
            $table->string('typed_font', 50)->nullable()->after('typed_name');
        });
    }

    public function down(): void
    {
        Schema::table('user_signatures', function (Blueprint $table) {
            $table->dropColumn(['method', 'typed_name', 'typed_font']);
        });

        Schema::rename('user_signatures', 'resale_signatures');
    }
};

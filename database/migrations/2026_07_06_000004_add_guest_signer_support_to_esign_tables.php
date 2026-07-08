<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guest signing: a signature request may now target an email address instead of
 * an existing account (lien waiver counterparties). Identity is asserted with a
 * one-time email code; consent rows for guests carry the email instead of a
 * user id. Account-signer flows (demand letters) are unaffected; their
 * signer_user_id stays set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->dropForeign(['signer_user_id']);
        });

        Schema::table('signature_requests', function (Blueprint $table) {
            $table->foreignId('signer_user_id')->nullable()->change();
            $table->foreign('signer_user_id')->references('id')->on('users')->nullOnDelete();

            // One-time email code challenge for guest signers.
            $table->string('guest_code_hash')->nullable()->after('signer_phone_snapshot');
            $table->timestamp('guest_code_expires_at')->nullable()->after('guest_code_hash');
            $table->unsignedTinyInteger('guest_code_attempts')->default(0)->after('guest_code_expires_at');
            $table->timestamp('guest_code_last_sent_at')->nullable()->after('guest_code_attempts');
            $table->timestamp('guest_verified_at')->nullable()->after('guest_code_last_sent_at');
        });

        Schema::table('esign_consents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('esign_consents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('guest_email')->nullable()->after('user_id');

            $table->index('guest_email');
        });
    }

    public function down(): void
    {
        Schema::table('esign_consents', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['guest_email']);
            $table->dropColumn('guest_email');
        });

        Schema::table('esign_consents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('signature_requests', function (Blueprint $table) {
            $table->dropForeign(['signer_user_id']);
            $table->dropColumn([
                'guest_code_hash',
                'guest_code_expires_at',
                'guest_code_attempts',
                'guest_code_last_sent_at',
                'guest_verified_at',
            ]);
        });

        Schema::table('signature_requests', function (Blueprint $table) {
            $table->foreignId('signer_user_id')->nullable(false)->change();
            $table->foreign('signer_user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

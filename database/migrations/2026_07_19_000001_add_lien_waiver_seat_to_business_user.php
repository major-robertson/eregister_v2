<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lien waiver seats: the business holds ONE Cashier subscription whose
     * quantity is the number of seats, and this pivot column marks which
     * members hold one. A user with a timestamp here (on a business with an
     * active lien_waiver subscription) has paid access; everyone else rides
     * the free tier. Living on the membership pivot means a member removed
     * from the business automatically loses the seat.
     */
    public function up(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->timestamp('lien_waiver_seat_at')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->dropColumn('lien_waiver_seat_at');
        });
    }
};

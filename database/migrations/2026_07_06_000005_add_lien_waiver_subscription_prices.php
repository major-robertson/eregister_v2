<?php

use App\Models\Price;
use Database\Seeders\LienWaiverPriceSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Land the lien-waiver subscription prices in the `prices` table. They
     * launched at $99/mo and $990/yr and were repriced on 2026-09-20 (see
     * that migration). The real Stripe recurring Price IDs live as literals
     * in LienWaiverPriceSeeder (the canonical source, like
     * ResaleCertPriceSeeder); this defers to it so a fresh migrate lands the
     * rows too. Idempotent (updateOrCreate).
     */
    public function up(): void
    {
        (new LienWaiverPriceSeeder)->run();
    }

    /**
     * Deactivate (don't delete): Payments hold price_id references once
     * anyone has checked out, so the rows must survive a rollback.
     */
    public function down(): void
    {
        Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->update(['active' => false]);
    }
};

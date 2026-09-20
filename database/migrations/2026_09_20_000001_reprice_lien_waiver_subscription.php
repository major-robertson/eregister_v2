<?php

use App\Models\Price;
use Database\Seeders\LienWaiverPriceSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Lien Waiver Pro moves from $99/mo and $990/yr to $49/mo and $490/yr per
     * seat. The Stripe price ids live as literals in LienWaiverPriceSeeder
     * (the canonical source); this re-runs it so a deploy lands the new rows.
     *
     * Payments made at the launch prices reference the existing rows by id,
     * so those rows become the (inactive) launch rows first and the new
     * prices get fresh rows: history keeps pointing at what was charged.
     * Idempotent: the rename only matches a row still at its launch amount,
     * and the seeder is updateOrCreate.
     */
    public function up(): void
    {
        $launchRows = [
            'monthly' => ['variant' => 'monthly_launch_99', 'amount_cents' => 9900],
            'yearly' => ['variant' => 'yearly_launch_990', 'amount_cents' => 99000],
        ];

        foreach ($launchRows as $variant => $launch) {
            Price::query()
                ->where('product_family', 'lien')
                ->where('product_key', 'lien_waiver')
                ->where('billing_type', 'subscription')
                ->where('variant_key', $variant)
                ->where('amount_cents', $launch['amount_cents'])
                ->update(['variant_key' => $launch['variant'], 'active' => false]);
        }

        (new LienWaiverPriceSeeder)->run();
    }

    /**
     * No-op: the rows are data, and Payments reference them by id. Rolling
     * the price back means changing the seeder and shipping a new migration.
     */
    public function down(): void {}
};

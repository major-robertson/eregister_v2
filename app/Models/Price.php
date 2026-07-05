<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Price extends Model
{
    protected $fillable = [
        'product_family',
        'product_key',
        'variant_key',
        'billing_type',
        'amount_cents',
        'currency',
        'interval',
        'interval_count',
        'stripe_price_id_test',
        'stripe_price_id_live',
        'stripe_lookup_key',
        'active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'interval_count' => 'integer',
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Resolve a price by family, product, variant, and billing type.
     * Throws if not found.
     */
    public static function resolve(
        string $family,
        string $productKey,
        string $variantKey = 'default',
        string $billingType = 'one_time'
    ): self {
        $price = static::where('product_family', $family)
            ->where('product_key', $productKey)
            ->where('variant_key', $variantKey)
            ->where('billing_type', $billingType)
            ->where('active', true)
            ->first();

        if (! $price) {
            throw new InvalidArgumentException(
                "Price not found: {$family}/{$productKey}/{$variantKey}/{$billingType}"
            );
        }

        return $price;
    }

    /**
     * Resolve a lien price, honoring per-state overrides.
     *
     * State-specific rows use variant_key "{STATE}_{service_level}" (e.g.
     * "NJ_full_service"). When a matching active row exists it wins; otherwise
     * we fall back to the default service-level price. State codes are
     * normalized to uppercase to match how they are stored/seeded.
     */
    public static function resolveLien(string $productKey, string $serviceLevel, ?string $state): self
    {
        if ($state !== null && $state !== '') {
            $stateSpecific = static::where('product_family', 'lien')
                ->where('product_key', $productKey)
                ->where('variant_key', strtoupper($state).'_'.$serviceLevel)
                ->where('billing_type', 'one_time')
                ->where('active', true)
                ->first();

            if ($stateSpecific !== null) {
                return $stateSpecific;
            }
        }

        return static::resolve('lien', $productKey, $serviceLevel, 'one_time');
    }

    /**
     * Get the Stripe Price ID for the current environment.
     * Detects live vs test based on configured Stripe secret key prefix.
     */
    public function stripePriceId(): ?string
    {
        $secret = (string) config('cashier.secret');
        $isLive = str_starts_with($secret, 'sk_live_');

        return $isLive ? $this->stripe_price_id_live : $this->stripe_price_id_test;
    }

    /**
     * Format the amount for display.
     * Returns placeholder for subscriptions where amount_cents is null.
     */
    public function formattedAmount(): string
    {
        if ($this->amount_cents === null) {
            return '—'; // Subscription prices come from Stripe
        }

        return '$'.number_format($this->amount_cents / 100, 2);
    }
}

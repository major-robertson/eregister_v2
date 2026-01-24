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

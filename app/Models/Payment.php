<?php

namespace App\Models;

use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'purchasable_type',
        'purchasable_id',
        'price_id',
        'provider',
        'livemode',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'amount_cents',
        'currency',
        'status',
        'billing_type',
        'error_message',
        'requires_manual_review',
        'meta',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'livemode' => 'boolean',
            'requires_manual_review' => 'boolean',
            'meta' => 'array',
            'paid_at' => 'datetime',
            'amount_cents' => 'integer',
        ];
    }

    /**
     * The purchasable item (polymorphic).
     */
    public function purchasable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The business that owns this payment.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * The price from the catalog (snapshot reference).
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    /**
     * Find retryable payment with row locking for concurrency safety.
     */
    public static function findRetryableForWithLock(Model $purchasable): ?self
    {
        return static::whereMorphedTo('purchasable', $purchasable)
            ->whereIn('status', [PaymentStatus::Initiated, PaymentStatus::RequiresPaymentMethod])
            ->lockForUpdate()
            ->latest()
            ->first();
    }

    /**
     * Detect if current Stripe config is live mode.
     */
    public static function isLiveMode(): bool
    {
        return str_starts_with((string) config('cashier.secret'), 'sk_live_');
    }

    /**
     * Format the amount for display.
     */
    public function formattedAmount(): string
    {
        $symbol = match (strtoupper($this->currency)) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => strtoupper($this->currency).' ',
        };

        return $symbol.number_format($this->amount_cents / 100, 2);
    }

    /**
     * Check if payment is complete.
     */
    public function isPaid(): bool
    {
        return $this->status === PaymentStatus::Succeeded && $this->paid_at !== null;
    }

    /**
     * Check if payment can be retried.
     */
    public function isRetryable(): bool
    {
        return $this->status?->isRetryable() ?? true;
    }

    /**
     * Check if payment is in a terminal state.
     */
    public function isTerminal(): bool
    {
        return $this->status?->isTerminal() ?? false;
    }
}

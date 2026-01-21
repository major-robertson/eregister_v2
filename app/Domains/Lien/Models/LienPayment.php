<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienPayment extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'filing_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'amount_cents',
        'currency',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(LienFiling::class, 'filing_id');
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
        return $this->status === 'paid' && $this->paid_at !== null;
    }
}

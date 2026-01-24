<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;

class StripeWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stripe_event_id',
        'type',
        'raw_payload',
        'received_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Check if an event has been fully processed (two-phase idempotency).
     */
    public static function isProcessed(string $stripeEventId): bool
    {
        return static::where('stripe_event_id', $stripeEventId)
            ->whereNotNull('processed_at')
            ->exists();
    }

    /**
     * Insert a pending event with raw payload (returns false if already exists).
     * Two-phase idempotency: insert first, process, then mark as processed.
     */
    public static function insertPending(string $stripeEventId, string $type, string $rawPayload): bool
    {
        try {
            static::create([
                'stripe_event_id' => $stripeEventId,
                'type' => $type,
                'raw_payload' => $rawPayload,
                'received_at' => now(),
                'processed_at' => null,
            ]);

            return true;
        } catch (UniqueConstraintViolationException) {
            return false; // Already exists
        }
    }

    /**
     * Mark an event as processed (called only after successful handling).
     */
    public static function markProcessed(string $stripeEventId): void
    {
        static::where('stripe_event_id', $stripeEventId)->update(['processed_at' => now()]);
    }
}

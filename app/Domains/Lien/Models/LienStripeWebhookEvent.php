<?php

namespace App\Domains\Lien\Models;

use Illuminate\Database\Eloquent\Model;

class LienStripeWebhookEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload_json',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
            'received_at' => 'datetime',
        ];
    }

    /**
     * Check if an event has already been processed.
     */
    public static function isProcessed(string $stripeEventId): bool
    {
        return static::where('stripe_event_id', $stripeEventId)->exists();
    }

    /**
     * Record a processed event.
     */
    public static function record(string $stripeEventId, string $type, array $payload): static
    {
        return static::create([
            'stripe_event_id' => $stripeEventId,
            'type' => $type,
            'payload_json' => $payload,
            'received_at' => now(),
        ]);
    }
}

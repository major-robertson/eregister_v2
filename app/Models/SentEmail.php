<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Log;

class SentEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_type',
        'emailable_type',
        'emailable_id',
        'scheduled_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record a scheduled/sent email and execute the callback, or skip if already recorded.
     * Uses the unique constraint on (email_type, emailable_type, emailable_id) for idempotency.
     *
     * For immediate emails, both scheduled_at and sent_at are set to now().
     * For delayed emails, pass scheduledAt with the future time; sent_at stays null until delivery.
     *
     * @return bool Whether the email was recorded (true) or skipped as duplicate (false).
     */
    public static function recordOrSkip(
        string $emailType,
        ?Model $emailable,
        User $user,
        callable $callback,
        ?\DateTimeInterface $scheduledAt = null
    ): bool {
        $isImmediate = $scheduledAt === null;

        try {
            static::create([
                'user_id' => $user->getKey(),
                'email_type' => $emailType,
                'emailable_type' => $emailable?->getMorphClass(),
                'emailable_id' => $emailable?->getKey(),
                'scheduled_at' => $scheduledAt ?? now(),
                'sent_at' => $isImmediate ? now() : null,
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            Log::debug('SentEmail: skipping duplicate', [
                'email_type' => $emailType,
                'emailable' => $emailable ? get_class($emailable).':'.$emailable->getKey() : null,
                'user_id' => $user->getKey(),
            ]);

            return false;
        }

        $callback();

        return true;
    }

    /**
     * Mark this email as actually sent (updates sent_at for delayed emails).
     */
    public function markSent(): void
    {
        if ($this->sent_at === null) {
            $this->update(['sent_at' => now()]);
        }
    }

    /**
     * Find a sent email record by type and emailable, then mark it as sent.
     */
    public static function markSentByType(string $emailType, Model $emailable): void
    {
        static::where('email_type', $emailType)
            ->where('emailable_type', $emailable->getMorphClass())
            ->where('emailable_id', $emailable->getKey())
            ->whereNull('sent_at')
            ->update(['sent_at' => now()]);
    }
}

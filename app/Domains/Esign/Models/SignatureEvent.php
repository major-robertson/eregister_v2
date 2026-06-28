<?php

namespace App\Domains\Esign\Models;

use App\Domains\Esign\Enums\SignatureEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One append-only, hash-chained audit event. Written only by AppendSignatureEvent.
 * Rows are immutable: any attempt to update or delete throws.
 */
class SignatureEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'signature_request_id',
        'signature_document_id',
        'event_type',
        'actor_type',
        'actor_user_id',
        'ip_address',
        'user_agent',
        'occurred_at',
        'metadata_json',
        'previous_event_hash',
        'event_hash',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SignatureEventType::class,
            'metadata_json' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Signature events are append-only and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Signature events are append-only and cannot be deleted.');
        });
    }

    public function signatureRequest(): BelongsTo
    {
        return $this->belongsTo(SignatureRequest::class);
    }

    public function signatureDocument(): BelongsTo
    {
        return $this->belongsTo(SignatureDocument::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->metadata_json[$key] ?? $default;
    }
}

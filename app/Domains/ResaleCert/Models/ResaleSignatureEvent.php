<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * One append-only, hash-chained audit event for the resale-cert signature
 * lifecycle (consent, adoption, generation, download, deletion). Written only
 * by AppendResaleSignatureEvent; the chain is per business. Rows are
 * immutable: any attempt to update or delete throws. Mirrors the Esign
 * domain's SignatureEvent.
 */
class ResaleSignatureEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id',
        'event_type',
        'resale_signature_id',
        'resale_certificate_id',
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
            'event_type' => ResaleSignatureEventType::class,
            'metadata_json' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException('Resale signature events are append-only and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new RuntimeException('Resale signature events are append-only and cannot be deleted.');
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(ResaleCertificate::class, 'resale_certificate_id')->withTrashed();
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return $this->metadata_json[$key] ?? $default;
    }
}

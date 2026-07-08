<?php

namespace App\Domains\Esign\Models;

use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * A signing session: one per "Send for E-Sign". Holds the signer, consent,
 * intent, and per-letter documents. Polymorphic `signable` points at the source
 * record (a LienFiling today) so the Esign domain never depends on Lien.
 */
class SignatureRequest extends Model
{
    protected $fillable = [
        'public_id',
        'signable_type',
        'signable_id',
        'business_id',
        'signer_user_id',
        'document_signing_policy_key',
        'status',
        'failure_reason',
        'signer_name_snapshot',
        'signer_email_snapshot',
        'signer_phone_snapshot',
        'guest_code_hash',
        'guest_code_expires_at',
        'guest_code_attempts',
        'guest_code_last_sent_at',
        'guest_verified_at',
        'intent_statement',
        'presented_text_json',
        'signature_method',
        'user_signature_id',
        'adopted_name',
        'email_verified_at_sign',
        'consent_id',
        'invited_at',
        'first_opened_at',
        'completed_at',
        'voided_at',
        'expires_at',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => SignatureRequestStatus::class,
            'presented_text_json' => 'array',
            'email_verified_at_sign' => 'datetime',
            'guest_code_expires_at' => 'datetime',
            'guest_code_attempts' => 'integer',
            'guest_code_last_sent_at' => 'datetime',
            'guest_verified_at' => 'datetime',
            'invited_at' => 'datetime',
            'first_opened_at' => 'datetime',
            'completed_at' => 'datetime',
            'voided_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $request): void {
            if (! $request->public_id) {
                $request->public_id = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function signable(): MorphTo
    {
        return $this->morphTo();
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function consent(): BelongsTo
    {
        return $this->belongsTo(EsignConsent::class, 'consent_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SignatureDocument::class)->orderBy('sort_order')->orderBy('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SignatureEvent::class)->orderBy('id');
    }

    /**
     * Guest sessions have no account — the signer proves control of the
     * invited email with a one-time code instead of logging in.
     */
    public function isGuest(): bool
    {
        return $this->signer_user_id === null;
    }

    public function policy(): DocumentSigningPolicy
    {
        return DocumentSigningPolicy::for($this->document_signing_policy_key);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isCompleted(): bool
    {
        return $this->status === SignatureRequestStatus::Completed;
    }

    public function lastEvent(): ?SignatureEvent
    {
        return $this->events()->latest('id')->first();
    }

    /**
     * The signing link expiry, defaulting to the configured TTL from invite time.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', SignatureRequestStatus::activeValues());
    }
}

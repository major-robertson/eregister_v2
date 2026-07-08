<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Domains\Business\Models\Business;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Models\User;
use Database\Factories\Lien\LienWaiverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LienWaiver extends Model implements HasMedia
{
    use BelongsToBusiness, HasFactory, InteractsWithMedia, SoftDeletes;

    protected static function newFactory(): LienWaiverFactory
    {
        return LienWaiverFactory::new();
    }

    protected $fillable = [
        'public_id',
        'business_id',
        'project_id',
        'created_by_user_id',
        'direction',
        'kind',
        'status',
        'source',
        'state',
        'template_key',
        'template_version',
        'amount_cents',
        'through_date',
        'invoice_number',
        'check_maker',
        'check_number',
        'exceptions',
        'lien_contact_id',
        'counterparty_company',
        'counterparty_name',
        'counterparty_email',
        'counterparty_phone',
        'signer_name',
        'signer_email',
        'signer_title',
        'render_snapshot_json',
        'generated_at',
        'sent_at',
        'signed_at',
        'voided_at',
        'deemed_effective_at',
    ];

    protected function casts(): array
    {
        return [
            'direction' => WaiverDirection::class,
            'kind' => WaiverKind::class,
            'status' => WaiverStatus::class,
            'amount_cents' => 'integer',
            'template_version' => 'integer',
            'through_date' => 'date',
            'render_snapshot_json' => 'array',
            'generated_at' => 'datetime',
            'sent_at' => 'datetime',
            'signed_at' => 'datetime',
            'voided_at' => 'datetime',
            'deemed_effective_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $waiver): void {
            if (! $waiver->public_id) {
                $waiver->public_id = Str::ulid()->toBase32();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function registerMediaCollections(): void
    {
        // The unsigned generated PDF.
        $this->addMediaCollection('generated')
            ->useDisk('s3');

        // The signed copy: the e-sign output, or an uploaded outside waiver.
        $this->addMediaCollection('signed')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->useDisk('s3');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(LienProject::class, 'project_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(LienContact::class, 'lien_contact_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function signatureRequests(): MorphMany
    {
        return $this->morphMany(SignatureRequest::class, 'signable');
    }

    public function activeSignatureRequest(): ?SignatureRequest
    {
        return $this->signatureRequests()->active()->latest('id')->first();
    }

    public function latestSignatureRequest(): ?SignatureRequest
    {
        return $this->signatureRequests()->latest('id')->first();
    }

    /**
     * The most recent executed e-sign document across ALL of this waiver's
     * signature requests: a completed request followed by a void + re-send
     * must not hide the earlier executed copy (signed PDFs are immutable
     * legal records and stay downloadable, mirroring the demand-letter flow).
     */
    public function latestSignedDocument(): ?SignatureDocument
    {
        return SignatureDocument::query()
            ->whereIn('signature_request_id', $this->signatureRequests()->select('id'))
            ->whereNotNull('signed_at')
            ->latest('signed_at')
            ->first();
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(LienWaiverNotificationLog::class, 'lien_waiver_id');
    }

    public function isSigned(): bool
    {
        return $this->status === WaiverStatus::Signed;
    }

    public function formattedAmount(): ?string
    {
        if ($this->amount_cents === null) {
            return null;
        }

        return '$'.number_format($this->amount_cents / 100, 2);
    }

    /**
     * The party name shown in lists: whoever is on the other side of the
     * exchange (customer for provide, vendor for collect).
     */
    public function counterpartyDisplayName(): string
    {
        return $this->counterparty_company
            ?: $this->counterparty_name
            ?: ($this->signer_email ?: 'Unnamed party');
    }

    /**
     * Saved waivers this calendar month, for free-tier metering. Voided
     * waivers still count: a save consumed the slot. The month boundary is
     * the product's Eastern display timezone (what the meter shows the user),
     * and forBusiness() bypasses the tenant global scope so admin/cross-tenant
     * callers count the right business.
     */
    public static function savedThisMonthFor(Business $business): int
    {
        $now = now()->eastern();

        return static::withTrashed()
            ->forBusiness($business)
            ->whereBetween('created_at', [$now->copy()->startOfMonth()->utc(), $now->copy()->endOfMonth()->utc()])
            ->count();
    }
}

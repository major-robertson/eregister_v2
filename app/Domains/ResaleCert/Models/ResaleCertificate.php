<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use App\Models\User;
use Database\Factories\ResaleCert\ResaleCertificateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * A generated resale certificate: one PDF issued to one vendor for one state
 * form (or the MTC/SST uniform form covering several states). Business and
 * vendor data are frozen into snapshots at generation time.
 */
class ResaleCertificate extends Model
{
    use BelongsToBusiness, HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'resale_vendor_id',
        'created_by_user_id',
        'state_code',
        'is_blanket',
        'item_description',
        'business_snapshot',
        'vendor_snapshot',
        'issue_date',
        'expiration_date',
        'pdf_path',
        'pdf_sha256',
    ];

    protected static function newFactory(): ResaleCertificateFactory
    {
        return ResaleCertificateFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_blanket' => 'boolean',
            'business_snapshot' => 'array',
            'vendor_snapshot' => 'array',
            'issue_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(ResaleVendor::class, 'resale_vendor_id')->withTrashed();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function stateRule(): BelongsTo
    {
        return $this->belongsTo(ResaleStateRule::class, 'state_code', 'state_code');
    }

    public function isUniform(): bool
    {
        return in_array($this->state_code, ['MTC', 'SST'], true);
    }

    public function isExpired(): bool
    {
        return $this->expiration_date !== null && $this->expiration_date->isPast();
    }

    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->expiration_date !== null
            && ! $this->isExpired()
            && now()->diffInDays($this->expiration_date, absolute: false) <= $days;
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isExpired() => 'Expired',
            $this->isExpiringSoon() => 'Expiring Soon',
            default => 'Active',
        };
    }

    public function statusColor(): string
    {
        return match (true) {
            $this->isExpired() => 'red',
            $this->isExpiringSoon() => 'amber',
            default => 'green',
        };
    }

    public function displayName(): string
    {
        return match ($this->state_code) {
            'MTC' => 'MTC Uniform Certificate',
            'SST' => 'SST Uniform Certificate',
            default => $this->stateRule?->state_name ?? $this->state_code,
        };
    }

    public function downloadFilename(): string
    {
        return sprintf(
            'resale_certificate_%s_%s_%s.pdf',
            $this->state_code,
            str_replace(' ', '_', $this->vendor_snapshot['legal_name'] ?? 'vendor'),
            $this->issue_date->format('Y-m-d'),
        );
    }

    public function deletePdfFile(): void
    {
        if ($this->pdf_path) {
            Storage::disk(config('resale_cert.disk'))->delete($this->pdf_path);
        }
    }
}

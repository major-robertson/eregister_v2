<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * In-app record of a certificate lifecycle notice (currently only
 * expiring-soon). Doubles as the dedup ledger so the daily expiration
 * check doesn't renotify the same certificate every run.
 */
class ResaleCertificateNotification extends Model
{
    use BelongsToBusiness;

    protected $fillable = [
        'business_id',
        'resale_certificate_id',
        'type',
        'severity',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function certificate(): BelongsTo
    {
        return $this->belongsTo(ResaleCertificate::class, 'resale_certificate_id');
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public static function notifyExpiring(ResaleCertificate $certificate, int $daysUntilExpiration): self
    {
        $severity = match (true) {
            $daysUntilExpiration <= 30 => 'danger',
            $daysUntilExpiration <= 60 => 'warning',
            default => 'info',
        };

        return static::create([
            'business_id' => $certificate->business_id,
            'resale_certificate_id' => $certificate->id,
            'type' => 'expiring_soon',
            'severity' => $severity,
            'title' => 'Certificate Expiring Soon',
            'message' => sprintf(
                'Certificate for %s (%s) expires in %d days',
                $certificate->vendor_snapshot['legal_name'] ?? 'vendor',
                $certificate->displayName(),
                $daysUntilExpiration,
            ),
            'data' => [
                'vendor_name' => $certificate->vendor_snapshot['legal_name'] ?? null,
                'state' => $certificate->displayName(),
                'expiration_date' => $certificate->expiration_date?->toDateString(),
                'days_until_expiration' => $daysUntilExpiration,
            ],
        ]);
    }
}

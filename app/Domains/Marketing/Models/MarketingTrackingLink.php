<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\DestinationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MarketingTrackingLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'token',
        'lead_id',
        'campaign_id',
        'campaign_step_id',
        'mailing_id',
        'destination_type',
        'destination',
        'qr_code_path',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'destination_type' => DestinationType::class,
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MarketingTrackingLink $link) {
            if (empty($link->public_id)) {
                $link->public_id = (string) Str::ulid();
            }
            if (empty($link->token)) {
                $link->token = static::generateToken();
            }
        });
    }

    /**
     * Generate a random base36 lowercase token.
     *
     * Using lowercase alphanumeric only to avoid case-sensitivity issues
     * with MySQL's utf8mb4_unicode_ci collation.
     */
    public static function generateToken(int $length = 12): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $token = '';

        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[random_int(0, 35)];
        }

        // Ensure uniqueness
        while (static::where('token', $token)->exists()) {
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= $chars[random_int(0, 35)];
            }
        }

        return $token;
    }

    /**
     * Get the lead.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class, 'lead_id');
    }

    /**
     * Get the campaign.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    /**
     * Get the campaign step.
     */
    public function campaignStep(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaignStep::class, 'campaign_step_id');
    }

    /**
     * Get the mailing.
     */
    public function mailing(): BelongsTo
    {
        return $this->belongsTo(MarketingMailing::class, 'mailing_id');
    }

    /**
     * Get the visits for this tracking link.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(MarketingVisit::class, 'tracking_link_id');
    }

    /**
     * Check if the tracking link has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Get the full URL for this tracking link.
     */
    public function getTrackingUrl(): string
    {
        return route('marketing.landing.token', ['token' => $this->token]);
    }

    /**
     * Get the canonical URL (the destination slug).
     */
    public function getCanonicalUrl(): string
    {
        if ($this->destination_type === DestinationType::LeadLanding && $this->lead) {
            return route('marketing.landing.slug', ['slug' => $this->lead->slug]);
        }

        return $this->destination;
    }

    /**
     * Get the S3 URL for the QR code.
     */
    public function getQrCodeUrl(): ?string
    {
        if (! $this->qr_code_path) {
            return null;
        }

        return \Storage::disk('s3')->url($this->qr_code_path);
    }
}

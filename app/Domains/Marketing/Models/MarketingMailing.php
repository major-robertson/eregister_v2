<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\MailingStatus;
use App\Domains\Marketing\Enums\MailProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class MarketingMailing extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'lead_campaign_id',
        'campaign_step_id',
        'idempotency_key',
        'provider',
        'provider_id',
        'provider_status',
        'provider_payload',
        'executed_at',
        'delivered_at',
        'failed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'provider' => MailProvider::class,
            'provider_status' => MailingStatus::class,
            'provider_payload' => 'array',
            'executed_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MarketingMailing $mailing) {
            if (empty($mailing->public_id)) {
                $mailing->public_id = (string) Str::ulid();
            }
            if (empty($mailing->idempotency_key)) {
                $mailing->idempotency_key = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the lead campaign enrollment.
     */
    public function leadCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingLeadCampaign::class, 'lead_campaign_id');
    }

    /**
     * Get the campaign step.
     */
    public function campaignStep(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaignStep::class, 'campaign_step_id');
    }

    /**
     * Get the tracking link for this mailing.
     */
    public function trackingLink(): HasOne
    {
        return $this->hasOne(MarketingTrackingLink::class, 'mailing_id');
    }

    /**
     * Get the visits for this mailing.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(MarketingVisit::class, 'mailing_id');
    }

    /**
     * Get the events for this mailing.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'mailing_id');
    }

    /**
     * Get the lead through the lead campaign.
     */
    public function getLead(): ?MarketingLead
    {
        return $this->leadCampaign?->lead;
    }

    /**
     * Get the campaign through the lead campaign.
     */
    public function getCampaign(): ?MarketingCampaign
    {
        return $this->leadCampaign?->campaign;
    }

    /**
     * Check if the mailing was successfully executed.
     */
    public function isExecuted(): bool
    {
        return $this->executed_at !== null && $this->provider_id !== null;
    }

    /**
     * Check if the mailing is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->provider_status === MailingStatus::Completed;
    }

    /**
     * Check if the mailing failed.
     */
    public function isFailed(): bool
    {
        return $this->failed_at !== null || $this->provider_status === MailingStatus::Cancelled;
    }

    /**
     * Mark the mailing as executed.
     */
    public function markExecuted(string $providerId, array $payload = []): void
    {
        $this->provider_id = $providerId;
        $this->provider_payload = $payload;
        $this->provider_status = MailingStatus::Ready;
        $this->executed_at = now();
        $this->save();
    }

    /**
     * Update status from webhook payload.
     */
    public function updateFromWebhook(array $payload): void
    {
        $this->provider_payload = $payload;

        $status = $payload['status'] ?? null;
        if ($status) {
            $this->provider_status = MailingStatus::tryFrom($status);

            if ($this->provider_status === MailingStatus::Completed && ! $this->delivered_at) {
                $this->delivered_at = now();
            }

            if ($this->provider_status === MailingStatus::Cancelled && ! $this->failed_at) {
                $this->failed_at = now();
            }
        }

        $this->save();
    }
}

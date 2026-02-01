<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\EventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'lead_id',
        'campaign_id',
        'campaign_step_id',
        'mailing_id',
        'event_type',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * Get the visit.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(MarketingVisit::class, 'visit_id');
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
     * Record an event from a visit.
     */
    public static function recordFromVisit(
        MarketingVisit $visit,
        EventType $eventType,
        array $metadata = []
    ): self {
        return static::create([
            'visit_id' => $visit->id,
            'lead_id' => $visit->lead_id,
            'campaign_id' => $visit->trackingLink?->campaign_id,
            'campaign_step_id' => $visit->trackingLink?->campaign_step_id,
            'mailing_id' => $visit->mailing_id,
            'event_type' => $eventType,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}

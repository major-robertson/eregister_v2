<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\CampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'landing_key',
    ];

    protected function casts(): array
    {
        return [
            'status' => CampaignStatus::class,
        ];
    }

    /**
     * Get the steps for this campaign, ordered.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(MarketingCampaignStep::class, 'campaign_id')->orderBy('order');
    }

    /**
     * Get the lead enrollments for this campaign.
     */
    public function leadCampaigns(): HasMany
    {
        return $this->hasMany(MarketingLeadCampaign::class, 'campaign_id');
    }

    /**
     * Get the tracking links for this campaign.
     */
    public function trackingLinks(): HasMany
    {
        return $this->hasMany(MarketingTrackingLink::class, 'campaign_id');
    }

    /**
     * Get the events for this campaign.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'campaign_id');
    }

    /**
     * Check if the campaign is active.
     */
    public function isActive(): bool
    {
        return $this->status === CampaignStatus::Active;
    }

    /**
     * Get the first step of the campaign.
     */
    public function getFirstStep(): ?MarketingCampaignStep
    {
        return $this->steps()->orderBy('order')->first();
    }

    /**
     * Get the next step after the given order.
     */
    public function getNextStep(int $currentOrder): ?MarketingCampaignStep
    {
        return $this->steps()->where('order', '>', $currentOrder)->orderBy('order')->first();
    }
}

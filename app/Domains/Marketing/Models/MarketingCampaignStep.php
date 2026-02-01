<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\CampaignStepType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaignStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'order',
        'type',
        'delay_days',
        'template_key',
        'provider_template_ref',
        'mailpiece_options',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => CampaignStepType::class,
            'delay_days' => 'integer',
            'order' => 'integer',
            'provider_template_ref' => 'array',
            'mailpiece_options' => 'array',
        ];
    }

    /**
     * Get the campaign this step belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    /**
     * Get the mailings for this step.
     */
    public function mailings(): HasMany
    {
        return $this->hasMany(MarketingMailing::class, 'campaign_step_id');
    }

    /**
     * Get the tracking links for this step.
     */
    public function trackingLinks(): HasMany
    {
        return $this->hasMany(MarketingTrackingLink::class, 'campaign_step_id');
    }

    /**
     * Get the events for this step.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'campaign_step_id');
    }

    /**
     * Check if this is a letter step.
     */
    public function isLetter(): bool
    {
        return $this->type === CampaignStepType::Letter;
    }

    /**
     * Check if this is a postcard step.
     */
    public function isPostcard(): bool
    {
        return $this->type === CampaignStepType::Postcard;
    }

    /**
     * Get the template ID for letters.
     */
    public function getLetterTemplateId(): ?string
    {
        return $this->provider_template_ref['templateId'] ?? null;
    }

    /**
     * Get the front template ID for postcards.
     */
    public function getPostcardFrontTemplateId(): ?string
    {
        return $this->provider_template_ref['frontTemplateId'] ?? null;
    }

    /**
     * Get the back template ID for postcards.
     */
    public function getPostcardBackTemplateId(): ?string
    {
        return $this->provider_template_ref['backTemplateId'] ?? null;
    }

    /**
     * Get the postcard size option.
     */
    public function getPostcardSize(): string
    {
        return $this->mailpiece_options['size'] ?? '6x4';
    }
}

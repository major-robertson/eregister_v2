<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\LeadCampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingLeadCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'current_step_order',
        'status',
        'next_action_at',
        'last_step_executed_at',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadCampaignStatus::class,
            'current_step_order' => 'integer',
            'next_action_at' => 'datetime',
            'last_step_executed_at' => 'datetime',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (MarketingLeadCampaign $enrollment) {
            // Update vanity tracking link with campaign_id
            MarketingTrackingLink::where('lead_id', $enrollment->lead_id)
                ->where('token', $enrollment->lead->slug)
                ->whereNull('mailing_id')
                ->update(['campaign_id' => $enrollment->campaign_id]);
        });
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
     * Get the mailings for this enrollment.
     */
    public function mailings(): HasMany
    {
        return $this->hasMany(MarketingMailing::class, 'lead_campaign_id');
    }

    /**
     * Check if the enrollment is due for action.
     */
    public function isDue(): bool
    {
        if (! in_array($this->status, [LeadCampaignStatus::Pending, LeadCampaignStatus::InProgress])) {
            return false;
        }

        return $this->next_action_at !== null && $this->next_action_at->isPast();
    }

    /**
     * Get the current step based on the current_step_order.
     */
    public function getCurrentStep(): ?MarketingCampaignStep
    {
        return $this->campaign->steps()->where('order', $this->current_step_order)->first();
    }

    /**
     * Get the next step to execute.
     */
    public function getNextStep(): ?MarketingCampaignStep
    {
        return $this->campaign->getNextStep($this->current_step_order);
    }

    /**
     * Mark the current step as executed and compute next_action_at.
     */
    public function markStepExecuted(): void
    {
        $this->last_step_executed_at = now();
        $this->status = LeadCampaignStatus::InProgress;

        $nextStep = $this->getNextStep();

        if ($nextStep) {
            $this->current_step_order = $nextStep->order;
            $this->next_action_at = $this->last_step_executed_at->addDays($nextStep->delay_days);
        } else {
            // No more steps, mark as completed
            $this->status = LeadCampaignStatus::Completed;
            $this->completed_at = now();
            $this->next_action_at = null;
        }

        $this->save();
    }

    /**
     * Mark the enrollment as failed.
     */
    public function markFailed(?string $reason = null): void
    {
        $this->status = LeadCampaignStatus::Failed;
        $this->next_action_at = null;
        $this->save();
    }

    /**
     * Initialize the enrollment for the first step.
     */
    public function initializeForFirstStep(): void
    {
        $firstStep = $this->campaign->getFirstStep();

        if (! $firstStep) {
            return;
        }

        $this->enrolled_at = now();
        $this->current_step_order = $firstStep->order;
        $this->status = LeadCampaignStatus::Pending;
        $this->next_action_at = now()->addDays($firstStep->delay_days);
        $this->save();
    }
}

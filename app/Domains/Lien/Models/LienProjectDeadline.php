<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\DeadlineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LienProjectDeadline extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'project_id',
        'deadline_rule_id',
        'document_type_id',
        'due_date',
        'computed_from_date',
        'missing_fields_json',
        'status',
        'completed_filing_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'computed_from_date' => 'date',
            'missing_fields_json' => 'array',
            'status' => DeadlineStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(LienProject::class, 'project_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(LienDeadlineRule::class, 'deadline_rule_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(LienDocumentType::class, 'document_type_id');
    }

    public function completedFiling(): BelongsTo
    {
        return $this->belongsTo(LienFiling::class, 'completed_filing_id');
    }

    public function filings(): HasMany
    {
        return $this->hasMany(LienFiling::class, 'project_deadline_id');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(LienNotificationLog::class, 'project_deadline_id');
    }

    /**
     * Get the current draft filing for this deadline, if any.
     */
    public function draftFiling(): HasOne
    {
        return $this->hasOne(LienFiling::class, 'project_deadline_id')
            ->where('status', 'draft');
    }

    /**
     * Calculate days remaining until deadline in the business's timezone.
     */
    public function daysRemaining(): ?int
    {
        if (! $this->due_date) {
            return null;
        }

        $timezone = $this->project->business->timezone ?? 'America/Los_Angeles';
        $now = now()->timezone($timezone)->startOfDay();
        $due = $this->due_date->timezone($timezone)->startOfDay();

        return (int) $now->diffInDays($due, false);
    }

    /**
     * Check if this deadline is overdue.
     */
    public function isOverdue(): bool
    {
        if (! $this->due_date || $this->status !== DeadlineStatus::Pending) {
            return false;
        }

        return $this->daysRemaining() < 0;
    }

    /**
     * Check if this deadline is due soon (within 7 days).
     */
    public function isDueSoon(): bool
    {
        if (! $this->due_date || $this->status !== DeadlineStatus::Pending) {
            return false;
        }

        $days = $this->daysRemaining();

        return $days !== null && $days >= 0 && $days <= 7;
    }

    /**
     * Check if the deadline rule is a placeholder (unverified).
     */
    public function isPlaceholder(): bool
    {
        return $this->rule?->is_placeholder ?? true;
    }

    /**
     * Check if this deadline has missing required information.
     */
    public function hasMissingFields(): bool
    {
        return ! empty($this->missing_fields_json);
    }

    /**
     * Check if filing can be started for this deadline.
     */
    public function canFile(): bool
    {
        // Already completed - cannot file again
        if ($this->status === DeadlineStatus::Completed) {
            return false;
        }

        $slug = $this->documentType?->slug;

        // Preliminary notice: always available unless already filed
        // Can file even with missing dates - user can enter them during filing
        if ($slug === 'prelim_notice') {
            return true;
        }

        // For other document types, check additional requirements

        // Missing required fields blocks filing for non-prelim documents
        if ($this->hasMissingFields()) {
            return false;
        }

        // Not applicable deadlines cannot be filed
        if ($this->status === DeadlineStatus::NotApplicable) {
            return false;
        }

        // Lien release: requires mechanics lien to be filed first
        if ($slug === 'lien_release') {
            return $this->hasPriorFilingCompleted();
        }

        // NOI and mechanics lien: no special restrictions beyond status checks
        return true;
    }

    /**
     * Get the reason why filing is blocked, or null if filing is available.
     */
    public function getFilingBlockerReason(): ?string
    {
        // Already completed
        if ($this->status === DeadlineStatus::Completed) {
            return 'Already Filed';
        }

        $slug = $this->documentType?->slug;

        // Preliminary notice: always available unless already filed
        if ($slug === 'prelim_notice') {
            return null;
        }

        // For other document types, check additional requirements

        // Missing required fields blocks filing for non-prelim documents
        if ($this->hasMissingFields()) {
            return 'Needs Info';
        }

        // Not applicable
        if ($this->status === DeadlineStatus::NotApplicable) {
            return 'Not Applicable';
        }

        // Lien release: check if mechanics lien is filed
        if ($slug === 'lien_release' && ! $this->hasPriorFilingCompleted()) {
            return 'Lien Required';
        }

        return null;
    }

    /**
     * Check if this deadline requires a prior filing (e.g., lien release requires lien).
     */
    public function requiresPriorFiling(): bool
    {
        return $this->documentType?->slug === 'lien_release';
    }

    /**
     * Check if the required prior filing has been completed.
     * For lien release, this checks if a mechanics lien has been RECORDED
     * (not just submitted - must have recorded_at timestamp set).
     */
    public function hasPriorFilingCompleted(): bool
    {
        if (! $this->requiresPriorFiling()) {
            return true;
        }

        // Lien release requires mechanics lien to be RECORDED (not just submitted)
        return $this->project->filings()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->whereNotNull('recorded_at')
            ->exists();
    }

    /**
     * Get the filing status label for display.
     */
    public function getFilingStatusLabel(): string
    {
        if ($this->status === DeadlineStatus::Completed) {
            return 'Filed';
        }

        $blocker = $this->getFilingBlockerReason();
        if ($blocker) {
            return $blocker;
        }

        return 'Ready';
    }

    /**
     * Get the appropriate color for the filing status badge.
     */
    public function getFilingStatusColor(): string
    {
        if ($this->status === DeadlineStatus::Completed) {
            return 'green';
        }

        $blocker = $this->getFilingBlockerReason();
        if ($blocker) {
            // "Needs Info" and "Lien Required" get amber, "Not Applicable" gets zinc
            if ($blocker === 'Not Applicable') {
                return 'zinc';
            }

            return 'amber';
        }

        return 'blue';
    }

    /**
     * Get the status label for display.
     * Returns: Filed, Not Applicable, Needs Info, or Ready.
     */
    public function getStatusLabel(): string
    {
        if ($this->status === DeadlineStatus::Completed) {
            return 'Filed';
        }
        if ($this->status === DeadlineStatus::NotApplicable) {
            return 'Not Applicable';
        }
        if ($this->hasMissingFields()) {
            return 'Needs Info';
        }

        return 'Ready';
    }

    /**
     * Get the status color based on status/condition (not label text).
     * This avoids brittle code where label changes break colors.
     */
    public function getStatusColor(): string
    {
        if ($this->status === DeadlineStatus::Completed) {
            return 'green';
        }
        if ($this->status === DeadlineStatus::NotApplicable) {
            return 'zinc';
        }
        if ($this->hasMissingFields()) {
            return 'amber';
        }

        return 'blue';
    }

    /**
     * Get the button text based on current state.
     */
    public function getButtonText(): string
    {
        if ($this->completedFiling) {
            return 'View Filing';
        }
        if ($this->draftFiling()->exists()) {
            return 'Continue';
        }
        if ($this->hasMissingFields()) {
            return "Start filing (we'll ask a few questions)";
        }

        return 'Start Filing';
    }

    /**
     * Check if filing can be started for this deadline.
     * "Needs Info" does NOT block starting - the wizard collects missing info.
     * Only Completed and NotApplicable block starting.
     */
    public function canStart(): bool
    {
        return $this->status !== DeadlineStatus::Completed
            && $this->status !== DeadlineStatus::NotApplicable;
    }
}

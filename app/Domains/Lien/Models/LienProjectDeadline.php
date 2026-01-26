<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\DeadlineStatus;
use Database\Factories\Lien\LienProjectDeadlineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LienProjectDeadline extends Model
{
    use BelongsToBusiness, HasFactory;

    protected static function newFactory(): LienProjectDeadlineFactory
    {
        return LienProjectDeadlineFactory::new();
    }

    protected $fillable = [
        'business_id',
        'project_id',
        'deadline_rule_id',
        'document_type_id',
        'due_date',
        'computed_from_date',
        'missing_fields_json',
        'status',
        'status_reason',
        'status_meta',
        'completed_filing_id',
        // External completion fields
        'completed_externally_at',
        'external_filed_at',
        'external_completion_note',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'computed_from_date' => 'date',
            'missing_fields_json' => 'array',
            'status' => DeadlineStatus::class,
            'status_meta' => 'array',
            'completed_externally_at' => 'datetime',
            'external_filed_at' => 'date',
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
        if (! $this->due_date) {
            return false;
        }

        // Only consider overdue if not already completed
        if ($this->status === DeadlineStatus::Completed) {
            return false;
        }

        return $this->daysRemaining() < 0;
    }

    /**
     * Check if this deadline is due soon (within 7 days).
     */
    public function isDueSoon(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        // Only consider due soon if not already completed
        if ($this->status === DeadlineStatus::Completed) {
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
     * Check if this deadline was completed externally (user filed themselves).
     */
    public function wasCompletedExternally(): bool
    {
        return $this->completed_externally_at !== null;
    }

    /**
     * Check if this deadline is completed (either through filing or externally).
     */
    public function isCompleted(): bool
    {
        return $this->status === DeadlineStatus::Completed
            || $this->completed_filing_id !== null
            || $this->completed_externally_at !== null;
    }

    /**
     * Check if filing can be started for this deadline.
     * Most deadlines can be started - user may have done prior steps themselves.
     */
    public function canFile(): bool
    {
        // Already completed - cannot file again
        if ($this->status === DeadlineStatus::Completed) {
            return false;
        }

        // Not applicable deadlines cannot be filed
        if ($this->status === DeadlineStatus::NotApplicable) {
            return false;
        }

        $slug = $this->documentType?->slug;

        // Preliminary notice: always available unless already filed
        // Can file even with missing dates - user can enter them during filing
        if ($slug === 'prelim_notice') {
            return true;
        }

        // Lien release: requires mechanics lien to be filed first
        if ($slug === 'lien_release') {
            return $this->hasPriorFilingCompleted();
        }

        // NOI and mechanics lien: can start even with missing info
        // The wizard will collect missing info
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

        // Not applicable
        if ($this->status === DeadlineStatus::NotApplicable) {
            return 'Not Applicable';
        }

        // Locked by purchase conflict
        if ($this->status === DeadlineStatus::Locked) {
            return 'Locked';
        }

        $slug = $this->documentType?->slug;

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
        // OR completed externally
        $hasRecordedFiling = $this->project->filings()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->whereNotNull('recorded_at')
            ->exists();

        if ($hasRecordedFiling) {
            return true;
        }

        // Check if lien was completed externally
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        return $lienDeadline?->wasCompletedExternally() ?? false;
    }

    /**
     * Get the status label for display.
     * Delegates to the enum's label() method.
     */
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    /**
     * Get the status color based on status.
     * Delegates to the enum's color() method.
     */
    public function getStatusColor(): string
    {
        return $this->status->color();
    }

    /**
     * Get the friendly message for the status_reason code.
     */
    public function getFriendlyStatusReason(): ?string
    {
        if (! $this->status_reason) {
            return null;
        }

        return config("lien.status_reasons.{$this->status_reason}", $this->status_reason);
    }

    /**
     * Get the button text based on current state.
     */
    public function getButtonText(): string
    {
        if ($this->completedFiling) {
            return 'View Filing';
        }
        if ($this->wasCompletedExternally()) {
            return 'View Details';
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
     * Most deadlines can be started - the wizard collects missing info.
     */
    public function canStart(): bool
    {
        return ! in_array($this->status, [
            DeadlineStatus::Completed,
            DeadlineStatus::NotApplicable,
        ], true);
    }

    /**
     * Check if this deadline is required (not optional).
     */
    public function isRequired(): bool
    {
        return $this->rule?->is_required ?? true;
    }

    /**
     * Check if this deadline is optional.
     */
    public function isOptional(): bool
    {
        return ! $this->isRequired();
    }

    /**
     * Mark this deadline as completed externally (user filed themselves).
     */
    public function markCompletedExternally(string $filedAt, ?string $note = null): void
    {
        $this->update([
            'status' => DeadlineStatus::Completed,
            'completed_externally_at' => now(),
            'external_filed_at' => $filedAt,
            'external_completion_note' => $note,
        ]);
    }
}

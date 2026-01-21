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
}

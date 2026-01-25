<?php

namespace App\Domains\Lien\Admin\Enums;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;

enum KanbanColumn: string
{
    case New = 'new';
    case ManagerReview = 'manager_review';
    case Processing = 'processing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::ManagerReview => 'Manager Review',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::ManagerReview => 'amber',
            self::Processing => 'blue',
            self::Completed => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::ManagerReview => 'exclamation-triangle',
            self::Processing => 'arrow-path',
            self::Completed => 'check-circle',
        };
    }

    /**
     * Determine which column a filing belongs to.
     * Uses rules, not just status mapping.
     */
    public static function forFiling(LienFiling $filing): self
    {
        // Manager Review takes priority - flagged filings go here
        if ($filing->needs_review) {
            return self::ManagerReview;
        }

        // Completed
        if ($filing->status === FilingStatus::Complete) {
            return self::Completed;
        }

        // Processing - active fulfillment statuses
        if (in_array($filing->status, [
            FilingStatus::InFulfillment,
            FilingStatus::Mailed,
            FilingStatus::Recorded,
        ], true)) {
            return self::Processing;
        }

        // New - paid and ready for processing
        if ($filing->paid_at !== null && in_array($filing->status, [
            FilingStatus::Paid,
        ], true)) {
            return self::New;
        }

        // Fallback: put unhandled statuses in New
        // This prevents null group keys and silent disappearance
        return self::New;
    }
}

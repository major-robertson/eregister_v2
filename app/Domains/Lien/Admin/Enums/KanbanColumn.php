<?php

namespace App\Domains\Lien\Admin\Enums;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;

enum KanbanColumn: string
{
    case New = 'new';
    case NeedsReview = 'needs_review';
    case ReadyToSend = 'ready_to_send';
    case WaitingOnNextStep = 'waiting_on_next_step';
    case Hold = 'hold';
    case Mailed = 'mailed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::NeedsReview => 'Needs Review',
            self::ReadyToSend => 'Ready to Send',
            self::WaitingOnNextStep => 'Waiting on Next Step',
            self::Hold => 'Hold',
            self::Mailed => 'Mailed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::NeedsReview => 'amber',
            self::ReadyToSend => 'lime',
            self::WaitingOnNextStep => 'cyan',
            self::Hold => 'red',
            self::Mailed => 'indigo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::NeedsReview => 'eye',
            self::ReadyToSend => 'paper-airplane',
            self::WaitingOnNextStep => 'queue-list',
            self::Hold => 'pause-circle',
            self::Mailed => 'envelope',
        };
    }

    /**
     * Determine which column a filing belongs to.
     */
    public static function forFiling(LienFiling $filing): self
    {
        return match ($filing->status) {
            FilingStatus::NeedsReview => self::NeedsReview,
            FilingStatus::ReadyToSend => self::ReadyToSend,
            FilingStatus::WaitingOnNextStep => self::WaitingOnNextStep,
            FilingStatus::Hold => self::Hold,
            FilingStatus::Mailed => self::Mailed,
            default => self::New,
        };
    }
}

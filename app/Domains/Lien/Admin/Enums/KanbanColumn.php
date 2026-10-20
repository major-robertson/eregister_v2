<?php

namespace App\Domains\Lien\Admin\Enums;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use LogicException;

enum KanbanColumn: string
{
    case New = 'new';
    case InProgress = 'in_progress';
    case WaitingOnCustomer = 'waiting_on_customer';
    case NeedsReview = 'needs_review';
    case ReadyToFile = 'ready_to_file';
    case WaitingOnNextStep = 'waiting_on_next_step';
    case Hold = 'hold';
    case SubmittedForRecording = 'submitted_for_recording';
    case Recorded = 'recorded';
    case Mailed = 'mailed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::InProgress => 'In Progress',
            self::WaitingOnCustomer => 'Waiting on customer',
            self::NeedsReview => 'Needs Review',
            self::ReadyToFile => 'Ready to File',
            self::WaitingOnNextStep => 'Waiting on Next Step',
            self::Hold => 'Hold',
            self::SubmittedForRecording => 'Submitted for Recording',
            self::Recorded => 'Recorded',
            self::Mailed => 'Mailed to Parties',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'sky',
            self::InProgress => 'blue',
            self::WaitingOnCustomer => 'orange',
            self::NeedsReview => 'amber',
            self::ReadyToFile => 'lime',
            self::WaitingOnNextStep => 'cyan',
            self::Hold => 'red',
            self::SubmittedForRecording => 'teal',
            self::Recorded => 'violet',
            self::Mailed => 'indigo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::InProgress => 'wrench',
            self::WaitingOnCustomer => 'user',
            self::NeedsReview => 'eye',
            self::ReadyToFile => 'paper-airplane',
            self::WaitingOnNextStep => 'queue-list',
            self::Hold => 'pause-circle',
            self::SubmittedForRecording => 'arrow-up-tray',
            self::Recorded => 'document-check',
            self::Mailed => 'envelope',
        };
    }

    /**
     * The column a status puts a filing in, or null for the statuses the
     * board leaves out (not paid yet, or finished). Every status has an arm
     * and there is no default, so a new FilingStatus fails here until someone
     * decides where it goes, instead of landing in New unnoticed.
     */
    public static function tryForStatus(FilingStatus $status): ?self
    {
        return match ($status) {
            FilingStatus::Paid => self::New,
            FilingStatus::InFulfillment => self::InProgress,
            FilingStatus::AwaitingClient, FilingStatus::AwaitingEsign, FilingStatus::AwaitingNotary => self::WaitingOnCustomer,
            FilingStatus::NeedsReview => self::NeedsReview,
            FilingStatus::ReadyToFile => self::ReadyToFile,
            FilingStatus::WaitingOnNextStep => self::WaitingOnNextStep,
            FilingStatus::Hold => self::Hold,
            FilingStatus::SubmittedForRecording => self::SubmittedForRecording,
            FilingStatus::Recorded => self::Recorded,
            FilingStatus::Mailed => self::Mailed,
            FilingStatus::Draft,
            FilingStatus::AwaitingPayment,
            FilingStatus::Complete,
            FilingStatus::Canceled,
            FilingStatus::Refunded => null,
        };
    }

    public static function tryForFiling(LienFiling $filing): ?self
    {
        return self::tryForStatus($filing->status);
    }

    /**
     * Determine which column a filing's status puts it in.
     *
     * @throws LogicException for a status the board leaves out
     */
    public static function forFiling(LienFiling $filing): self
    {
        return self::tryForFiling($filing)
            ?? throw new LogicException("Filing status {$filing->status->value} has no board column.");
    }

    /**
     * The statuses the board leaves out: Draft, Awaiting Payment, Complete,
     * Canceled and Refunded.
     *
     * @return list<FilingStatus>
     */
    public static function offBoardStatuses(): array
    {
        return array_values(array_filter(
            FilingStatus::cases(),
            fn (FilingStatus $status): bool => self::tryForStatus($status) === null,
        ));
    }
}

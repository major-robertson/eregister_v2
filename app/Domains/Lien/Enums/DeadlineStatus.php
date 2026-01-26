<?php

namespace App\Domains\Lien\Enums;

/**
 * UX-driven deadline statuses.
 *
 * The "why" (restrictions, rights eliminated, etc.) is stored in
 * status_reason and status_meta fields on the deadline model,
 * not encoded into the status itself.
 */
enum DeadlineStatus: string
{
    case NotStarted = 'not_started';           // neutral - available to start
    case DeadlineUnknown = 'deadline_unknown'; // neutral gray - missing info
    case InDraft = 'in_draft';                 // has draft filing
    case AwaitingPayment = 'awaiting_payment'; // filing awaiting payment
    case Purchased = 'purchased';              // paid, not yet in fulfillment
    case InFulfillment = 'in_fulfillment';     // in fulfillment process
    case Completed = 'completed';              // done (paid or external)
    case DueSoon = 'due_soon';                 // amber - within threshold
    case Missed = 'missed';                    // red - deadline passed
    case Locked = 'locked';                    // gray w/ lock - purchase conflict
    case NotApplicable = 'not_applicable';     // gray N/A (no rights, blocked)

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::DeadlineUnknown => 'Deadline Unknown',
            self::InDraft => 'Draft',
            self::AwaitingPayment => 'Awaiting Payment',
            self::Purchased => 'Purchased',
            self::InFulfillment => 'In Progress',
            self::Completed => 'Completed',
            self::DueSoon => 'Due Soon',
            self::Missed => 'Overdue',
            self::Locked => 'Locked',
            self::NotApplicable => 'N/A',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NotStarted => 'blue',
            self::DeadlineUnknown => 'zinc',
            self::InDraft => 'zinc',
            self::AwaitingPayment => 'amber',
            self::Purchased => 'sky',
            self::InFulfillment => 'blue',
            self::Completed => 'green',
            self::DueSoon => 'amber',
            self::Missed => 'red',
            self::Locked => 'zinc',
            self::NotApplicable => 'zinc',
        };
    }

    /**
     * Check if this status represents a terminal/completed state.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Completed,
            self::NotApplicable,
        ], true);
    }

    /**
     * Check if this status allows starting a new filing.
     */
    public function canStartFiling(): bool
    {
        return ! in_array($this, [
            self::Completed,
            self::NotApplicable,
            self::Purchased,
            self::InFulfillment,
            self::AwaitingPayment,
        ], true);
    }

    /**
     * Check if this status represents an active filing in progress.
     */
    public function hasActiveFiling(): bool
    {
        return in_array($this, [
            self::InDraft,
            self::AwaitingPayment,
            self::Purchased,
            self::InFulfillment,
        ], true);
    }
}

<?php

namespace App\Domains\Forms\Enums;

/**
 * Admin workflow status for an individual FormApplicationState (one card
 * on the workspace kanban board). Each state has its own lifecycle —
 * a CA + TX + NY application produces three independent cards.
 *
 * Approved is the only terminal status. Every other status can
 * transition to any other non-self status, including post-submission
 * cases (state agency rejects, requests more info, etc.).
 */
enum FormApplicationStateAdminStatus: string
{
    case New = 'new';
    case NeedsReview = 'needs_review';
    case AwaitingClient = 'awaiting_client';
    case Hold = 'hold';
    case SubmittedToState = 'submitted_to_state';
    case Rejected = 'rejected';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::NeedsReview => 'Needs Review',
            self::AwaitingClient => 'Awaiting Client',
            self::Hold => 'Hold',
            self::SubmittedToState => 'Submitted',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::NeedsReview => 'amber',
            self::AwaitingClient => 'cyan',
            self::Hold => 'zinc',
            self::SubmittedToState => 'indigo',
            self::Rejected => 'red',
            self::Approved => 'green',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::NeedsReview => 'eye',
            self::AwaitingClient => 'user-circle',
            self::Hold => 'pause-circle',
            self::SubmittedToState => 'arrow-up-tray',
            self::Rejected => 'x-circle',
            self::Approved => 'check-circle',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Approved;
    }

    /**
     * Return the set of statuses this status is allowed to transition to.
     * Approved is terminal (empty array). Every other status can move
     * to any other non-self status — admins routinely move work in
     * unexpected directions (state rejects after submission, customer
     * resurfaces a held item, etc.).
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        if ($this->isTerminal()) {
            return [];
        }

        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => $case !== $this,
        ));
    }
}

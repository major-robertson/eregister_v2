<?php

namespace App\Domains\Forms\Admin\Enums;

use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplicationState;

/**
 * Kanban column for the Sales Tax admin board. Currently a 1:1 mapping
 * with FormApplicationStateAdminStatus, but kept as a separate enum so
 * the UI grouping stays decoupled from the underlying status (matches
 * the lien KanbanColumn pattern). Future column groupings (e.g. fold
 * SubmittedToState + Rejected into one "With State" column) can change
 * here without touching the status enum.
 */
enum SalesTaxKanbanColumn: string
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
        return $this->status()->label();
    }

    public function color(): string
    {
        return $this->status()->color();
    }

    public function icon(): string
    {
        return $this->status()->icon();
    }

    /**
     * The default board hides AwaitingClient (parked on customer, not
     * active admin work) and Approved (terminal — would otherwise pile
     * up forever). The /board/all view uses cases() to show everything.
     *
     * @return array<int, self>
     */
    public static function defaultBoardCases(): array
    {
        return [
            self::New,
            self::NeedsReview,
            self::SubmittedToState,
            self::Rejected,
            self::Hold,
        ];
    }

    /**
     * Map a FormApplicationState to its kanban column based on the
     * current admin status.
     */
    public static function forState(FormApplicationState $state): self
    {
        return self::from($state->current_admin_status->value);
    }

    private function status(): FormApplicationStateAdminStatus
    {
        return FormApplicationStateAdminStatus::from($this->value);
    }
}

<?php

namespace App\Domains\Lien\Enums;

enum FilingStatus: string
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case InFulfillment = 'in_fulfillment';
    case Mailed = 'mailed';
    case Recorded = 'recorded';
    case Complete = 'complete';
    case Canceled = 'canceled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AwaitingPayment => 'Awaiting Payment',
            self::Paid => 'Submitted',
            self::InFulfillment => 'In Fulfillment',
            self::Mailed => 'Mailed',
            self::Recorded => 'Recorded',
            self::Complete => 'Complete',
            self::Canceled => 'Canceled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::AwaitingPayment => 'amber',
            self::Paid => 'sky',
            self::InFulfillment => 'blue',
            self::Mailed => 'indigo',
            self::Recorded => 'violet',
            self::Complete => 'green',
            self::Canceled => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'pencil',
            self::AwaitingPayment => 'credit-card',
            self::Paid => 'check',
            self::InFulfillment => 'clock',
            self::Mailed => 'envelope',
            self::Recorded => 'document-check',
            self::Complete => 'check-circle',
            self::Canceled => 'x-circle',
        };
    }

    /**
     * Get a user-friendly description of what's happening at this status.
     */
    public function userDescription(): string
    {
        return match ($this) {
            self::Draft => 'Your filing is saved as a draft.',
            self::AwaitingPayment => 'Your filing is ready. Complete payment to proceed.',
            self::Paid => 'Payment received. Your filing is being prepared.',
            self::InFulfillment => 'We are preparing and sending your notice.',
            self::Mailed => 'Your notice has been mailed to all recipients.',
            self::Recorded => 'Your document has been recorded with the county.',
            self::Complete => 'Your filing is complete. All notices have been delivered.',
            self::Canceled => 'This filing has been canceled.',
        };
    }

    /**
     * Get the "what's next" message for the user.
     */
    public function whatsNext(): ?string
    {
        return match ($this) {
            self::Draft => 'Complete the form and submit for processing.',
            self::AwaitingPayment => 'Complete payment to begin processing.',
            self::Paid, self::InFulfillment, self::Mailed, self::Recorded => 'We will email you when there is an update or if we need additional information.',
            self::Complete, self::Canceled => null,
        };
    }

    /**
     * Check if this is a user-visible milestone status.
     */
    public function isUserMilestone(): bool
    {
        return match ($this) {
            self::Paid, self::InFulfillment, self::Mailed, self::Recorded, self::Complete, self::Canceled => true,
            self::Draft, self::AwaitingPayment => false,
        };
    }
}

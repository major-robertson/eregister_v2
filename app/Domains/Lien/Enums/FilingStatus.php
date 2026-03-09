<?php

namespace App\Domains\Lien\Enums;

enum FilingStatus: string
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case AwaitingClient = 'awaiting_client';
    case AwaitingEsign = 'awaiting_esign';
    case InFulfillment = 'in_fulfillment';
    case Mailed = 'mailed';
    case Recorded = 'recorded';
    case Complete = 'complete';
    case Canceled = 'canceled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AwaitingPayment => 'Awaiting Payment',
            self::Paid => 'Submitted',
            self::AwaitingClient => 'Awaiting Client',
            self::AwaitingEsign => 'Awaiting E-Signature',
            self::InFulfillment => 'In Fulfillment',
            self::Mailed => 'Mailed',
            self::Recorded => 'Recorded',
            self::Complete => 'Complete',
            self::Canceled => 'Canceled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::AwaitingPayment => 'amber',
            self::Paid => 'sky',
            self::AwaitingClient => 'orange',
            self::AwaitingEsign => 'purple',
            self::InFulfillment => 'blue',
            self::Mailed => 'indigo',
            self::Recorded => 'violet',
            self::Complete => 'green',
            self::Canceled => 'red',
            self::Refunded => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'pencil',
            self::AwaitingPayment => 'credit-card',
            self::Paid => 'check',
            self::AwaitingClient => 'user',
            self::AwaitingEsign => 'pencil-square',
            self::InFulfillment => 'clock',
            self::Mailed => 'envelope',
            self::Recorded => 'document-check',
            self::Complete => 'check-circle',
            self::Canceled => 'x-circle',
            self::Refunded => 'arrow-uturn-left',
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
            self::AwaitingClient => 'We need additional information from you to continue processing your filing.',
            self::AwaitingEsign => 'An e-signature request has been sent to you. Please check your email.',
            self::InFulfillment => 'We are preparing and sending your notice.',
            self::Mailed => 'Your notice has been mailed to all recipients.',
            self::Recorded => 'Your document has been recorded with the county.',
            self::Complete => 'Your filing is complete. All notices have been delivered.',
            self::Canceled => 'This filing has been canceled.',
            self::Refunded => 'This filing has been refunded.',
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
            self::AwaitingClient => 'Please respond to our email so we can continue processing your filing.',
            self::AwaitingEsign => 'Please complete the e-signature request sent to your email.',
            self::Paid, self::InFulfillment, self::Mailed, self::Recorded => 'We will email you when there is an update or if we need additional information.',
            self::Complete, self::Canceled, self::Refunded => null,
        };
    }

    /**
     * Check if this is a user-visible milestone status.
     */
    public function isUserMilestone(): bool
    {
        return match ($this) {
            self::Paid, self::AwaitingClient, self::AwaitingEsign, self::InFulfillment, self::Mailed, self::Recorded, self::Complete, self::Canceled, self::Refunded => true,
            self::Draft, self::AwaitingPayment => false,
        };
    }
}

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
            self::Paid => 'Paid',
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
            self::Mailed => 'mail',
            self::Recorded => 'file-check',
            self::Complete => 'check-circle',
            self::Canceled => 'x-circle',
        };
    }
}

<?php

namespace App\Domains\Marketing\Enums;

enum MailingStatus: string
{
    case Ready = 'ready';
    case Printing = 'printing';
    case ProcessedForDelivery = 'processed_for_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Ready => 'Ready',
            self::Printing => 'Printing',
            self::ProcessedForDelivery => 'Processed for Delivery',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Ready => 'zinc',
            self::Printing => 'blue',
            self::ProcessedForDelivery => 'purple',
            self::Completed => 'green',
            self::Cancelled => 'red',
        };
    }
}

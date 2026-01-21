<?php

namespace App\Domains\Lien\Enums;

enum FulfillmentStatus: string
{
    case Queued = 'queued';
    case InProgress = 'in_progress';
    case WaitingOnCustomer = 'waiting_on_customer';
    case Done = 'done';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Queued',
            self::InProgress => 'In Progress',
            self::WaitingOnCustomer => 'Waiting on Customer',
            self::Done => 'Done',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Queued => 'zinc',
            self::InProgress => 'blue',
            self::WaitingOnCustomer => 'amber',
            self::Done => 'green',
        };
    }
}

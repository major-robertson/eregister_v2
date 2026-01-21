<?php

namespace App\Domains\Lien\Enums;

enum DeadlineStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Missed = 'missed';
    case NotApplicable = 'not_applicable';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Missed => 'Missed',
            self::NotApplicable => 'Not Applicable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Completed => 'green',
            self::Missed => 'red',
            self::NotApplicable => 'zinc',
        };
    }
}

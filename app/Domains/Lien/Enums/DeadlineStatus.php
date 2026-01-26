<?php

namespace App\Domains\Lien\Enums;

enum DeadlineStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Missed = 'missed';
    case NotApplicable = 'not_applicable';
    case Blocked = 'blocked';
    case Warning = 'warning';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Missed => 'Missed',
            self::NotApplicable => 'Not Applicable',
            self::Blocked => 'Blocked',
            self::Warning => 'Warning',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Completed => 'green',
            self::Missed => 'red',
            self::NotApplicable => 'zinc',
            self::Blocked => 'red',
            self::Warning => 'amber',
        };
    }
}

<?php

namespace App\Domains\Lien\Enums;

enum RecordingMethod: string
{
    case Erecord = 'erecord';
    case Mail = 'mail';

    public function label(): string
    {
        return match ($this) {
            self::Erecord => 'E-Recording',
            self::Mail => 'Mail to County',
        };
    }
}

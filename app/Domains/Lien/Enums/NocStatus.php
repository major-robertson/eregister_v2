<?php

namespace App\Domains\Lien\Enums;

enum NocStatus: string
{
    case Yes = 'yes';
    case No = 'no';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::Unknown => 'Not sure',
        };
    }
}

<?php

namespace App\Domains\Marketing\Enums;

enum MailProvider: string
{
    case PostGrid = 'postgrid';
    // Future: Lob, etc.

    public function label(): string
    {
        return match ($this) {
            self::PostGrid => 'PostGrid',
        };
    }
}

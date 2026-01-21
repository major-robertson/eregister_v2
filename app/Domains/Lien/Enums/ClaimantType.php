<?php

namespace App\Domains\Lien\Enums;

enum ClaimantType: string
{
    case Subcontractor = 'subcontractor';
    case Supplier = 'supplier';
    case Gc = 'gc';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Subcontractor => 'Subcontractor',
            self::Supplier => 'Material Supplier',
            self::Gc => 'General Contractor',
            self::Other => 'Other',
        };
    }
}

<?php

namespace App\Domains\Lien\Enums;

enum PartyRole: string
{
    case Claimant = 'claimant';
    case Customer = 'customer';
    case Owner = 'owner';
    case Gc = 'gc';
    case Lender = 'lender';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Claimant => 'Claimant (You)',
            self::Customer => 'Customer / Hiring Party',
            self::Owner => 'Property Owner',
            self::Gc => 'General Contractor',
            self::Lender => 'Construction Lender',
            self::Other => 'Other',
        };
    }
}

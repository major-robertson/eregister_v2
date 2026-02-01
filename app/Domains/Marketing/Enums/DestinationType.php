<?php

namespace App\Domains\Marketing\Enums;

enum DestinationType: string
{
    case LeadLanding = 'lead_landing';
    case Url = 'url';

    public function label(): string
    {
        return match ($this) {
            self::LeadLanding => 'Lead Landing Page',
            self::Url => 'External URL',
        };
    }
}

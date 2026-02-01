<?php

namespace App\Domains\Marketing\Enums;

enum CampaignStepType: string
{
    case Letter = 'letter';
    case Postcard = 'postcard';
    // Future: Email, Sms, AudienceSync

    public function label(): string
    {
        return match ($this) {
            self::Letter => 'Letter',
            self::Postcard => 'Postcard',
        };
    }
}

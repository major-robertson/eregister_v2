<?php

namespace App\Domains\Lien\Enums;

enum ServiceLevel: string
{
    case SelfServe = 'self_serve';
    case FullService = 'full_service';

    public function label(): string
    {
        return match ($this) {
            self::SelfServe => 'Self-Serve',
            self::FullService => 'Full Service',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SelfServe => 'Download your document and file it yourself',
            self::FullService => 'We handle everything: verification, mailing, and recording',
        };
    }
}

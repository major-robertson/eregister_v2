<?php

namespace App\Domains\Lien\Admin\Enums;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;

enum KanbanColumn: string
{
    case New = 'new';
    case AwaitingClient = 'awaiting_client';
    case AwaitingSignatures = 'awaiting_signatures';
    case Mailed = 'mailed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::AwaitingClient => 'Awaiting Client',
            self::AwaitingSignatures => 'Awaiting Signatures',
            self::Mailed => 'Mailed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::AwaitingClient => 'orange',
            self::AwaitingSignatures => 'purple',
            self::Mailed => 'indigo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::AwaitingClient => 'user',
            self::AwaitingSignatures => 'pencil-square',
            self::Mailed => 'envelope',
        };
    }

    /**
     * Determine which column a filing belongs to.
     */
    public static function forFiling(LienFiling $filing): self
    {
        if ($filing->status === FilingStatus::AwaitingClient) {
            return self::AwaitingClient;
        }

        if ($filing->status === FilingStatus::AwaitingEsign || $filing->status === FilingStatus::AwaitingNotary) {
            return self::AwaitingSignatures;
        }

        if ($filing->status === FilingStatus::Mailed) {
            return self::Mailed;
        }

        return self::New;
    }
}

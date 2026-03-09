<?php

namespace App\Domains\Lien\Admin\Enums;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;

enum KanbanColumn: string
{
    case New = 'new';
    case AwaitingClient = 'awaiting_client';
    case AwaitingEsign = 'awaiting_esign';
    case Mailed = 'mailed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::AwaitingClient => 'Awaiting Client',
            self::AwaitingEsign => 'Awaiting E-Signature',
            self::Mailed => 'Mailed',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::AwaitingClient => 'orange',
            self::AwaitingEsign => 'purple',
            self::Mailed => 'indigo',
            self::Refunded => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::New => 'inbox',
            self::AwaitingClient => 'user',
            self::AwaitingEsign => 'pencil-square',
            self::Mailed => 'envelope',
            self::Refunded => 'arrow-uturn-left',
        };
    }

    /**
     * Determine which column a filing belongs to.
     */
    public static function forFiling(LienFiling $filing): self
    {
        if ($filing->status === FilingStatus::Refunded) {
            return self::Refunded;
        }

        if ($filing->status === FilingStatus::AwaitingClient) {
            return self::AwaitingClient;
        }

        if ($filing->status === FilingStatus::AwaitingEsign) {
            return self::AwaitingEsign;
        }

        if ($filing->status === FilingStatus::Mailed) {
            return self::Mailed;
        }

        return self::New;
    }
}

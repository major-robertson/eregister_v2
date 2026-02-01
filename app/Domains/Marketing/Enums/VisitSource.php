<?php

namespace App\Domains\Marketing\Enums;

enum VisitSource: string
{
    case QrScan = 'qr_scan';
    case Direct = 'direct';
    case Referral = 'referral';

    public function label(): string
    {
        return match ($this) {
            self::QrScan => 'QR Scan',
            self::Direct => 'Direct',
            self::Referral => 'Referral',
        };
    }
}

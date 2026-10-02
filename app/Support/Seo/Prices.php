<?php

namespace App\Support\Seo;

/**
 * Prices quoted in page titles and meta descriptions. Read from the same
 * config the checkout uses, so a snippet can never advertise a stale price.
 */
final class Prices
{
    /** Default (non-state) lien price, e.g. lien('prelim_notice') => "$29". */
    public static function lien(string $type, string $level = 'self_serve'): string
    {
        return self::dollars((int) config("lien.pricing.{$type}.{$level}"));
    }

    /** Lien waiver Pro seat price, e.g. waiver('monthly') => "$49". */
    public static function waiver(string $interval = 'monthly'): string
    {
        return self::dollars((int) config("lien_waivers.prices.{$interval}.amount_cents"));
    }

    public static function dollars(int $cents): string
    {
        return '$'.number_format($cents / 100);
    }
}

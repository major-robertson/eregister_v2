<?php

namespace App\Support\Analytics;

use Carbon\CarbonInterface;

/**
 * Builds the `tableState` value that opens a Google Ads report on a fixed
 * date range, so a link in an email lands on the right week without anyone
 * touching the date picker.
 *
 * The value is a base64 protobuf. Everything except the date range is
 * constant and copied from a real URL; field 7 wraps field 1, which holds
 * the start and end dates as {1: year, 2: month, 3: day} messages.
 */
class AdsTableState
{
    /** The Google Ads account that holds the eRegister campaigns. */
    public const OCID = '91053260';

    /** A real tableState for 2026-09-20 to 2026-09-22; bytes 10-31 are the date range. */
    private const TEMPLATE = 'CggIABIEaW1wcjoUChIKBwjqDxAJGBQSBwjqDxAJGBZIAVABgAFkiAEB0AEB2AEDkAIAmgIuEhVzZWdtZW50YXRpb25faW5mby5kYXkaEXN0YXRzLmNvbnZlcnNpb25zGgA4AQ==';

    public static function forRange(CarbonInterface $start, CarbonInterface $end): string
    {
        $template = base64_decode(self::TEMPLATE, true);
        $head = substr($template, 0, 10);
        $tail = substr($template, 32);

        $inner = self::lengthDelimited(0x0A, self::date($start)).self::lengthDelimited(0x12, self::date($end));
        $range = self::lengthDelimited(0x3A, self::lengthDelimited(0x0A, $inner));

        return str_replace('=', '%3D', base64_encode($head.$range.$tail));
    }

    /**
     * Campaign table for the range, on the account's ocid.
     */
    public static function campaignsUrl(CarbonInterface $start, CarbonInterface $end): string
    {
        return 'https://ads.google.com/aw/campaigns?ocid='.self::OCID.'&tableState='.self::forRange($start, $end);
    }

    private static function date(CarbonInterface $date): string
    {
        return "\x08".self::varint($date->year)."\x10".self::varint($date->month)."\x18".self::varint($date->day);
    }

    private static function lengthDelimited(int $tag, string $payload): string
    {
        return chr($tag).self::varint(strlen($payload)).$payload;
    }

    private static function varint(int $value): string
    {
        $out = '';

        while (true) {
            $byte = $value & 0x7F;
            $value >>= 7;

            if ($value === 0) {
                return $out.chr($byte);
            }

            $out .= chr($byte | 0x80);
        }
    }
}

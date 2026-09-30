<?php

namespace App\Domains\Lien\Documents;

/**
 * Dollar amounts in words for the instruments that state the amount both
 * ways ("Four Thousand Two Hundred Thirteen Dollars and Seventy-Five Cents").
 * Dependency-free on purpose: the words are recorded, so they must not change
 * with an intl build.
 */
final class MoneyWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
        'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

    private const SCALES = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];

    public static function dollars(int $cents): string
    {
        $sign = $cents < 0 ? 'Minus ' : '';
        $cents = abs($cents);
        $dollars = intdiv($cents, 100);
        $rest = $cents % 100;

        $dollarWords = $dollars === 0 ? 'Zero' : self::integer($dollars);
        $centWords = $rest === 0 ? 'No' : self::integer($rest);

        return "{$sign}{$dollarWords} Dollars and {$centWords} Cents";
    }

    public static function integer(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $number = abs($number);
        $parts = [];
        $scale = 0;

        while ($number > 0) {
            $chunk = $number % 1000;

            if ($chunk > 0) {
                $words = self::chunk($chunk);
                $parts[] = $scale > 0 ? $words.' '.self::SCALES[$scale] : $words;
            }

            $number = intdiv($number, 1000);
            $scale++;
        }

        return implode(' ', array_reverse($parts));
    }

    private static function chunk(int $number): string
    {
        $out = [];

        if ($number >= 100) {
            $out[] = self::ONES[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $ones = $number % 10;
            $out[] = self::TENS[intdiv($number, 10)].($ones > 0 ? '-'.self::ONES[$ones] : '');
        } elseif ($number > 0) {
            $out[] = self::ONES[$number];
        }

        return implode(' ', $out);
    }
}

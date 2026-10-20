<?php

namespace App\Domains\Lien\Engine;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Business-day arithmetic for lien deadlines: Monday to Friday, not a US
 * federal holiday (UsFederalHolidays). Every argument is read as the
 * calendar date it shows (a `date` cast is midnight UTC on that date) and
 * placed in America/New_York, so pass today as now()->eastern(). Results are
 * midnight in America/New_York.
 */
final class BusinessDays
{
    public const TIMEZONE = UsFederalHolidays::TIMEZONE;

    public static function isBusinessDay(CarbonInterface $date): bool
    {
        $day = self::calendarDate($date);

        return ! $day->isWeekend()
            && ! array_key_exists($day->toDateString(), UsFederalHolidays::forYear($day->year));
    }

    /**
     * Whole business days from the day after $from through $to: Friday to
     * the next Monday is 1. Negative when $to is earlier, zero on the same day.
     */
    public static function between(CarbonInterface $from, CarbonInterface $to): int
    {
        $start = self::calendarDate($from);
        $end = self::calendarDate($to);

        if ($end->lessThan($start)) {
            return -self::between($end, $start);
        }

        $count = 0;

        for ($day = $start->addDay(); $day->lessThanOrEqualTo($end); $day = $day->addDay()) {
            if (self::isBusinessDay($day)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * The date $days business days after $date (before it when negative).
     * Zero returns the date itself.
     */
    public static function add(CarbonInterface $date, int $days): CarbonImmutable
    {
        if ($days < 0) {
            return self::subtract($date, -$days);
        }

        $day = self::calendarDate($date);

        while ($days > 0) {
            $day = $day->addDay();

            if (self::isBusinessDay($day)) {
                $days--;
            }
        }

        return $day;
    }

    /**
     * The date $days business days before $date (after it when negative).
     * Zero returns the date itself.
     */
    public static function subtract(CarbonInterface $date, int $days): CarbonImmutable
    {
        if ($days < 0) {
            return self::add($date, -$days);
        }

        $day = self::calendarDate($date);

        while ($days > 0) {
            $day = $day->subDay();

            if (self::isBusinessDay($day)) {
                $days--;
            }
        }

        return $day;
    }

    private static function calendarDate(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $date->format('Y-m-d'), self::TIMEZONE);
    }
}

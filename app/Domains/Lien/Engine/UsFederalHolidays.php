<?php

namespace App\Domains\Lien\Engine;

use Carbon\CarbonImmutable;

/**
 * The US federal holidays (5 U.S.C. § 6103) as the days offices close: a
 * holiday on a Saturday is observed the Friday before, one on a Sunday the
 * Monday after. Computed, not stored, so no year ever runs out.
 */
final class UsFederalHolidays
{
    public const TIMEZONE = 'America/New_York';

    /** @var array<int, array<string, string>> */
    private static array $cache = [];

    /**
     * The observed holidays that fall in the calendar year, 'Y-m-d' => name,
     * in date order. New Year's Day on a Saturday is observed on December 31
     * of the year before, so it belongs to that year's list.
     *
     * @return array<string, string>
     */
    public static function forYear(int $year): array
    {
        if (isset(self::$cache[$year])) {
            return self::$cache[$year];
        }

        $observed = [];

        foreach ([...self::actualDates($year), ...self::actualDates($year + 1)] as [$date, $name]) {
            $day = self::observed($date);

            if ($day->year === $year) {
                $observed[$day->toDateString()] = $name;
            }
        }

        ksort($observed);

        return self::$cache[$year] = $observed;
    }

    /**
     * Each holiday's actual date in the year.
     *
     * @return list<array{0: CarbonImmutable, 1: string}>
     */
    private static function actualDates(int $year): array
    {
        $fixed = fn (int $month, int $day) => CarbonImmutable::create($year, $month, $day, 0, 0, 0, self::TIMEZONE);

        $holidays = [
            [$fixed(1, 1), "New Year's Day"],
            [self::nthWeekday($year, 1, CarbonImmutable::MONDAY, 3), 'Martin Luther King Jr. Day'],
            [self::nthWeekday($year, 2, CarbonImmutable::MONDAY, 3), "Presidents' Day"],
            [self::lastWeekday($year, 5, CarbonImmutable::MONDAY), 'Memorial Day'],
            [$fixed(7, 4), 'Independence Day'],
            [self::nthWeekday($year, 9, CarbonImmutable::MONDAY, 1), 'Labor Day'],
            [self::nthWeekday($year, 10, CarbonImmutable::MONDAY, 2), 'Columbus Day'],
            [$fixed(11, 11), 'Veterans Day'],
            [self::nthWeekday($year, 11, CarbonImmutable::THURSDAY, 4), 'Thanksgiving Day'],
            [$fixed(12, 25), 'Christmas Day'],
        ];

        // Juneteenth became a federal holiday in 2021.
        if ($year >= 2021) {
            $holidays[] = [$fixed(6, 19), 'Juneteenth'];
        }

        return $holidays;
    }

    private static function observed(CarbonImmutable $date): CarbonImmutable
    {
        return match ($date->dayOfWeek) {
            CarbonImmutable::SATURDAY => $date->subDay(),
            CarbonImmutable::SUNDAY => $date->addDay(),
            default => $date,
        };
    }

    private static function nthWeekday(int $year, int $month, int $weekday, int $nth): CarbonImmutable
    {
        $first = CarbonImmutable::create($year, $month, 1, 0, 0, 0, self::TIMEZONE);
        $offset = ($weekday - $first->dayOfWeek + 7) % 7;

        return $first->addDays($offset + 7 * ($nth - 1));
    }

    private static function lastWeekday(int $year, int $month, int $weekday): CarbonImmutable
    {
        $last = CarbonImmutable::create($year, $month, 1, 0, 0, 0, self::TIMEZONE)->endOfMonth()->startOfDay();
        $offset = ($last->dayOfWeek - $weekday + 7) % 7;

        return $last->subDays($offset);
    }
}

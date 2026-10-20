<?php

use App\Domains\Lien\Engine\BusinessDays;
use Carbon\CarbonImmutable;

function bdNy(string $date): CarbonImmutable
{
    return CarbonImmutable::parse($date, 'America/New_York');
}

describe('isBusinessDay', function () {
    it('is a weekday that is not a federal holiday', function () {
        expect(BusinessDays::isBusinessDay(bdNy('2026-10-20')))->toBeTrue()   // Tuesday
            ->and(BusinessDays::isBusinessDay(bdNy('2026-10-24')))->toBeFalse() // Saturday
            ->and(BusinessDays::isBusinessDay(bdNy('2026-10-25')))->toBeFalse() // Sunday
            ->and(BusinessDays::isBusinessDay(bdNy('2026-11-26')))->toBeFalse() // Thanksgiving
            ->and(BusinessDays::isBusinessDay(bdNy('2026-07-03')))->toBeFalse() // Independence Day, observed
            ->and(BusinessDays::isBusinessDay(bdNy('2026-11-27')))->toBeTrue(); // the Friday after Thanksgiving
    });

    it('reads a date cast (midnight UTC) as its calendar date, not the evening before in New York', function () {
        // 2026-11-27 00:00 UTC is 7pm on Thanksgiving in New York.
        expect(BusinessDays::isBusinessDay(CarbonImmutable::parse('2026-11-27 00:00:00', 'UTC')))->toBeTrue();
    });
});

describe('between', function () {
    it('counts from the day after the start through the end', function () {
        expect(BusinessDays::between(bdNy('2026-10-20'), bdNy('2026-10-20')))->toBe(0)
            ->and(BusinessDays::between(bdNy('2026-10-20'), bdNy('2026-10-21')))->toBe(1)
            ->and(BusinessDays::between(bdNy('2026-10-23'), bdNy('2026-10-26')))->toBe(1)  // Friday to Monday
            ->and(BusinessDays::between(bdNy('2026-10-20'), bdNy('2026-10-27')))->toBe(5);
    });

    it('skips a holiday weekend', function () {
        // Friday July 3, 2026 is the observed Independence Day.
        expect(BusinessDays::between(bdNy('2026-07-02'), bdNy('2026-07-06')))->toBe(1)
            // Thanksgiving week: Wednesday to the next Monday is Friday and Monday.
            ->and(BusinessDays::between(bdNy('2026-11-25'), bdNy('2026-11-30')))->toBe(2);
    });

    it('is negative when the end is earlier', function () {
        expect(BusinessDays::between(bdNy('2026-10-27'), bdNy('2026-10-20')))->toBe(-5)
            ->and(BusinessDays::between(bdNy('2026-07-06'), bdNy('2026-07-02')))->toBe(-1);
    });

    it('counts a long span across Veterans Day and Thanksgiving', function () {
        expect(BusinessDays::between(bdNy('2026-10-20'), bdNy('2026-12-11')))->toBe(36);
    });
});

describe('add and subtract', function () {
    it('steps over weekends and holidays', function () {
        expect(BusinessDays::add(bdNy('2026-10-23'), 1)->toDateString())->toBe('2026-10-26')
            ->and(BusinessDays::add(bdNy('2026-11-25'), 1)->toDateString())->toBe('2026-11-27')
            ->and(BusinessDays::add(bdNy('2026-07-02'), 1)->toDateString())->toBe('2026-07-06')
            ->and(BusinessDays::subtract(bdNy('2026-07-06'), 1)->toDateString())->toBe('2026-07-02')
            ->and(BusinessDays::subtract(bdNy('2026-12-14'), 5)->toDateString())->toBe('2026-12-07');
    });

    it('returns the date itself for zero, and flips direction for negatives', function () {
        expect(BusinessDays::add(bdNy('2026-10-24'), 0)->toDateString())->toBe('2026-10-24')
            ->and(BusinessDays::add(bdNy('2026-10-26'), -1)->toDateString())->toBe('2026-10-23')
            ->and(BusinessDays::subtract(bdNy('2026-10-23'), -1)->toDateString())->toBe('2026-10-26');
    });

    it('agrees with between', function () {
        $start = bdNy('2026-12-18');
        $end = BusinessDays::add($start, 10);

        expect(BusinessDays::between($start, $end))->toBe(10)
            ->and(BusinessDays::subtract($end, 10)->toDateString())->toBe('2026-12-18');
    });

    it('returns midnight in New York', function () {
        $date = BusinessDays::add(CarbonImmutable::parse('2026-10-20 23:30:00', 'UTC'), 1);

        expect($date->toDateString())->toBe('2026-10-21')
            ->and($date->timezoneName)->toBe('America/New_York')
            ->and($date->format('H:i'))->toBe('00:00');
    });
});

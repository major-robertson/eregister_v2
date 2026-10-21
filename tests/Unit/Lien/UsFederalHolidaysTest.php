<?php

use App\Domains\Lien\Engine\UsFederalHolidays;

it('lists the eleven observed federal holidays for 2026', function () {
    expect(UsFederalHolidays::forYear(2026))->toBe([
        '2026-01-01' => "New Year's Day",
        '2026-01-19' => 'Martin Luther King Jr. Day',
        '2026-02-16' => "Presidents' Day",
        '2026-05-25' => 'Memorial Day',
        '2026-06-19' => 'Juneteenth',
        '2026-07-03' => 'Independence Day', // July 4 is a Saturday
        '2026-09-07' => 'Labor Day',
        '2026-10-12' => 'Columbus Day',
        '2026-11-11' => 'Veterans Day',
        '2026-11-26' => 'Thanksgiving Day',
        '2026-12-25' => 'Christmas Day',
    ]);
});

it('moves a Saturday holiday to Friday and a Sunday holiday to Monday', function () {
    $holidays = UsFederalHolidays::forYear(2027);

    expect($holidays)
        ->toHaveKey('2027-06-18')      // Juneteenth, Saturday June 19
        ->toHaveKey('2027-07-05')      // Independence Day, Sunday July 4
        ->toHaveKey('2027-12-24')      // Christmas, Saturday December 25
        ->not->toHaveKey('2027-06-19')
        ->not->toHaveKey('2027-07-04')
        ->not->toHaveKey('2027-12-25');
});

it('puts a Saturday New Year\'s Day on December 31 of the year before', function () {
    // January 1, 2028 is a Saturday.
    expect(UsFederalHolidays::forYear(2027))->toHaveKey('2027-12-31', "New Year's Day")
        ->and(UsFederalHolidays::forYear(2028))->not->toHaveKey('2028-01-01')
        ->and(UsFederalHolidays::forYear(2028))->not->toHaveKey('2027-12-31')
        ->and(UsFederalHolidays::forYear(2028))->toHaveCount(10);
});

it('has no Juneteenth before 2021', function () {
    expect(UsFederalHolidays::forYear(2020))->not->toContain('Juneteenth')
        ->toHaveCount(10)
        ->and(UsFederalHolidays::forYear(2021))->toHaveKey('2021-06-18', 'Juneteenth');
});

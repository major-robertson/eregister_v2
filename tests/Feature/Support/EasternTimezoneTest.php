<?php

use Carbon\CarbonImmutable;

// The eastern() macro is registered in AppServiceProvider::configureDefaults()
// and is the single place the product converts stored UTC timestamps for display.

it('converts a UTC timestamp to Eastern Standard Time in winter (UTC-5)', function () {
    $utc = CarbonImmutable::parse('2026-01-15 18:00:00', 'UTC');

    expect($utc->eastern()->format('H:i'))->toBe('13:00');
});

it('converts a UTC timestamp to Eastern Daylight Time in summer (UTC-4)', function () {
    $utc = CarbonImmutable::parse('2026-07-15 18:00:00', 'UTC');

    expect($utc->eastern()->format('H:i'))->toBe('14:00');
});

it('does not mutate the original immutable instance', function () {
    $utc = CarbonImmutable::parse('2026-01-15 18:00:00', 'UTC');
    $utc->eastern();

    expect($utc->format('H:i'))->toBe('18:00');
});

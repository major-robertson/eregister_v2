<?php

use App\Domains\Lien\Support\ZipStateRanges;

uses(Tests\TestCase::class);

it('covers every state we list, and DC', function () {
    foreach ([...array_keys(config('states')), 'DC'] as $state) {
        expect(ZipStateRanges::RANGES)->toHaveKey($state);
    }
});

it('gives no ZIP prefix to two states', function () {
    $states = [...array_keys(config('states')), 'DC'];

    foreach (range(0, 999) as $prefix) {
        $owners = array_filter($states, function (string $state) use ($prefix): bool {
            foreach (ZipStateRanges::RANGES[$state] as [$low, $high]) {
                if ($prefix >= $low && $prefix <= $high) {
                    return true;
                }
            }

            return false;
        });

        expect(count($owners))->toBeLessThanOrEqual(1, "Prefix {$prefix} belongs to ".implode(', ', $owners));
    }
});

it('places a real ZIP code in its state', function (string $zip, string $state) {
    expect(ZipStateRanges::matches($zip, $state))->toBeTrue()
        ->and(ZipStateRanges::statesFor($zip)[0])->toBe($state);
})->with([
    ['36104', 'AL'], ['99501', 'AK'], ['85001', 'AZ'], ['72201', 'AR'], ['94105', 'CA'],
    ['80202', 'CO'], ['06103', 'CT'], ['19901', 'DE'], ['32301', 'FL'], ['30303', 'GA'],
    ['96813', 'HI'], ['83702', 'ID'], ['60601', 'IL'], ['46204', 'IN'], ['50309', 'IA'],
    ['66603', 'KS'], ['40601', 'KY'], ['70112', 'LA'], ['04101', 'ME'], ['21201', 'MD'],
    ['02108', 'MA'], ['48226', 'MI'], ['55102', 'MN'], ['39201', 'MS'], ['63101', 'MO'],
    ['59601', 'MT'], ['68102', 'NE'], ['89101', 'NV'], ['03301', 'NH'], ['07102', 'NJ'],
    ['87501', 'NM'], ['10001', 'NY'], ['27601', 'NC'], ['58501', 'ND'], ['43215', 'OH'],
    ['73102', 'OK'], ['97201', 'OR'], ['19103', 'PA'], ['02903', 'RI'], ['29201', 'SC'],
    ['57501', 'SD'], ['37219', 'TN'], ['78701', 'TX'], ['84111', 'UT'], ['05602', 'VT'],
    ['23219', 'VA'], ['98101', 'WA'], ['25301', 'WV'], ['53703', 'WI'], ['82001', 'WY'],
    ['20001', 'DC'],
    // Prefixes outside a state's main block.
    'Austin IRS' => ['73301', 'TX'],
    'El Paso' => ['88510', 'TX'],
    'Northern Virginia' => ['20147', 'VA'],
    'Andover, MA' => ['05501', 'MA'],
    'Holtsville, NY' => ['00501', 'NY'],
    'Puerto Rico' => ['00901', 'PR'],
]);

it('flags a ZIP code from another state with a plain message', function () {
    expect(ZipStateRanges::matches('85210', 'CA'))->toBeFalse()
        ->and(ZipStateRanges::mismatchMessage('85210', 'CA'))
        ->toBe('ZIP code 85210 is in Arizona, not California. Check the ZIP code and the state.');
});

it('passes a ZIP code whose prefix is not in the table', function () {
    expect(ZipStateRanges::statesFor('26900'))->toBe([])
        ->and(ZipStateRanges::matches('26900', 'WV'))->toBeTrue()
        ->and(ZipStateRanges::mismatchMessage('26900', 'WV'))->toBeNull();
});

it('accepts ZIP+4 and a lowercase state', function () {
    expect(ZipStateRanges::matches('85210-1234', 'AZ'))->toBeTrue()
        ->and(ZipStateRanges::matches('852101234', 'az'))->toBeTrue()
        ->and(ZipStateRanges::mismatchMessage('85210-1234', 'AZ'))->toBeNull();
});

it('leaves blank or unreadable values to the other rules', function (?string $zip, ?string $state) {
    expect(ZipStateRanges::matches($zip, $state))->toBeTrue()
        ->and(ZipStateRanges::mismatchMessage($zip, $state))->toBeNull();
})->with([
    'no ZIP' => [null, 'CA'],
    'blank ZIP' => ['', 'CA'],
    'short ZIP' => ['852', 'CA'],
    'letters' => ['ABCDE', 'CA'],
    'no state' => ['85210', null],
    'unknown state' => ['85210', 'ZZ'],
]);

it('allows a ZIP code that crosses a state line', function () {
    expect(ZipStateRanges::matches('06390', 'NY'))->toBeTrue()
        ->and(ZipStateRanges::matches('06390', 'CT'))->toBeTrue()
        ->and(ZipStateRanges::matches('73949', 'TX'))->toBeTrue()
        ->and(ZipStateRanges::matches('73949', 'OK'))->toBeTrue();
});

<?php

use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Support\PartyAddress;

uses(Tests\TestCase::class);

function partyAddressJobsite(): LienProject
{
    return new LienProject([
        'jobsite_address1' => '100 North Jobsite Road',
        'jobsite_city' => 'Mesa',
        'jobsite_state' => 'AZ',
        'jobsite_zip' => '85201',
    ]);
}

it('spots the jobsite address however it is written', function (string $line1, ?string $city, ?string $zip) {
    expect(PartyAddress::sameAsJobsite(partyAddressJobsite(), $line1, $city, $zip))->toBeTrue();
})->with([
    'as entered' => ['100 North Jobsite Road', 'Mesa', '85201'],
    'short forms and case' => ['100 n. jobsite rd', 'MESA', '85201-1234'],
    'no ZIP, same city' => ['100 N Jobsite Rd', 'Mesa', null],
]);

it('tells a different address from the jobsite', function (string $line1, ?string $city, ?string $zip) {
    expect(PartyAddress::sameAsJobsite(partyAddressJobsite(), $line1, $city, $zip))->toBeFalse();
})->with([
    'other street number' => ['102 North Jobsite Road', 'Mesa', '85201'],
    'same street, other ZIP' => ['100 North Jobsite Road', 'Phoenix', '85001'],
    'no ZIP, other city' => ['100 North Jobsite Road', 'Phoenix', null],
    'blank' => ['', 'Mesa', '85201'],
]);

it('flags a street line with no street number', function () {
    expect(PartyAddress::lacksStreetNumber('Main Street'))->toBeTrue()
        ->and(PartyAddress::lacksStreetNumber('123 Main Street'))->toBeFalse()
        ->and(PartyAddress::lacksStreetNumber('PO Box 45'))->toBeFalse()
        ->and(PartyAddress::lacksStreetNumber(''))->toBeFalse()
        ->and(PartyAddress::lacksStreetNumber(null))->toBeFalse();
});

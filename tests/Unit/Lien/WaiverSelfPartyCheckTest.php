<?php

use App\Domains\Lien\Waivers\WaiverSelfPartyCheck;

it('treats company names that differ only in case, punctuation or entity words as the same', function (string $a, string $b) {
    expect(WaiverSelfPartyCheck::normalizeName($a))->toBe(WaiverSelfPartyCheck::normalizeName($b))
        ->not->toBe('');
})->with([
    'case' => ['Rural Concrete Creations', 'rural concrete creations'],
    'suffix and comma' => ['Rural Concrete Creations, LLC', 'Rural concrete creations'],
    'leading "the" and Co.' => ['The Acme Framing Co.', 'Acme Framing'],
    'ampersand' => ['A & B Builders, Inc.', 'A and B Builders'],
]);

it('keeps names that really differ apart', function (string $a, string $b) {
    expect(WaiverSelfPartyCheck::normalizeName($a))->not->toBe(WaiverSelfPartyCheck::normalizeName($b));
})->with([
    'a longer name that contains it' => ['ABC Supply', 'ABC'],
    'different words' => ['Rural Concrete Creations', 'Urban Concrete Creations'],
]);

it('normalizes a blank name, or one made only of entity words, to nothing', function (?string $name) {
    expect(WaiverSelfPartyCheck::normalizeName($name))->toBe('');
})->with([null, '', '  ', 'LLC', 'The Company, Inc.']);

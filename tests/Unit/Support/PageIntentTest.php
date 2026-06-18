<?php

use App\Support\PageIntent;
use Illuminate\Http\Request;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->map = [
        'sales-tax-registration' => 'Registration',
        'sales-tax-permit' => 'Permit',
        'sales-tax-id' => 'ID',
    ];
});

function setIntentQuery(?string $intent): void
{
    $query = $intent === null ? [] : ['intent' => $intent];

    app()->instance('request', Request::create('/', 'GET', $query));
}

describe('PageIntent', function () {
    it('maps a whitelisted intent to its keyword', function (string $intent, string $expected) {
        setIntentQuery($intent);

        expect(PageIntent::keyword($this->map, 'sales-tax-registration'))->toBe($expected);
    })->with([
        'registration' => ['sales-tax-registration', 'Registration'],
        'permit' => ['sales-tax-permit', 'Permit'],
        'id' => ['sales-tax-id', 'ID'],
    ]);

    it('falls back to the default when no intent is provided', function () {
        setIntentQuery(null);

        expect(PageIntent::keyword($this->map, 'sales-tax-registration'))->toBe('Registration');
    });

    it('falls back to the default for an unknown intent', function () {
        setIntentQuery('not-a-real-intent');

        expect(PageIntent::keyword($this->map, 'sales-tax-registration'))->toBe('Registration');
    });
});

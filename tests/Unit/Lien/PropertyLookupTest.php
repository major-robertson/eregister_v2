<?php

use App\Domains\Lien\Support\PropertyLookup;

/*
| EREG-136: where a customer finds the legal description or parcel number.
| The link only ever comes from the data files.
*/

it('uses the state word for the parcel number, and the county word over it', function () {
    $state = PropertyLookup::fromRecording(['parcel_label' => 'Tax Parcel ID'], [], 'Georgia');
    $county = PropertyLookup::fromRecording(['parcel_label' => 'Parcel Number'], ['parcel_label' => 'Serial Number'], 'Utah');

    expect($state->parcelLabel)->toBe('Tax Parcel ID')
        ->and($state->parcelNumberPhrase())->toBe('parcel number (Tax Parcel ID)')
        ->and($state->localParcelLabel())->toBe('Tax Parcel ID')
        ->and($county->parcelLabel)->toBe('Serial Number');
});

it('keeps the generic words generic', function (string $label) {
    $lookup = PropertyLookup::fromRecording(['parcel_label' => $label], [], 'Kansas');

    expect($lookup->localParcelLabel())->toBeNull();
})->with(['Parcel ID', 'Parcel Number']);

it('says just "parcel number" when that is the state word', function () {
    expect(PropertyLookup::fromRecording(['parcel_label' => 'Parcel Number'], [], 'Oregon')->parcelNumberPhrase())->toBe('parcel number');
});

it('falls back to Parcel ID with no label at all', function () {
    expect(PropertyLookup::fromRecording([], [], 'Alaska')->parcelLabel)->toBe('Parcel ID');
});

it('links the county assessor search, else the state directory, else nothing', function () {
    $county = PropertyLookup::fromRecording(
        ['assessor_url' => 'https://state.example.gov/assessors'],
        ['assessor_url' => 'https://county.example.gov/parcel-search'],
        'Kansas',
    );
    $state = PropertyLookup::fromRecording(['assessor_url' => 'https://state.example.gov/assessors'], [], 'Kansas');
    $none = PropertyLookup::fromRecording(['parcel_label' => 'Parcel ID'], [], 'Kansas');

    expect($county->assessorUrl)->toBe('https://county.example.gov/parcel-search')
        ->and($state->assessorUrl)->toBe('https://state.example.gov/assessors')
        ->and($none->assessorUrl)->toBeNull();
});

it('ignores a link that is not https', function (mixed $url) {
    expect(PropertyLookup::fromRecording([], ['assessor_url' => $url], 'Kansas')->assessorUrl)->toBeNull();
})->with([
    'plain http' => ['http://county.example.gov/parcel-search'],
    'a script' => ['javascript:alert(1)'],
    'not a string' => [['https://county.example.gov']],
    'blank' => [''],
]);

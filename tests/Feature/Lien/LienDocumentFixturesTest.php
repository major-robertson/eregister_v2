<?php

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

it('builds a renderable project and filing from the shared fixtures', function () {
    $filing = lienFixtureFiling(lienFixtureProject('OH', 'Franklin'), 'mechanics_lien');

    $text = lienFixtureText(app(LienDocumentGenerator::class)->render($filing));

    expect($text)
        ->toContain('CLAIM OF LIEN')
        ->toContain('STATE OF OHIO')
        ->toContain('COUNTY OF FRANKLIN')
        ->toContain('S G Roser Construction LLC')
        ->toContain('Mike Stuntz')
        ->toContain('Ken Walker Builders')
        ->toContain('$4,213.75')
        ->toContain('BAYWOOD PARK LOT 25')
        ->toContain('July 10, 2026');
});

it('prints the recording legend under the recorder rule only when the state or county sets one', function () {
    $doc = [
        'form' => ['recording' => LienDocumentRegistry::for('OH')['recording'], 'recorder_space_in' => 2.0],
        'preparer' => ['name' => 'eRegister', 'attention' => null, 'address_lines' => ['1 Main St'], 'phone' => null],
    ];

    expect(view('documents.lien._parts.recorder-block', ['doc' => $doc])->render())
        ->not->toContain('recorder-legend');

    $doc['form']['recording']['legend'] = 'Submitted electronically by eRegister under the county MOU.';

    $html = view('documents.lien._parts.recorder-block', ['doc' => $doc])->render();

    expect($html)->toContain('recorder-legend')
        ->and($html)->toContain('Submitted electronically by eRegister under the county MOU.')
        ->and(strpos($html, 'Space above this line'))->toBeLessThan(strpos($html, 'recorder-legend'));
});

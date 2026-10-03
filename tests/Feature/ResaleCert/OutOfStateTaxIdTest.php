<?php

use App\Domains\ResaleCert\Pdf\SampleCertificate;
use App\Domains\ResaleCert\Services\CertificatePdfService;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Forms that take another state's number but have no field for the state
 * that issued it: an out-of-state number is printed with its state
 * ("CT 11122233344"), an in-state one bare.
 */

/** The page content streams of a rendered certificate, inflated, so the stamped text can be searched. */
function stampedText(string $stateCode, string $sourceState): string
{
    Storage::fake(config('resale_cert.disk'));

    $certificate = SampleCertificate::make($stateCode, $sourceState);
    $user = new User(['first_name' => 'Jane', 'last_name' => 'Buyer']);
    $user->setRelation('currentSignature', null);
    $certificate->setRelation('createdBy', $user);

    $bytes = app(CertificatePdfService::class)->renderCertificate($certificate, false);

    preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $bytes, $streams);

    return collect($streams[1])
        ->map(fn (string $stream) => @gzuncompress($stream) ?: $stream)
        ->implode("\n");
}

it('labels an out-of-state number with its state', function (string $stateCode) {
    expect(stampedText($stateCode, 'CT'))->toContain('(CT 11122233344) Tj');
})->with(['AZ', 'KS', 'KY', 'NV', 'PA', 'RI', 'VA', 'VT']);

it('prints an in-state number bare', function (string $stateCode) {
    $text = stampedText($stateCode, $stateCode);

    expect($text)->toContain('(11122233344) Tj')
        ->and($text)->not->toContain("({$stateCode} 11122233344)");
})->with(['AZ', 'KS', 'KY', 'NV', 'PA', 'RI', 'VA', 'VT']);

it('explains an out-of-state Pennsylvania license under Number 8', function () {
    expect(stampedText('PA', 'CT'))
        ->toContain('(Out-of-state purchaser, not registered in PA. License ID above was issued by CT.) Tj');

    expect(stampedText('PA', 'PA'))->not->toContain('Out-of-state purchaser');
});

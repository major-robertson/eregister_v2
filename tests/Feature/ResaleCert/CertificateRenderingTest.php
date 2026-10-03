<?php

use App\Domains\ResaleCert\Pdf\SampleCertificate;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate;
use App\Domains\ResaleCert\Pdf\States\SstUniformCertificate;
use App\Domains\ResaleCert\Services\CertificatePdfService;
use App\Models\User;
use App\Models\UserSignature;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Every state form renders through the real FPDI pipeline: the template
 * imports, the class stamps its fields and signature, and no field write
 * spills onto an extra page.
 */

// Read straight from the config file: datasets are built before the app boots.
$states = (require __DIR__.'/../../../config/resale_cert.php')['states'];

$withClass = array_keys(array_filter($states, fn (array $state) => ! empty($state['class'])));

$outOfState = array_keys(array_filter($states, fn (array $state) => ! empty($state['class'])
    && ! in_array($state['class'], [MtcUniformCertificate::class, SstUniformCertificate::class], true)));

function renderSampleCertificate(string $stateCode, string $sourceState): string
{
    $disk = Storage::fake(config('resale_cert.disk'));
    $disk->put('signatures/sample.png', UploadedFile::fake()->image('sample.png', 500, 100)->getContent());

    $user = new User(['first_name' => 'Jane', 'last_name' => 'Buyer']);
    $user->setRelation('currentSignature', new UserSignature(['image_path' => 'signatures/sample.png']));

    $certificate = SampleCertificate::make($stateCode, $sourceState);
    $certificate->setRelation('createdBy', $user);

    return app(CertificatePdfService::class)->renderCertificate($certificate, false);
}

function renderedPageCount(string $bytes): int
{
    return preg_match_all('#/Type\s*/Page\b(?!s)#', $bytes);
}

function expectedPageCount(string $stateCode, string $sourceState): ?int
{
    $relativePath = app(StateCertificateFactory::class)
        ->getTemplatePathForCertificate($stateCode, SampleCertificate::make($stateCode, $sourceState));

    if (! $relativePath) {
        return null; // drawn from scratch (AL, OK)
    }

    return (new Fpdi)->setSourceFile(resource_path($relativePath));
}

it('renders the in-state certificate with one page per template page', function (string $stateCode) {
    $bytes = renderSampleCertificate($stateCode, $stateCode);

    expect(substr($bytes, 0, 4))->toBe('%PDF');

    $expected = expectedPageCount($stateCode, $stateCode);
    $expected === null
        ? expect(renderedPageCount($bytes))->toBeGreaterThanOrEqual(1)
        : expect(renderedPageCount($bytes))->toBe($expected);
})->with($withClass);

it('renders the out-of-state certificate with one page per template page', function (string $stateCode) {
    $bytes = renderSampleCertificate($stateCode, 'CT');

    expect(substr($bytes, 0, 4))->toBe('%PDF');

    $expected = expectedPageCount($stateCode, 'CT');
    $expected === null
        ? expect(renderedPageCount($bytes))->toBeGreaterThanOrEqual(1)
        : expect(renderedPageCount($bytes))->toBe($expected);
})->with($outOfState);

it('has a template file for every configured form', function () {
    $directory = resource_path(config('resale_cert.templates_path'));

    foreach (config('resale_cert.states') as $stateCode => $state) {
        foreach (array_filter([$state['template'] ?? '', $state['template_out_of_state'] ?? '']) as $template) {
            expect(file_exists($directory.'/'.$template))->toBeTrue("{$stateCode}: {$template} is missing");
        }
    }
});

it('references every template in the directory from the config', function () {
    $referenced = collect(config('resale_cert.states'))
        ->flatMap(fn (array $state) => [$state['template'] ?? '', $state['template_out_of_state'] ?? ''])
        ->filter()
        ->unique();

    $files = collect(glob(resource_path(config('resale_cert.templates_path')).'/*.pdf'))->map(fn (string $path) => basename($path));

    expect($files->diff($referenced)->values()->all())->toBe([]);
});

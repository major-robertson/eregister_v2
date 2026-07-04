<?php

/**
 * FPDI smoke test: render every registered state certificate against a
 * fabricated (unsaved) certificate and report failures. Run with:
 *   php scripts/resale-cert-pdf-smoke.php
 */

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use setasign\Fpdi\Fpdi;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$factory = new StateCertificateFactory;

$certificate = new ResaleCertificate([
    'state_code' => 'TX',
    'is_blanket' => true,
    'item_description' => 'All tangible personal property for resale',
    'business_snapshot' => [
        'legal_name' => 'Acme Trading LLC',
        'dba' => 'Acme',
        'ein' => '12-3456789',
        'products_description' => 'General merchandise',
        'email' => 'billing@acme.test',
        'phone' => '(512) 555-1234',
        'signer_title' => 'Owner',
        'address' => [
            'line1' => '100 Congress Ave',
            'line2' => null,
            'city' => 'Austin',
            'state' => 'TX',
            'postal_code' => '78701',
            'country' => 'US',
        ],
        'tax_id' => '11122233344',
        'tax_id_source_state' => 'TX',
        'selected_states_tax_ids' => [
            'TX' => ['tax_id' => '11122233344', 'source_state' => 'TX'],
            'FL' => ['tax_id' => '99887766', 'source_state' => 'TX'],
        ],
    ],
    'vendor_snapshot' => [
        'legal_name' => 'Supplier Co',
        'address' => [
            'line1' => '200 Main St',
            'line2' => null,
            'city' => 'Dallas',
            'state' => 'TX',
            'postal_code' => '75201',
            'country' => 'US',
        ],
        'contact' => ['name' => 'Pat Vendor', 'email' => 'pat@supplier.test', 'phone' => '(214) 555-9876'],
    ],
    'issue_date' => now(),
]);
$certificate->id = 999999;
$certificate->setRelation('createdBy', null);

$failures = [];
$passed = 0;

foreach ($factory->getRegisteredStates() as $stateCode) {
    $certificate->state_code = $stateCode;

    try {
        $pdf = new Fpdi;
        $handler = $factory->make($stateCode);

        if (method_exists($handler, 'usesCustomGeneration') && $handler->usesCustomGeneration()) {
            $handler->generateCustomPdf($pdf, $certificate);
        } else {
            $relativePath = $factory->getTemplatePathForCertificate($stateCode, $certificate);
            $templatePath = resource_path($relativePath);

            if (! file_exists($templatePath)) {
                throw new RuntimeException("Template missing: {$relativePath}");
            }

            $pageCount = $pdf->setSourceFile($templatePath);

            for ($page = 1; $page <= $pageCount; $page++) {
                $tplIdx = $pdf->importPage($page);
                $size = $pdf->getTemplateSize($tplIdx);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplIdx);
                $pdf->SetFont('Helvetica', '', 10);
                $handler->fillFormFields($pdf, $certificate, $page, $pageCount);
            }
        }

        $bytes = $pdf->Output('S');

        if (! str_starts_with($bytes, '%PDF')) {
            throw new RuntimeException('Output is not a PDF');
        }

        $passed++;
        echo "PASS {$stateCode} (".strlen($bytes)." bytes)\n";
    } catch (Throwable $e) {
        $failures[$stateCode] = get_class($e).': '.$e->getMessage();
        echo "FAIL {$stateCode}: {$failures[$stateCode]}\n";
    }
}

echo "\n{$passed} passed, ".count($failures)." failed\n";
exit($failures === [] ? 0 : 1);

<?php

namespace App\Domains\ResaleCert\Http\Controllers;

use App\Domains\ResaleCert\Pdf\SampleCertificate;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use App\Domains\ResaleCert\Services\CertificatePdfService;
use Illuminate\Http\Response;

/**
 * A sample certificate a customer can look at before subscribing: the
 * state's official form filled with fixed sample data (nothing saved, no
 * signature). Same sample shape as the admin coordinate mapper's preview,
 * without the grid overlay.
 */
class SampleCertificateController
{
    public function __invoke(string $stateCode, CertificatePdfService $pdfService, StateCertificateFactory $factory): Response
    {
        $stateCode = strtoupper($stateCode);

        abort_unless($factory->has($stateCode), 404, 'No certificate form for this state.');

        $bytes = $pdfService->renderCertificate(SampleCertificate::make($stateCode, brand: 'Sample'), false);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sample-resale-certificate-'.strtolower($stateCode).'.pdf"',
        ]);
    }
}

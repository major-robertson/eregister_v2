<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Support\SignatureCertificateData;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Renders the SIGNED Payment Demand Letter: the same letter body (from the
 * locked render snapshot, not live data) plus an electronic signature block and
 * an appended Certificate of Completion / audit page. Kept separate from
 * DemandLetterGenerator so that generator's small surface stays untouched.
 * The signature block and certificate facts come from
 * SignatureCertificateData, shared with PdfSignatureStamper.
 *
 * Pinned to the DOMPDF driver so it never depends on Chrome.
 */
class DemandLetterSignedGenerator
{
    /**
     * @param  array<string, mixed>  $letter  The frozen render payload (DemandLetterGenerator::data()).
     */
    public function render(array $letter, SignatureContext $context): PdfBuilder
    {
        return Pdf::view('documents.lien.demand-letter-signed', [
            'letter' => $letter,
            'signature' => SignatureCertificateData::signatureBlock($context),
            'certificate' => SignatureCertificateData::certificate($context),
        ])->driver('dompdf')->format('letter');
    }
}

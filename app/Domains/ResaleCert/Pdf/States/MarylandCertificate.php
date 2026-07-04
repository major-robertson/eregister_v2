<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MarylandCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/maryland.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        $pdf->SetAutoPageBreak(false);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 8.9, 52.4, $data->businessName);
        $this->writeAt($pdf, 59.3, 52.4, $data->businessFullAddress);
        $this->writeAt($pdf, 59.3, 61.9, $data->businessTaxId);

        // Vendor Information
        $this->writeAt($pdf, 9.6, 24.0, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 139.2, 61.9, $data->issueDate);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 9.4, 58.3, 6);

        $pdf->SetAutoPageBreak(true);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class TennesseeCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/tennessee.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        $pdf->SetFont('Helvetica', '', 8);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 23.5, 76.0, $data->businessTaxId);
        $this->writeAt($pdf, 104.7, 67.3, $data->businessName);
        $this->writeAt($pdf, 102.3, 71.5, $data->businessName);
        $this->writeAt($pdf, 93.1, 80.0, $data->businessFullAddress);

        // Vendor Information
        $this->writeAt($pdf, 35.7, 24.5, $data->vendorName);
        $this->writeAt($pdf, 35.7, 29.6, $data->vendorFullAddress);

        // Certificate Details
        $this->writeAt($pdf, 15.1, 80.2, $data->issueDate);

        // Special Elements
        $this->writeAt($pdf, 6.4, 45.6, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 87.0, 73.4, 4);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class NevadaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/nevada.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Set smaller font size for all fields
        $pdf->SetFont('Helvetica', '', 6);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 111.5, 51.0, $data->businessTaxId);
        $this->writeAt($pdf, 32.2, 122.1, $data->businessCity);
        $this->writeAt($pdf, 69.4, 122.1, $data->businessState);
        $this->writeAt($pdf, 135.7, 113.4, $data->businessName);
        $this->writeAt($pdf, 135.7, 122.1, $data->businessFullAddress);

        // Vendor Information
        $this->writeAt($pdf, 83.5, 63.3, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 25.9, 59.3, $data->productDescription);
        $this->writeAt($pdf, 97.6, 87.5, $data->productDescription);
        $this->writeAt($pdf, 38.8, 114.5, $data->issueDate);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 120.4, 126.3, 6);
    }
}

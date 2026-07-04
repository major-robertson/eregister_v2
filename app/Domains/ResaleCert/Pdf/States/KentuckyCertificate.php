<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class KentuckyCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/kentucky.pdf';
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

        // Disable text wrapping and remove margins
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 34.1, 17.1, $data->businessName);
        $this->writeAt($pdf, 74.8, 17.1, $data->businessFullAddress);
        $this->writeAt($pdf, 65.8, 23.2, $data->businessTaxId);

        // Vendor Information
        $this->writeAt($pdf, 8.2, 39.0, $data->vendorName);
        $this->writeAt($pdf, 71.7, 39.0, $data->vendorFullAddress);

        // Certificate Details
        $this->writeAt($pdf, 8.2, 29.4, $data->productDescription);
        $this->writeAt($pdf, 8.2, 59.8, $data->productDescription);
        $this->writeAt($pdf, 85.8, 77.6, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 85.8, 71.0, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 141, 10.5, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 8.2, 67.0, 6);

        // Re-enable auto page break
        $pdf->SetAutoPageBreak(true);
    }
}

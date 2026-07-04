<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class NorthDakotaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/north_dakota.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 130.5, 37.6, $data->businessTaxId);
        $this->writeAt($pdf, 16.9, 73.6, $data->businessName);

        // Set smaller font size for business address
        $pdf->SetFont('Helvetica', '', 8);
        $this->writeAt($pdf, 114.1, 73.6, $data->businessFullAddress);
        $pdf->SetFont('Helvetica', '', 10); // Reset to default size

        // Vendor Information
        $this->writeAt($pdf, 112.9, 51.0, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 60.0, 46.3, $data->productDescription);
        $this->writeAt($pdf, 114.1, 83.0, $data->issueDate);

        // State - use tax ID source state instead of hardcoding "ND"
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
        $this->writeAt($pdf, 55.3, 37.6, $taxIdSourceState);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 16.9, 79.3, 6);
    }
}

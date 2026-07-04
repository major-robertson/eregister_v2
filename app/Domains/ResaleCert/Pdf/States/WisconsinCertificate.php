<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class WisconsinCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/wisconsin.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        $data = $this->extractCertificateData($certificate);

        // Page 1: Fill all form fields
        if ($currentPage === 1) {
            // Coordinates are in millimeters (mm) - FPDI default unit
            // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
            // Business Information
            $this->writeAt($pdf, 15.3, 51.5, $data->businessName);
            $this->writeAt($pdf, 142.3, 51.5, $data->businessType);
            $this->writeAt($pdf, 15.3, 61.9, $data->businessStreetAddress);
            $this->writeAt($pdf, 97.6, 61.9, $data->businessCity);
            $this->writeAt($pdf, 142.3, 61.9, $data->businessState);
            $this->writeAt($pdf, 154.0, 61.9, $data->businessZip);
            $this->writeAt($pdf, 15.3, 72.0, $data->businessTaxId);

            // State for tax ID (same for both in-state and out-of-state)
            $business = $certificate->business_snapshot;
            $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
            $this->writeAt($pdf, 155.2, 72.0, $taxIdSourceState);
            $this->writeAt($pdf, 124.4, 118.8, $data->businessTaxId);

            // Vendor Information
            $this->writeAt($pdf, 15.3, 96.9, $data->vendorName);
            $this->writeAt($pdf, 15.3, 106.8, $data->vendorAddress1);
            $this->writeAt($pdf, 97.6, 106.8, $data->vendorCity);
            $this->writeAt($pdf, 155.2, 106.8, $data->vendorZip);
            $this->writeAt($pdf, 142.3, 106.8, $data->vendorState);

            // Special Elements
            $this->writeAt($pdf, 121.1, 34.6, $data->checkmarkX);
            $this->writeAt($pdf, 12.7, 118.1, $data->checkmarkX);
        }

        // Page 2: Fill form fields
        if ($currentPage === 2) {
            // Coordinates are in millimeters (mm) - FPDI default unit
            // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
            // Certificate Details
            $this->writeAt($pdf, 179.9, 263.9, $data->issueDate);

            // Contact Information
            $this->writeAt($pdf, 77.6, 263.9, $data->signerName);
            $this->writeAt($pdf, 138.8, 263.9, $data->signerTitle);

            // Special Elements
            $this->addSignatureWithHeight($pdf, $certificate, 14.1, 260.4, 6);
        }
    }
}

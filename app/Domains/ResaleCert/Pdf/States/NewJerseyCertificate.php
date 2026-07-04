<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class NewJerseyCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/new_jersey.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Determine if in-state or out-of-state (different forms)
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'NJ');

        if ($isInState) {
            // In-state NJ form coordinates
            $this->fillInStateForm($pdf, $data, $certificate);
        } else {
            // Out-of-state form coordinates
            $this->fillOutOfStateForm($pdf, $data, $certificate);
        }
    }

    /**
     * Fill in-state NJ form
     */
    protected function fillInStateForm(Fpdi $pdf, $data, $certificate): void
    {
        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 24.7, 85.6, $data->businessName);
        $this->writeAt($pdf, 77.6, 76.0, $data->businessTaxId);
        $this->writeAt($pdf, 28.2, 99.0, $data->businessStreetAddress);
        $this->writeAt($pdf, 85.8, 99.0, $data->businessCity);
        $this->writeAt($pdf, 127.0, 99.0, $data->businessState);
        $this->writeAt($pdf, 163.5, 99.0, $data->businessZip);
        $this->writeAt($pdf, 41.2, 110.5, $data->businessType);

        // Vendor Information
        $this->writeAt($pdf, 24.7, 52.2, $data->vendorName);
        $this->writeAt($pdf, 27.5, 61.6, $data->vendorStreetAddress);
        $this->writeAt($pdf, 163.5, 61.6, $data->vendorZip);
        $this->writeAt($pdf, 85.8, 61.6, $data->vendorCity);
        $this->writeAt($pdf, 127.0, 61.6, $data->vendorState);

        // Certificate Details
        $this->writeAt($pdf, 27.5, 146.3, $data->productDescription);
        $this->writeAt($pdf, 27.5, 162.8, $data->productDescription);
        $this->writeAt($pdf, 130.5, 254.5, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 31.8, 233.3, $data->signerName);
        $this->writeAt($pdf, 22.3, 254.5, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 160.2, 27.0, $data->checkmarkX);
        $this->writeAt($pdf, 27.0, 173.3, $data->checkmarkX);
        $this->writeAt($pdf, 27.0, 196.9, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 45.9, 239.2, 6);
    }

    /**
     * Fill out-of-state form
     */
    protected function fillOutOfStateForm(Fpdi $pdf, $data, $certificate): void
    {
        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 23.0, 98.1, $data->businessName);
        $this->writeAt($pdf, 38.1, 105.6, $data->businessFullAddress);

        // State (tax ID source state)
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
        $this->writeAt($pdf, 43.5, 113.4, $taxIdSourceState);

        $this->writeAt($pdf, 60.0, 120.9, $data->businessTaxId);
        $this->writeAt($pdf, 102.8, 128.6, $data->businessType);

        // Vendor Information
        $this->writeAt($pdf, 22.3, 67.5, $data->vendorName);
        $this->writeAt($pdf, 24.7, 75.0, $data->vendorFullAddress);

        // Certificate Details
        $this->writeAt($pdf, 74.1, 136.2, $data->productDescription);
        $this->writeAt($pdf, 56.4, 143.7, $data->productDescription);
        $this->writeAt($pdf, 121.1, 229.5, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 27.5, 210.3, $data->signerName);
        $this->writeAt($pdf, 18.8, 230.5, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 39.3, 159.5, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 39.7, 215.7, 6);
    }
}

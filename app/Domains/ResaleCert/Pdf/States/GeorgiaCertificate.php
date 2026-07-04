<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class GeorgiaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/georgia.pdf';
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
        $isInState = ($taxIdSourceState === 'GA');

        if ($isInState) {
            // In-state GA form coordinates
            $this->fillInStateForm($pdf, $data, $certificate);
        } else {
            // Out-of-state form coordinates
            $this->fillOutOfStateForm($pdf, $data, $certificate);
        }
    }

    /**
     * Fill in-state GA form
     */
    protected function fillInStateForm(Fpdi $pdf, $data, $certificate): void
    {
        // Supplier information
        $this->writeAt($pdf, 22.5, 42, $data->vendorName);
        $this->writeAt($pdf, 159.5, 42, $data->issueDate);
        $this->writeAt($pdf, 21.5, 53, $data->vendorStreetAddress);
        $this->writeAt($pdf, 115.5, 53, $data->vendorCity);
        $this->writeAt($pdf, 158.5, 53, $data->vendorState);
        $this->writeAt($pdf, 177.58, 53, $data->vendorZip);

        // Checkbox
        $this->writeAt($pdf, 15.5, 76.5, $data->checkmarkX);

        // Purchaser information
        $this->writeAt($pdf, 45, 224.5, $data->businessName);
        $this->writeAt($pdf, 161, 224.5, $data->businessTaxId);
        $this->writeAt($pdf, 62, 232, $data->businessType);
        $this->writeAt($pdf, 48, 240, $data->businessFullAddress);

        // Signer information
        $this->writeAt($pdf, 36.5, 247.75, $data->signerName);
        $this->writeAt($pdf, 132.5, 247.75, $data->signerTitle);

        // Contact information
        $pdf->SetAutoPageBreak(false);
        $this->writeAt($pdf, 45, 262.5, $data->phone);
        $this->writeAt($pdf, 115, 262.5, $data->email);

        // Add signature
        $this->addSignatureWithHeight($pdf, $certificate, 31, 251, 7);
        $pdf->SetAutoPageBreak(true);
    }

    /**
     * Fill out-of-state form
     */
    protected function fillOutOfStateForm(Fpdi $pdf, $data, $certificate): void
    {
        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 47.5, 181.3, $data->businessName);
        $this->writeAt($pdf, 47.5, 191.2, $data->businessTaxId);

        // State (tax ID source state)
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
        $this->writeAt($pdf, 164.6, 191.2, $taxIdSourceState);

        $this->writeAt($pdf, 47.5, 200.9, $data->businessType);
        $this->writeAt($pdf, 47.5, 210.7, $data->businessFullAddress);

        // Vendor Information
        $this->writeAt($pdf, 19.1, 44.7, $data->vendorName);
        $this->writeAt($pdf, 19.1, 54.6, $data->vendorStreetAddress);
        $this->writeAt($pdf, 114.1, 54.6, $data->vendorCity);
        $this->writeAt($pdf, 152.2, 54.6, $data->vendorState);
        $this->writeAt($pdf, 179.9, 54.6, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 178.7, 44.7, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 58.8, 220.4, $data->signerName);
        $this->writeAt($pdf, 168.2, 220.4, $data->signerTitle);
        $this->writeAt($pdf, 47.5, 230.0, $data->phone);
        $this->writeAt($pdf, 131.7, 230.2, $data->email);

        // Special Elements
        $this->writeAt($pdf, 24.3, 100.4, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 115, 214.8, 8);
    }
}

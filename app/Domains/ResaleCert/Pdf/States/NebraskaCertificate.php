<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class NebraskaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/nebraska.pdf';
    }

    /**
     * Standardize Nebraska Sales Tax ID to exactly 7 digits
     *
     * Rules:
     * - Input may appear as "01-1234567", "011234567", "010134567", "01 1234567", or "1234567"
     * - Remove any leading "01" (with or without hyphen or space)
     * - Remove all non-digit characters
     * - If more than 7 digits remain after cleaning, drop only the first two if they are "01"
     * - Output exactly the final 7 digits
     */
    private function standardizeNebraskaTaxId(?string $taxId): ?string
    {
        if (empty($taxId)) {
            return $taxId;
        }

        // Remove all non-digit characters
        $cleaned = preg_replace('/\D/', '', $taxId);

        // If we have more than 7 digits, check if first two are "01" and remove them
        if (strlen($cleaned) > 7 && substr($cleaned, 0, 2) === '01') {
            $cleaned = substr($cleaned, 2);
        }

        // Return the last 7 digits
        return substr($cleaned, -7);
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Check if in-state or out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'NE');

        // For in-state: standardize Nebraska tax ID to 7 digits
        // For out-of-state: use full tax ID without trimming
        $taxIdToDisplay = $isInState
            ? $this->standardizeNebraskaTaxId($data->businessTaxId)
            : $data->businessTaxId;

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 10.6, 40.7, $data->businessName);
        $this->writeAt($pdf, 10.6, 32.2, $data->businessDba);
        $this->writeAt($pdf, 10.6, 49.2, $data->businessStreetAddress);
        $this->writeAt($pdf, 10.6, 57.6, $data->businessCity);
        $this->writeAt($pdf, 60.0, 57.6, $data->businessState);
        $this->writeAt($pdf, 94.8, 57.6, $data->businessZip);

        // Tax ID and state - different positions for in-state vs out-of-state
        if ($isInState) {
            // In-state: standardized 7-digit ID at original position
            $this->writeAt($pdf, 66.3, 121.4, $taxIdToDisplay);
        } else {
            // Out-of-state: full tax ID and state at different positions
            $this->writeAt($pdf, 57.6, 131.0, $taxIdToDisplay);
            $this->writeAt($pdf, 131.7, 131.0, $taxIdSourceState);
        }

        // Vendor Information
        $this->writeAt($pdf, 109.4, 32.2, $data->vendorName);
        $this->writeAt($pdf, 109.4, 49.2, $data->vendorStreetAddress);
        $this->writeAt($pdf, 110.5, 57.6, $data->vendorCity);
        $this->writeAt($pdf, 159.9, 57.6, $data->vendorState);
        $this->writeAt($pdf, 194.0, 57.6, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 78.1, 98.3, $data->productDescription);
        $this->writeAt($pdf, 17.2, 115.9, $data->productDescription);
        $this->writeAt($pdf, 182.3, 231.0, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 127.0, 231.0, $data->signerTitle);
        $this->writeAt($pdf, 25.9, 239.4, $data->signerName);

        // Special Elements
        $this->writeAt($pdf, 12.5, 71.5, $data->checkmarkX);
        $this->writeAt($pdf, 29.4, 84.4, $data->checkmarkX);
        $this->writeAt($pdf, 121.1, 110.8, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 25.9, 227, 6);
    }
}

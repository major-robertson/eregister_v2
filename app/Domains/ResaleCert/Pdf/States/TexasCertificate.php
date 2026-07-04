<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class TexasCertificate extends BaseStateCertificate
{
    /**
     * Y coordinate for in-state (TX) tax ID digits
     */
    protected $inStateTaxIdY = 64.2;

    /**
     * X positions for each of the 11 digits (in-state only)
     */
    protected $taxIdXPositions = [
        17.5, 22.5, 27.5, 32.5, 37.5, 42.5, 47.5, 52.5, 57.5, 62.5, 67.5,
    ];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/texas.pdf';
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
        $this->writeAt($pdf, 11.8, 36.5, $data->businessName);
        $this->writeAt($pdf, 11.8, 45.2, $data->businessStreetAddress);
        $this->writeAt($pdf, 11.8, 53.4, $data->businessCityStateZip);

        // Texas Sales Tax ID
        // In-state: Place each digit individually at specific x positions
        // Out-of-state: Write full tax ID without breaking it up
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'TX');

        if ($isInState) {
            // In-state: Break into individual digits
            $taxIdDigits = preg_replace('/\D+/', '', $data->businessTaxId ?? '');
            if (! empty($taxIdDigits)) {
                $digits = str_split($taxIdDigits);
                $digitCount = count($digits);

                // Place up to 11 digits at manual positions
                for ($i = 0; $i < min(11, $digitCount); $i++) {
                    $this->writeAt($pdf, $this->taxIdXPositions[$i], $this->inStateTaxIdY, $digits[$i]);
                }
            }
        } else {
            // Out-of-state: Write full tax ID without breaking it up
            $this->writeAt($pdf, 17.6, 76.9, $data->businessTaxId);
        }

        $this->writeAt($pdf, 16.5, 173.8, $data->businessType);

        // Vendor Information
        $this->writeAt($pdf, 24.9, 104.2, $data->vendorName);
        $this->writeAt($pdf, 39.0, 114.5, $data->vendorStreetAddress);
        $this->writeAt($pdf, 45.9, 124.2, $data->vendorCityStateZip);

        // Certificate Details
        $this->writeAt($pdf, 16.5, 142.5, $data->productDescription);
        $this->writeAt($pdf, 169.3, 249.8, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 142.3, 36.5, $data->phone);
        $this->writeAt($pdf, 101.1, 249.8, $data->signerTitle);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 23.0, 246.2, 6);
    }
}

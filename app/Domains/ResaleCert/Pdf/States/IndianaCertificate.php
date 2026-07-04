<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class IndianaCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for in-state (IN) tax ID
     */
    protected $inStateTaxIdCoordinates = ['x' => 54.8, 'y' => 85.4];

    /**
     * Coordinates for out-of-state tax ID and state
     */
    protected $outOfStateTaxIdCoordinates = ['x' => 46.1, 'y' => 102.3];

    protected $outOfStateStateCoordinates = ['x' => 129.5, 'y' => 102.3];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/indiana.pdf';
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
        $this->writeAt($pdf, 49.4, 60.4, $data->businessName);
        $this->writeAt($pdf, 47.0, 66.5, $data->businessStreetAddress);
        $this->writeAt($pdf, 187.0, 66.5, $data->businessZip);
        $this->writeAt($pdf, 106.4, 66.5, $data->businessCity);
        $this->writeAt($pdf, 151.7, 66.5, $data->businessState);

        // Tax ID - different handling for in-state vs out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'IN');

        if ($isInState) {
            // In-state: tax ID at normal position and "001" code
            $this->writeAt($pdf, $this->inStateTaxIdCoordinates['x'], $this->inStateTaxIdCoordinates['y'], $data->businessTaxId);
            $this->writeAt($pdf, 144.6, 85.4, '001');
        } else {
            // Out-of-state: tax ID and state at different positions
            $this->writeAt($pdf, $this->outOfStateTaxIdCoordinates['x'], $this->outOfStateTaxIdCoordinates['y'], $data->businessTaxId);
            $this->writeAt($pdf, $this->outOfStateStateCoordinates['x'], $this->outOfStateStateCoordinates['y'], $taxIdSourceState);
        }

        // Vendor Information
        $this->writeAt($pdf, 43.6, 112.3, $data->vendorName);
        $this->writeAt($pdf, 106.4, 118.9, $data->vendorCity);
        $this->writeAt($pdf, 47.0, 119.2, $data->vendorStreetAddress);
        $this->writeAt($pdf, 151.7, 119.2, $data->vendorState);
        $this->writeAt($pdf, 187.0, 119.2, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 74.1, 138.1, $data->productDescription);
        $this->writeAt($pdf, 148.2, 245.1, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 42.3, 252.1, $data->signerName);
        $this->writeAt($pdf, 148.2, 252.1, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 33.0, 131.5, $data->checkmarkX);
        $this->writeAt($pdf, 21.2, 152.5, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 55.3, 240.5, 6);
    }
}

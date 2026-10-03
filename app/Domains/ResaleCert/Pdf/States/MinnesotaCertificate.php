<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MinnesotaCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 12.9, 'y' => 98.8];

    protected $stateCoordinates = ['x' => 101.1, 'y' => 98.8];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/minnesota.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        $pdf->SetAutoPageBreak(false);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 12.9, 82.1, $data->businessName);
        $this->writeAt($pdf, 101.1, 90.6, $data->businessCity);
        $this->writeAt($pdf, 147.0, 90.6, $data->businessState);
        $this->writeAt($pdf, 164.6, 90.6, $data->businessZip);
        $this->writeAt($pdf, 12.9, 90.6, $data->businessStreetAddress);

        // Tax ID and state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;

        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);
        $this->writeAt($pdf, $this->stateCoordinates['x'], $this->stateCoordinates['y'], $taxIdSourceState);
        $this->writeAt($pdf, 137.6, 177.4, $data->businessType);

        // Vendor Information
        $this->writeAt($pdf, 12.9, 115.0, $data->vendorName);
        $this->writeAt($pdf, 12.9, 124.2, $data->vendorStreetAddress);
        $this->writeAt($pdf, 101.1, 124.2, $data->vendorCity);
        $this->writeAt($pdf, 147.0, 124.2, $data->vendorState);
        $this->writeAt($pdf, 164.6, 124.2, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 167.7, 258.5, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 76.4, 258.5, $data->signerName);
        $this->writeAt($pdf, 129.6, 258.5, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 105.8, 178.0, $data->checkmarkX);
        $this->writeAt($pdf, 12.9, 227.7, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 12.9, 254.5, 6);

        $pdf->SetAutoPageBreak(true);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class IdahoCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for in-state (ID) tax ID
     */
    protected $inStateTaxIdCoordinates = ['x' => 102.8, 'y' => 95.7];

    /**
     * Coordinates for out-of-state "No tax registration" checkmark
     */
    protected $outOfStateCheckmarkCoordinates = ['x' => 28.7, 'y' => 113.5];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/idaho.pdf';
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
        $this->writeAt($pdf, 12.9, 31.0, $data->businessName);
        $this->writeAt($pdf, 12.9, 39.3, $data->businessStreetAddress);
        $this->writeAt($pdf, 12.9, 47.5, $data->businessCity);
        $this->writeAt($pdf, 61.1, 47.5, $data->businessState);
        $this->writeAt($pdf, 76.6, 47.5, $data->businessZip);
        $this->writeAt($pdf, 88.2, 79.3, $data->businessType);

        // Tax ID - only for in-state, otherwise mark "No tax registration"
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'ID');

        if ($isInState) {
            // In-state: write the tax ID and upper checkmark
            $this->writeAt($pdf, 28.7, 96, $data->checkmarkX);
            $this->writeAt($pdf, $this->inStateTaxIdCoordinates['x'], $this->inStateTaxIdCoordinates['y'], $data->businessTaxId);
        } else {
            // Out-of-state: checkmark "No tax registration for this state" (lower checkmark only)
            $this->writeAt($pdf, $this->outOfStateCheckmarkCoordinates['x'], $this->outOfStateCheckmarkCoordinates['y'], $data->checkmarkX);
        }

        $this->writeAt($pdf, 12.9, 265.1, $data->businessEin);

        // Vendor Information
        $this->writeAt($pdf, 108.2, 31.0, $data->vendorName);
        $this->writeAt($pdf, 108.2, 39.3, $data->vendorStreetAddress);
        $this->writeAt($pdf, 108.2, 47.5, $data->vendorCity);
        $this->writeAt($pdf, 156.4, 47.5, $data->vendorState);
        $this->writeAt($pdf, 172.9, 47.5, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 88.2, 86, $data->productDescription);
        $this->writeAt($pdf, 151.7, 265.1, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 81.1, 256.8, $data->signerName);
        $this->writeAt($pdf, 151.7, 256.8, $data->signerTitle);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 12.9, 253, 6);
        $pdf->SetAutoPageBreak(true);
    }
}

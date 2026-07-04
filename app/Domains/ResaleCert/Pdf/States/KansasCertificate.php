<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class KansasCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for in-state (KS) tax ID
     */
    protected $inStateTaxIdCoordinates = ['x' => 49.4, 'y' => 54.6];

    /**
     * Coordinates for out-of-state tax ID
     * TODO: Update these coordinates using the dev grid overlay tool
     */
    protected $outOfStateTaxIdCoordinates = ['x' => 49.4, 'y' => 62.0];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/kansas.pdf';
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
        // Business Information - Tax ID uses appropriate coordinates
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'KS');
        $taxIdCoords = $isInState ? $this->inStateTaxIdCoordinates : $this->outOfStateTaxIdCoordinates;
        $this->writeAt($pdf, $taxIdCoords['x'], $taxIdCoords['y'], $data->businessTaxId);
        $this->writeAt($pdf, 28.5, 104.7, $data->businessName);
        $this->writeAt($pdf, 25.6, 115.7, $data->businessAddress1);
        $this->writeAt($pdf, 112.9, 115.7, $data->businessCity);
        $this->writeAt($pdf, 176.4, 115.7, $data->businessZip);
        $this->writeAt($pdf, 157.6, 115.7, $data->businessState);

        // Vendor Information
        $this->writeAt($pdf, 22.3, 26.3, $data->vendorName);
        $this->writeAt($pdf, 25.9, 37.2, $data->vendorStreetAddress);
        $this->writeAt($pdf, 112.9, 37.2, $data->vendorCity);
        $this->writeAt($pdf, 157.6, 37.2, $data->vendorState);
        $this->writeAt($pdf, 176.4, 37.2, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 11.8, 64.7, $data->productDescription);
        $this->writeAt($pdf, 11.8, 81.8, $data->productDescription);
        $this->writeAt($pdf, 167.0, 126.3, $data->issueDate);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 27.0, 122, 7);
    }
}

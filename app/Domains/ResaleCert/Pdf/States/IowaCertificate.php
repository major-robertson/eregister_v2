<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class IowaCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 37.6, 'y' => 128.6];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/iowa.pdf';
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
        $this->writeAt($pdf, 51.7, 77.1, $data->businessName);
        $this->writeAt($pdf, 47.0, 84.0, $data->businessDba);
        $this->writeAt($pdf, 25.9, 91.0, $data->businessStreetAddress);
        $this->writeAt($pdf, 18.8, 98.1, $data->businessCity);
        $this->writeAt($pdf, 63.5, 98.1, $data->businessState);
        $this->writeAt($pdf, 87.0, 98.1, $data->businessZip);

        // Tax ID
        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);

        // Vendor Information
        $this->writeAt($pdf, 145.8, 77.1, $data->vendorName);
        $this->writeAt($pdf, 127.0, 91.0, $data->vendorStreetAddress);
        $this->writeAt($pdf, 119.9, 98.1, $data->vendorCity);
        $this->writeAt($pdf, 167.0, 98.1, $data->vendorState);
        $this->writeAt($pdf, 188.1, 98.1, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 171.7, 243.9, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 47.0, 234.5, $data->signerName);
        $this->writeAt($pdf, 145.8, 234.5, $data->email);
        $this->writeAt($pdf, 23.5, 105.1, $data->phone);

        // Special Elements
        $this->writeAt($pdf, 45.2, 65.1, $data->checkmarkX);
        $this->writeAt($pdf, 112.2, 122.8, $data->checkmarkX);
        $this->writeAt($pdf, 8.9, 118.1, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 51.7, 240.4, 6);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MichiganCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 100.4, 'y' => 130.5];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/michigan.pdf';
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
        // Business Information - Tax ID
        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);
        $this->writeAt($pdf, 15.3, 232.1, $data->businessName);
        $this->writeAt($pdf, 159.9, 232.1, $data->businessType);
        $this->writeAt($pdf, 15.3, 240.6, $data->businessStreetAddress);
        $this->writeAt($pdf, 108.2, 240.6, $data->businessCityStateZip);

        // Vendor Information
        $this->writeAt($pdf, 14.1, 81.6, $data->vendorName);
        $this->writeAt($pdf, 74.1, 81.6, $data->vendorFullAddress);

        // Certificate Details
        $this->writeAt($pdf, 167.5, 259.4, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 15.3, 249.1, $data->phone);
        $this->writeAt($pdf, 108.2, 259.4, $data->signerTitle);
        $this->writeAt($pdf, 108.2, 249.1, $data->signerName);

        // Special Elements
        $this->writeAt($pdf, 25.9, 60.7, $data->checkmarkX);
        $this->writeAt($pdf, 25.9, 98.8, $data->checkmarkX);
        $this->writeAt($pdf, 25.9, 130.5, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 15.3, 255.6, 6);

        $pdf->SetAutoPageBreak(true);
    }
}

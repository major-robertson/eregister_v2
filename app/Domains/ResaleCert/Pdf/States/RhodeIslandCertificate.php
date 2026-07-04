<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class RhodeIslandCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/rhode_island.pdf';
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
        $this->writeAt($pdf, 50.3, 81.4, $data->businessTaxId);
        $this->writeAt($pdf, 102.3, 209.6, $data->businessName);
        $this->writeAt($pdf, 102.3, 217.8, $data->businessStreetAddress);
        $this->writeAt($pdf, 102.3, 225.8, $data->businessCityStateZip);

        // Vendor Information
        $this->writeAt($pdf, 31.8, 104.2, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 115.9, 87.7, $data->productDescription);
        $this->writeAt($pdf, 40.0, 168.6, $data->productDescription);
        $this->writeAt($pdf, 41, 242.2, date('F j', strtotime($data->issueDate))); // Month, Day
        $this->writeAt($pdf, 96, 242.2, date('y', strtotime($data->issueDate))); // Last 2 digits of year

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 111.7, 249.8, 6);
    }
}

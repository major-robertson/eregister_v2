<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MississippiCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/mississippi.pdf';
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
        $this->writeAt($pdf, 62.6, 133.6, $data->businessTaxId);
        $this->writeAt($pdf, 106.1, 172.6, $data->businessName);
        $this->writeAt($pdf, 125.1, 206.7, $data->businessStreetAddress);
        $this->writeAt($pdf, 106.5, 216.4, $data->businessCityStateZip);

        // Vendor Information
        $this->writeAt($pdf, 37.6, 56.0, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 41.2, 143.5, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 114.1, 196.9, $data->signerName);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 106.5, 182.7, 6);
    }
}

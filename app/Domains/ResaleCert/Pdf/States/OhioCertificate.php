<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class OhioCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/ohio.pdf';
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
        $this->writeAt($pdf, 14.1, 87.5, $data->businessType);
        $this->writeAt($pdf, 88.2, 134.1, $data->businessType);
        $this->writeAt($pdf, 88.2, 143.9, $data->businessStreetAddress);
        $this->writeAt($pdf, 88.2, 153.8, $data->businessCityStateZip);

        // Vendor Information
        $this->writeAt($pdf, 12.9, 56.9, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 88.2, 173.3, $data->issueDate);
        $this->writeAt($pdf, 88.2, 182.7, $data->businessTaxId);

        // Contact Information
        $this->writeAt($pdf, 88.2, 123.9, $data->signerName);
        $this->writeAt($pdf, 167.0, 163.9, $data->signerTitle);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 88.2, 160, 6);
    }
}

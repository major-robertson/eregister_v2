<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class PennsylvaniaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/pennsylvania.pdf';
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
        $this->writeAt($pdf, 73.1, 123.9, $data->businessTaxId);
        $this->writeAt($pdf, 14.1, 203.9, $data->businessName);
        $this->writeAt($pdf, 139.9, 203.9, $data->businessEin);
        $this->writeAt($pdf, 14.1, 213.8, $data->businessStreetAddress);
        $this->writeAt($pdf, 104.7, 213.8, $data->businessCity);
        $this->writeAt($pdf, 174.0, 213.8, $data->businessZip);
        $this->writeAt($pdf, 158.8, 213.8, $data->businessState);

        // Vendor Information
        $this->writeAt($pdf, 14.1, 68.9, $data->vendorName);
        $this->writeAt($pdf, 14.1, 78.1, $data->vendorStreetAddress);
        $this->writeAt($pdf, 102.3, 78.1, $data->vendorCity);
        $this->writeAt($pdf, 158.8, 78.1, $data->vendorState);
        $this->writeAt($pdf, 175.2, 78.1, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 174.0, 203.9, $data->issueDate);

        // Special Elements
        $this->writeAt($pdf, 64.0, 20.5, $data->checkmarkX);
        $this->writeAt($pdf, 40.0, 58.1, $data->checkmarkX);
        $this->writeAt($pdf, 15.3, 125.1, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 95.3, 200, 6);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class VermontCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/vermont.pdf';
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
        $this->writeAt($pdf, 16.9, 77.4, $data->businessName);
        $this->writeAt($pdf, 150.5, 77.4, $data->businessEin);
        $this->writeAt($pdf, 16.9, 85.8, $data->businessDba);
        $this->writeAt($pdf, 16.9, 94.3, $data->businessStreetAddress);
        $this->writeAt($pdf, 16.9, 102.5, $data->businessCity);
        $this->writeAt($pdf, 136.6, 102.5, $data->businessState);
        $this->writeAt($pdf, 150.5, 102.5, $data->businessZip);
        $this->writeAt($pdf, 16.9, 111.2, $data->businessType);
        $this->writeAt($pdf, 154.3, 177.8, $data->businessTaxId);

        // Vendor Information
        $this->writeAt($pdf, 16.9, 127.5, $data->vendorName);
        $this->writeAt($pdf, 16.9, 135.9, $data->vendorStreetAddress);
        $this->writeAt($pdf, 16.9, 144.6, $data->vendorCity);
        $this->writeAt($pdf, 150.5, 144.6, $data->vendorZip);
        $this->writeAt($pdf, 136.6, 144.6, $data->vendorState);

        // Certificate Details
        $this->writeAt($pdf, 16.9, 161.3, $data->productDescription);
        $this->writeAt($pdf, 172.9, 246.7, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 150.5, 85.8, $data->phone);
        $this->writeAt($pdf, 122.3, 246.7, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 56.2, 61.2, $data->checkmarkX);
        $this->writeAt($pdf, 25.9, 178.0, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 28.2, 241, 8);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MassachusettsCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/massachusetts.pdf';
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
        $this->writeAt($pdf, 14.8, 36.5, $data->businessName);
        $this->writeAt($pdf, 141.8, 36.5, $data->businessTaxId);
        $this->writeAt($pdf, 14.8, 45.2, $data->businessStreetAddress);
        $this->writeAt($pdf, 141.8, 53.4, $data->businessState);
        $this->writeAt($pdf, 162.7, 53.4, $data->businessZip);
        $this->writeAt($pdf, 14.8, 53.4, $data->businessCity);
        $this->writeAt($pdf, 14.8, 61.6, $data->businessType);

        // Vendor Information
        $this->writeAt($pdf, 14.8, 87.3, $data->vendorName);
        $this->writeAt($pdf, 14.8, 95.7, $data->vendorStreetAddress);
        $this->writeAt($pdf, 77.6, 95.7, $data->vendorCity);
        $this->writeAt($pdf, 141.8, 95.7, $data->vendorState);
        $this->writeAt($pdf, 162.7, 95.7, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 14.8, 70.3, $data->productDescription);
        $this->writeAt($pdf, 141.8, 122.1, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 77.6, 122.1, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 83.0, 126.5, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 14.8, 119, 5);
    }
}

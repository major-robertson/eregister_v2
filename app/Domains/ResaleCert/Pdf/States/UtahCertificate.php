<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class UtahCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/utah.pdf';
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
        $this->writeAt($pdf, 11.3, 30.1, $data->businessName);
        $this->writeAt($pdf, 11.3, 38.1, $data->businessStreetAddress);
        $this->writeAt($pdf, 108.9, 38.1, $data->businessCity);
        $this->writeAt($pdf, 178.7, 38.1, $data->businessZip);
        $this->writeAt($pdf, 156.4, 38.1, $data->businessState);
        $this->writeAt($pdf, 55.3, 61.6, $data->businessTaxId);

        // Vendor Information
        $this->writeAt($pdf, 55.3, 53.6, $data->vendorName);

        // Certificate Details
        $this->writeAt($pdf, 156.4, 53.6, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 156.4, 30.1, $data->phone);
        $this->writeAt($pdf, 82.1, 45.9, $data->signerName);
        $this->writeAt($pdf, 156.4, 45.9, $data->signerTitle);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 11.1, 42, 6);
        $this->writeAt($pdf, 11.1, 100.4, $data->checkmarkX);
    }
}

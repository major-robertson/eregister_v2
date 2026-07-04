<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class VirginiaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/virginia.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Supplier information
        $this->writeAt($pdf, 18.5, 55.5, $data->vendorName);
        $this->writeAt($pdf, 150.5, 55.5, $data->issueDate);
        $this->writeAt($pdf, 12.5, 65.5, $data->vendorStreetAddress);
        $this->writeAt($pdf, 97, 65.5, $data->vendorCity);
        $this->writeAt($pdf, 162.5, 65.5, $data->vendorState);
        $this->writeAt($pdf, 185.5, 65.5, $data->vendorZip);

        // Checkbox
        $this->writeAt($pdf, 12.5, 132.5, $data->checkmarkX);

        // Business information
        $this->writeAt($pdf, 38.5, 174.5, $data->businessName);
        $this->writeAt($pdf, 137.5, 174.5, $data->businessTaxId);
        $this->writeAt($pdf, 30.5, 181.85, $data->businessDba);

        // Business address
        $this->writeAt($pdf, 27.5, 190.5, $data->businessStreetAddress);
        $this->writeAt($pdf, 102.5, 190.5, $data->businessCity);
        $this->writeAt($pdf, 159.5, 190.5, $data->businessState);
        $this->writeAt($pdf, 184, 190.5, $data->businessZip);

        // Business type and signer title
        $this->writeAt($pdf, 75, 206.5, $data->businessType);
        $this->writeAt($pdf, 124.75, 227.5, $data->signerTitle);

        // Add signature
        $this->addSignatureWithHeight($pdf, $certificate, 20, 222, 8);
    }
}

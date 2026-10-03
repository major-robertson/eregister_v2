<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

/**
 * Nevada Resale Certificate, TAX-F005 V2026.1.
 */
class NevadaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/nevada.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        $pdf->SetFont('Helvetica', '', 9);

        // "I hold valid seller's permit, Location ID number ____"
        $this->writeAt($pdf, 138, 62.2, $data->businessTaxId);

        // "engaged in the business of selling:" box
        $this->writeAt($pdf, 24, 79, $data->productDescription);

        // "which I purchase from:" box (seller name, then address)
        $this->writeAt($pdf, 24, 104.5, $data->vendorName);
        $this->writeAt($pdf, 24, 109.5, $data->vendorFullAddress);

        // "Description of the property to be purchased:" box
        $this->writeAt($pdf, 24, 163.5, $data->productDescription);

        // "Purchaser Location Address:" box
        $this->writeAt($pdf, 24, 206, $data->businessFullAddress);

        // "Purchaser Name (Print):"
        $this->writeAt($pdf, 66, 225, $data->businessName);

        // "Signature of Purchaser:" and "Dated:"
        $this->addSignatureWithHeight($pdf, $certificate, 66, 229.8, 7);
        $this->writeAt($pdf, 162, 233.7, $data->issueDate);
    }
}

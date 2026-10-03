<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

/**
 * Kentucky Resale Certificate, 51A105 (1-23). The form is 6 x 4 inches.
 */
class KentuckyCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/kentucky.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Set smaller font size for all fields
        $pdf->SetFont('Helvetica', '', 6);

        // Disable text wrapping and remove margins
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        // "Check Applicable Block": Blanket
        $this->writeAt($pdf, 141.1, 8.4, $data->checkmarkX);

        // "I hereby certify that ____" (Name of Business, Address)
        $this->writeAt($pdf, 35, 14.6, $data->businessName);
        $this->writeAt($pdf, 92, 14.6, $data->businessFullAddress);

        // "Sales and Use Tax Permit, Account No. ____"
        $this->writeAt($pdf, 67, 20.6, $data->businessTaxId);

        // "engaged in the business of selling ... the following:"
        $this->writeAt($pdf, 8.5, 27, $data->productDescription);

        // "which I shall purchase from:" (Name of Seller, Address)
        $this->writeAt($pdf, 8.5, 36.3, $data->vendorName);
        $this->writeAt($pdf, 72, 36.3, $data->vendorFullAddress);

        // "Description of product to be purchased:"
        $this->writeAt($pdf, 8.5, 56.7, $data->productDescription);

        // Authorized Signature, Title, Date
        $this->addSignatureWithHeight($pdf, $certificate, 8.5, 66.2, 6);
        $this->writeAt($pdf, 86.5, 69.6, $data->signerTitle);
        $this->writeAt($pdf, 120.5, 69.6, $data->issueDate);

        // Re-enable auto page break
        $pdf->SetAutoPageBreak(true);
    }
}

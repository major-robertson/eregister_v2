<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class CaliforniaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/california.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $pdf->SetAutoPageBreak(false);

        $data = $this->extractCertificateData($certificate);

        $this->writeAt($pdf, 76, 61, $data->businessTaxId);

        // ---------------------------------------------------------------------
        // 2) I am engaged in the business of selling the following type of TPP:
        // ---------------------------------------------------------------------
        // (fills to the right of the printed sentence)
        $this->writeAt($pdf, 20, 78, $data->businessType);

        // ---------------------------------------------------------------------
        // 3) "This certificate is for the purchase from"  — vendor's name inline
        // ---------------------------------------------------------------------
        $this->writeAt($pdf, 85, 87, $data->vendorName);

        // ---------------------------------------------------------------------
        // 5) Description of property to be purchased for resale
        // ---------------------------------------------------------------------
        // (first writing baseline just below the label; add wrapping if desired)
        $this->writeAt($pdf, 21, 139, $data->productDescription);

        // ---------------------------------------------------------------------
        // Purchaser section (bottom of page)
        // ---------------------------------------------------------------------

        // NAME OF PURCHASER  — line sits just above the small-caps label
        $this->writeAt($pdf, 18, 217, $data->businessName);

        // SIGNATURE — line sits just above the "SIGNATURE OF PURCHASER..." caption
        $this->addSignatureWithHeight($pdf, $certificate, 20, 222, 6);

        // PRINTED NAME OF PERSON SIGNING (left) and TITLE (right)
        $this->writeAt($pdf, 18, 234, $data->signerName);
        $this->writeAt($pdf, 127, 234, $data->signerTitle);

        $this->writeAt($pdf, 18, 243, $data->businessFullAddress);

        // TELEPHONE NUMBER (left) and DATE (right)
        $phoneWithoutParens = str_replace(['(', ')'], ' ', $data->phone);
        $this->writeAt($pdf, 19.5, 251, $phoneWithoutParens);
        $this->writeAt($pdf, 127, 251, $data->issueDate);

        $pdf->SetAutoPageBreak(true);
    }
}

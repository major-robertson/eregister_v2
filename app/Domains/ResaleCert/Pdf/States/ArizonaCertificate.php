<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class ArizonaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/arizona.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Compute default 12‑month blanket period from issueDate (used for "Period From/Through")
        $periodFrom = $data->issueDate;
        $periodThrough = $data->issueDate;
        try {
            $start = new \DateTime($data->issueDate ?: 'now');
            $periodFrom = $start->format('m/d/Y');
            $end = (clone $start)->modify('+12 months -1 day');
            $periodThrough = $end->format('m/d/Y');
        } catch (\Exception $e) { /* fall back to provided value */
        }

        // -----------------------------
        // A. Business Name and Address
        // -----------------------------
        // Business Name (left box) and TPT/Sales Tax License No. (right box)
        $this->writeAt($pdf, 13, 73, $data->businessName);
        $this->writeAt($pdf, 78, 73, $data->businessTaxId);

        // Address line
        $this->writeAt($pdf, 13, 82, $data->businessStreetAddress);

        // City / State / ZIP (three adjoining boxes)
        $this->writeAt($pdf, 13, 90, $data->businessCity);
        $this->writeAt($pdf, 77, 90, $data->businessState);
        $this->writeAt($pdf, 94, 90, $data->businessZip);

        // Business Email (Optional)
        $this->writeAt($pdf, 13, 98, $data->email);
        $this->writeAt($pdf, 113, 98, $data->phone);

        // Vendor's Name (left)
        $this->writeAt($pdf, 13, 107, $data->vendorName);

        // -----------------------------
        // B. Check Applicable Box
        // -----------------------------
        // Blanket => select "Period" (second row), and fill From/Through
        $this->writeAt($pdf, 112.5, 74, $data->checkmarkX); // "Period" checkbox (NOT single-use)
        $this->writeAt($pdf, 134, 74, $periodFrom);       // Period From
        $this->writeAt($pdf, 176, 74, $periodThrough);    // Through

        // ----------------------------------------------------
        // C. Precise Nature of Purchaser's Business
        // ----------------------------------------------------
        $this->writeAt($pdf, 15, 120, $data->businessType);

        // ----------------------------------------------------
        // D. Description of Property Being Purchased
        // ----------------------------------------------------
        $this->writeAt($pdf, 15, 142, $data->productDescription);

        // ----------------------------------------------------
        // E.
        // ----------------------------------------------------
        $this->writeAt($pdf, 13, 167, $data->checkmarkX);

        // ---------------------------------------------
        // F. Certification — print name / sign / title / date
        // ---------------------------------------------
        // Align printed name on the underline in the "I, (print full name) ..." sentence
        $this->writeAt($pdf, 43, 243, $data->signerName);

        $pdf->SetAutoPageBreak(false);

        // Signature of Purchaser (baseline sits just above the TITLE/DATE lines)
        $this->addSignatureWithHeight($pdf, $certificate, 15, 257, 8);

        // Title and Date: drop to sit on their lines
        $this->writeAt($pdf, 117, 262, $data->signerTitle);
        $this->writeAt($pdf, 174, 262, $data->issueDate);

        $pdf->SetAutoPageBreak(true);
    }
}

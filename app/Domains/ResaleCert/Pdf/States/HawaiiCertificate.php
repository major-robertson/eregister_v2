<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class HawaiiCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/hawaii.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $pdf->SetAutoPageBreak(false);

        $d = $this->extractCertificateData($certificate);

        // -----------------------------
        // Top: "To" (Seller) block
        // -----------------------------
        // Name of Seller
        $this->writeAt($pdf, 19, 47, $d->vendorName);

        // Address of Seller (line 1 + line 2 if present)
        $this->writeAt($pdf, 19, 60, $d->vendorStreetAddress);

        // Date of this Certificate (right side of seller address row)
        $this->writeAt($pdf, 126, 60, $d->issueDate);

        // City / State / Postal (Seller)
        $this->writeAt($pdf, 19, 72, $d->vendorCity);
        $this->writeAt($pdf, 58, 72, $d->vendorState);
        $this->writeAt($pdf, 83, 72, $d->vendorZip);

        // -----------------------------
        // Hawaii Tax ID & Nature of Business
        // -----------------------------
        // GE __ __ __ - __ __ __ - __ __ __ __ - __ __
        // Hawaii General Excise Tax ID (12 digits)
        $taxIdDigits = preg_replace('/\D+/', '', $d->businessTaxId ?? '');
        if (! empty($taxIdDigits)) {
            // Manual x positions for 12 digits (all at y=102)
            // Format: GE XXX-XXX-XXXX-XX
            $xPositions = [
                125,   // 1st digit
                130,   // 2nd digit
                135,   // 3rd digit
                141,   // 4th digit (after dash)
                146,   // 5th digit
                151,   // 6th digit
                156.5,   // 7th digit (after dash)
                161.5,   // 8th digit
                166.5,   // 9th digit
                171,   // 10th digit
                178,   // 11th digit (after dash)
                183,   // 12th digit
            ];

            $digits = str_split($taxIdDigits);
            $digitCount = count($digits);

            // Place up to 12 digits at manual positions
            for ($i = 0; $i < min(12, $digitCount); $i++) {
                $this->writeAt($pdf, $xPositions[$i], 102, $digits[$i]);
            }
        }

        // Nature/character of Purchaser's business
        $this->writeAt($pdf, 26, 121, $d->businessType);

        // -----------------------------
        // Blanket application language (no fields)
        // -----------------------------

        // -----------------------------
        // Checkbox section (resale at retail/lease; resale at wholesale)
        // Default: check Retail/Lease only. If you track sale type, conditionally X the second.
        // -----------------------------
        // Retail / leases (first box)
        $this->writeAt($pdf, 30, 173.50, $d->checkmarkX);

        // -----------------------------
        // Bottom: Purchaser & Signature block (two-column layout)
        // Left column: Name/Address/City-State-ZIP
        // Right column: Signature/Printed Name/Title/Date
        // -----------------------------
        // Name of Purchaser
        $this->writeAt($pdf, 13, 212, $d->businessName);

        // Signature of Purchaser (right of Name row)
        $this->addSignatureWithHeight($pdf, $certificate, 113, 207, 8);

        // Address of Purchaser
        $this->writeAt($pdf, 13, 223, $d->businessStreetAddress);

        // Print Name of Signatory (right of Address row)
        $this->writeAt($pdf, 113, 223, $d->signerName);

        // City / State / ZIP (Purchaser)
        $this->writeAt($pdf, 13, 233, $d->businessCity);
        $this->writeAt($pdf, 55, 233, $d->businessState);
        $this->writeAt($pdf, 85, 233, $d->businessZip);

        // Title and Date (right column)
        $this->writeAt($pdf, 113, 233, $d->signerTitle);
        $this->writeAt($pdf, 184, 233, $d->issueDate);

        $pdf->SetAutoPageBreak(true);
    }
}

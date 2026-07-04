<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class DistrictOfColumbiaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/district_of_columbia.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // -----------------------------
        // LEFT COLUMN — TO: SELLER
        // -----------------------------
        $this->writeAt($pdf, 22, 66, $data->vendorName);
        $this->writeAt($pdf, 22, 92, $data->vendorStreetAddress);
        $this->writeAt($pdf, 22, 105, $data->vendorCity);
        $this->writeAt($pdf, 62, 105, $data->vendorState);  // STATE cell
        $this->writeAt($pdf, 83, 105, $data->vendorZip);    // ZIP cell

        // -----------------------------
        // RIGHT COLUMN — FROM: PURCHASER
        // -----------------------------
        $this->writeAt($pdf, 110, 66, $data->businessName);
        $this->writeAt($pdf, 110, 79, $data->businessDba);
        $this->writeAt($pdf, 110, 92, $data->businessStreetAddress);
        $this->writeAt($pdf, 110, 105, $data->businessCity);
        $this->writeAt($pdf, 151, 105, $data->businessState); // STATE cell
        $this->writeAt($pdf, 171, 105, $data->businessZip);   // ZIP cell

        // DC Sales and Use Tax Account ID Number (Purchaser)
        // (Form is invalid without this number.)
        // Place each digit individually at specific x positions, all at height 110
        $taxIdDigits = preg_replace('/\D+/', '', $data->businessTaxId ?? '');
        if (! empty($taxIdDigits)) {
            // Manual x positions for first 12 digits (all at y=110)
            $xPositions = [
                109,  // 1st digit
                114,  // 2nd digit
                119.75,  // 3rd digit
                125,  // 4th digit
                130,  // 5th digit
                136,  // 6th digit
                141.25,  // 7th digit
                146.50,  // 8th digit
                152.50,  // 9th digit
                157.50,  // 10th digit
                163.25,  // 11th digit
                168.50,  // 12th digit
            ];

            $digits = str_split($taxIdDigits);
            $digitCount = count($digits);

            // Place up to 12 digits at manual positions
            for ($i = 0; $i < min(12, $digitCount); $i++) {
                $this->writeAt($pdf, $xPositions[$i], 136, $digits[$i]);
            }
        }

        // -----------------------------
        // Signature / Title / Date
        // -----------------------------
        $pdf->SetAutoPageBreak(false);

        // Signature image sits on the "AUTHORIZED SIGNATURE" line
        $this->addSignatureWithHeight($pdf, $certificate, 20, 175, 8);

        // Title (center) and Date (right) on their caption lines
        $this->writeAt($pdf, 111, 178, $data->signerTitle);
        $this->writeAt($pdf, 166, 178, $data->issueDate);

        $pdf->SetAutoPageBreak(true);
    }
}

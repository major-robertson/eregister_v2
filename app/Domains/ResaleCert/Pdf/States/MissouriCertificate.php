<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MissouriCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/missouri.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        $data = $this->extractCertificateData($certificate);

        // Page 1: Fill form fields
        if ($currentPage === 1) {
            // Coordinates are in millimeters (mm) - FPDI default unit
            // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
            // Business Information
            $this->writeAt($pdf, 21.2, 46.8, $data->businessName);
            $this->writeAt($pdf, 21.2, 61.6, $data->businessStreetAddress);
            $this->writeAt($pdf, 87.0, 61.6, $data->businessCity);
            $this->writeAt($pdf, 87.0, 54.3, $data->businessDba);

            // Missouri Tax I.D. Number boxes (top right): only a Missouri
            // tax id belongs here; out-of-state ids go on the resale line below
            $business = $certificate->business_snapshot;
            $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
            $taxIdDigits = preg_replace('/\D+/', '', $data->businessTaxId ?? '');
            if ($taxIdSourceState === 'MO' && ! empty($taxIdDigits)) {
                $xPositions1 = [
                    147.5,  // 1st digit
                    155.5,  // 2nd digit
                    162.5,  // 3rd digit
                    169.5,  // 4th digit
                    176.6,  // 5th digit
                    183.5,  // 6th digit
                    190.4,  // 7th digit
                    197.3,  // 8th digit
                ];

                $digits = str_split($taxIdDigits);
                $digitCount = count($digits);

                // Place up to 8 digits at manual positions
                for ($i = 0; $i < min(8, $digitCount); $i++) {
                    $this->writeAt($pdf, $xPositions1[$i], 46.8, $digits[$i]);
                }
            }

            $this->writeAt($pdf, 145.8, 61.6, $data->businessState);
            $this->writeAt($pdf, 178.7, 61.6, $data->businessZip);
            $this->writeAt($pdf, 21.2, 76.4, $data->businessType);
            $this->writeAt($pdf, 133, 114.8, $data->businessTaxId);

            // Home state: the state that issued the tax id
            $this->writeAt($pdf, 183.9, 114.8, $taxIdSourceState);

            // Vendor Information
            $this->writeAt($pdf, 21.2, 85.1, $data->vendorName);
            $this->writeAt($pdf, 21.2, 92.3, $data->vendorContact);
            $this->writeAt($pdf, 21.2, 99.5, $data->vendorStreetAddress);
            $this->writeAt($pdf, 87.0, 99.5, $data->vendorCity);
            $this->writeAt($pdf, 145.8, 99.5, $data->vendorState);
            $this->writeAt($pdf, 178.7, 99.5, $data->vendorZip);

            // Vendor Phone Number Breakout (10 digits: XXX-XXX-XXXX)
            // Place each digit individually at specific x positions
            $vendorPhoneDigits = preg_replace('/\D+/', '', $data->vendorPhone ?? '');
            if (! empty($vendorPhoneDigits)) {

                $xPositionsVendor = [
                    146.1,  // 1st digit (area code)
                    151.3,  // 2nd digit
                    157,  // 3rd digit
                    162.8,  // 4th digit (prefix)
                    167.8,  // 5th digit
                    172.8,  // 6th digit
                    179.8,  // 7th digit (line number)
                    184.8,  // 8th digit
                    189.8,  // 9th digit
                    194.8,  // 10th digit
                ];

                $vendorDigits = str_split($vendorPhoneDigits);
                $vendorDigitCount = count($vendorDigits);

                // Place up to 10 digits at manual positions
                for ($i = 0; $i < min(10, $vendorDigitCount); $i++) {
                    $this->writeAt($pdf, $xPositionsVendor[$i], 85.1, $vendorDigits[$i]);
                }
            }

            // Certificate Details
            $this->writeAt($pdf, 21.2, 69.1, $data->productDescription);

            // Contact Information
            $this->writeAt($pdf, 21.2, 54.3, $data->signerName);

            // Telephone Number Breakout (10 digits: XXX-XXX-XXXX)
            // Place each digit individually at specific x positions
            $phoneDigits = preg_replace('/\D+/', '', $data->phone ?? '');
            if (! empty($phoneDigits)) {

                $xPositions = [
                    146.1,  // 1st digit (area code)
                    151.3,  // 2nd digit
                    157,  // 3rd digit
                    162.8,  // 4th digit (prefix)
                    167.8,  // 5th digit
                    172.8,  // 6th digit
                    179.8,  // 7th digit (line number)
                    184.8,  // 8th digit
                    189.8,  // 9th digit
                    194.8,  // 10th digit
                ];

                $digits = str_split($phoneDigits);
                $digitCount = count($digits);

                // Place up to 10 digits at manual positions
                for ($i = 0; $i < min(10, $digitCount); $i++) {
                    $this->writeAt($pdf, $xPositions[$i], 68.3, $digits[$i]);
                }
            }

            // Special Elements
            $this->writeAt($pdf, 22.3, 115.0, $data->checkmarkX);
        }

        // Page 2: Signature block (Signature, Title, Date MM/DD/YYYY)
        if ($currentPage === 2) {
            $this->writeAt($pdf, 101, 115.9, $data->signerTitle);

            $this->addSignatureWithHeight($pdf, $certificate, 22, 113.2, 4.8);

            // Date digits go one per underscore slot. Cell() instead of
            // Write() so nothing wraps.
            $date = ($certificate->issue_date ?? now())->format('mdY');
            $slots = [165.9, 170.2, 175.9, 180.2, 185.5, 190, 194.4, 198.8];

            foreach ($slots as $i => $x) {
                $pdf->SetXY($x, 115.7);
                $pdf->Cell(0, 0, $date[$i], 0, 0, 'L');
            }
        }
    }
}

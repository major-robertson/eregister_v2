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

            // Tax ID Breakout #1 (top right - Federal Tax ID or State Tax ID)
            // Place each digit individually at specific x positions
            $taxIdDigits = preg_replace('/\D+/', '', $data->businessTaxId ?? '');
            if (! empty($taxIdDigits)) {
                $xPositions1 = [
                    147.5,  // 1st digit
                    155,  // 2nd digit
                    161,  // 3rd digit
                    168.5,  // 4th digit
                    176,  // 5th digit
                    182,  // 6th digit
                    189,  // 7th digit
                    197,  // 8th digit
                ];

                $digits = str_split($taxIdDigits);
                $digitCount = count($digits);

                // Place up to 11 digits at manual positions
                for ($i = 0; $i < min(8, $digitCount); $i++) {
                    $this->writeAt($pdf, $xPositions1[$i], 46.8, $digits[$i]);
                }
            }

            $this->writeAt($pdf, 145.8, 61.6, $data->businessState);
            $this->writeAt($pdf, 178.7, 61.6, $data->businessZip);
            $this->writeAt($pdf, 21.2, 76.4, $data->businessType);
            $this->writeAt($pdf, 133, 114.8, $data->businessTaxId);

            // Get the tax ID source state instead of hardcoding "MO"
            $business = $certificate->business_snapshot;
            $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
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

        // Page 2: Fill form fields
        if ($currentPage === 2) {
            // Coordinates are in millimeters (mm) - FPDI default unit
            // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
            // Contact Information
            $this->writeAt($pdf, 100.0, 65.4, $data->signerTitle);

            // Special Elements
            $this->addSignatureWithHeight($pdf, $certificate, 20.0, 62.8, 5);

            // Date Fields - MM DD YYYY (to the right of owner/signature)
            // Using Cell() instead of Write() to prevent text wrapping
            $date = $certificate->created_at ?? now();
            $month = $date->format('m');
            $day = $date->format('d');
            $year = $date->format('Y');

            // Month (MM)
            $pdf->SetXY(165.5, 65.4);
            $pdf->Cell(0, 0, substr($month, 0, 1), 0, 0, 'L');
            $pdf->SetXY(169.5, 65.4);
            $pdf->Cell(0, 0, substr($month, 1, 1), 0, 0, 'L');

            // Day (DD)
            $pdf->SetXY(175, 65.4);
            $pdf->Cell(0, 0, substr($day, 0, 1), 0, 0, 'L');
            $pdf->SetXY(179, 65.4);
            $pdf->Cell(0, 0, substr($day, 1, 1), 0, 0, 'L');

            // Year (YYYY)
            $pdf->SetXY(184.5, 65.4);
            $pdf->Cell(0, 0, substr($year, 0, 1), 0, 0, 'L');
            $pdf->SetXY(189, 65.4);
            $pdf->Cell(0, 0, substr($year, 1, 1), 0, 0, 'L');
            $pdf->SetXY(193, 65.4);
            $pdf->Cell(0, 0, substr($year, 2, 1), 0, 0, 'L');
            $pdf->SetXY(197.5, 65.4);
            $pdf->Cell(0, 0, substr($year, 3, 1), 0, 0, 'L');

        }
    }
}

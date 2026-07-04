<?php

namespace App\Domains\ResaleCert\Pdf;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use setasign\Fpdi\Fpdi;

/**
 * Base class for states that generate custom PDFs from scratch
 * instead of using a template file
 */
abstract class BaseCustomCertificate extends BaseStateCertificate
{
    /**
     * Return null to indicate custom generation (no template file)
     */
    public function getTemplatePath(): string
    {
        return '';
    }

    /**
     * Check if this certificate uses custom generation
     */
    public function usesCustomGeneration(): bool
    {
        return true;
    }

    /**
     * Template-based field filling is not used for custom certificates
     * Override this to prevent usage
     */
    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // Not used for custom generation
    }

    /**
     * Generate a custom PDF certificate from scratch
     *
     * @param  Fpdi  $pdf  The PDF object
     * @param  ResaleCertificate  $certificate  The certificate data
     */
    public function generateCustomPdf(Fpdi $pdf, ResaleCertificate $certificate): void
    {
        // Add a new letter-sized page (8.5" x 11" = 215.9mm x 279.4mm)
        $pdf->AddPage('P', [215.9, 279.4]);

        // Set default font
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        // Extract certificate data
        $data = $this->extractCertificateData($certificate);

        // Draw the certificate content
        $this->drawCertificateContent($pdf, $data, $certificate);
    }

    /**
     * Draw the certificate content on the PDF
     * Can be overridden by child classes for state-specific formatting
     *
     * @param  object  $data  Certificate data
     */
    protected function drawCertificateContent(Fpdi $pdf, $data, ResaleCertificate $certificate): void
    {
        $leftMargin = 20;
        $rightMargin = 195;
        $y = 15;

        // Title
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 10, 'RESALE CERTIFICATE', 0, 1, 'C');
        $y += 10;

        // State subtitle
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY($leftMargin, $y);
        $stateName = $this->getStateName($certificate->state_code);
        $pdf->Cell(175, 8, 'For the State of '.$stateName, 0, 1, 'C');
        $y += 12;

        // Section I: Seller Information
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 8, 'Section I: Seller Information', 0, 1, 'L');
        $pdf->Line($leftMargin, $y + 8, $rightMargin, $y + 8);
        $y += 10;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(40, 6, 'Name:', 0, 0, 'L');
        $pdf->Cell(135, 6, $data->vendorName, 0, 1, 'L');
        $y += 6;

        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(40, 6, 'Business address:', 0, 0, 'L');
        $pdf->MultiCell(135, 6, $data->vendorFullAddress, 0, 'L');
        $y += 10;

        // Section II: Purchaser Information
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 8, 'Section II: Purchaser Information', 0, 1, 'L');
        $pdf->Line($leftMargin, $y + 8, $rightMargin, $y + 8);
        $y += 10;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(40, 6, 'Name:', 0, 0, 'L');
        $pdf->Cell(135, 6, $data->businessName, 0, 1, 'L');
        $y += 6;

        if ($data->businessDba) {
            $pdf->SetXY($leftMargin, $y);
            $pdf->Cell(40, 6, 'DBA:', 0, 0, 'L');
            $pdf->Cell(135, 6, $data->businessDba, 0, 1, 'L');
            $y += 6;
        }

        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(40, 6, 'Business address:', 0, 0, 'L');
        $pdf->MultiCell(135, 6, $data->businessFullAddress, 0, 'L');
        $y += 10;

        // Section III: Purchaser Status
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 8, 'Section III: Purchaser Status', 0, 1, 'L');
        $pdf->Line($leftMargin, $y + 8, $rightMargin, $y + 8);
        $y += 10;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(5, 6, 'X', 0, 0, 'L'); // Checkbox symbol
        $pdf->Cell(170, 6, 'The purchaser is registered with the state tax authority.', 0, 1, 'L');
        $y += 6;

        // Get the tax ID source state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;

        $pdf->SetXY($leftMargin + 5, $y);
        $pdf->Cell(40, 6, 'State:', 0, 0, 'L');
        $pdf->Cell(130, 6, $taxIdSourceState, 0, 1, 'L');
        $y += 6;

        $pdf->SetXY($leftMargin + 5, $y);
        $pdf->Cell(40, 6, 'Tax ID:', 0, 0, 'L');
        $pdf->Cell(130, 6, $data->businessTaxId, 0, 1, 'L');
        $y += 8;

        // Section IV: Property Description
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 8, 'Section IV: Property Description', 0, 1, 'L');
        $pdf->Line($leftMargin, $y + 8, $rightMargin, $y + 8);
        $y += 10;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY($leftMargin, $y);
        $pdf->SetFillColor(249, 249, 249);
        $pdf->Rect($leftMargin, $y, 175, 16, 'FD');
        $pdf->SetXY($leftMargin + 2, $y + 2);
        $pdf->MultiCell(171, 4, $data->productDescription, 0, 'L');
        $y += 18;

        // Section V: Purchaser Certification
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(175, 8, 'Section V: Purchaser Certification', 0, 1, 'L');
        $pdf->Line($leftMargin, $y + 8, $rightMargin, $y + 8);
        $y += 10;

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(5, 6, 'X', 0, 0, 'L'); // Checkbox symbol
        $pdf->Cell(170, 6, 'I certify that all purchases from this seller are for resale.', 0, 1, 'L');
        $y += 10;

        // Signature block
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(50, 6, 'Signature:', 0, 0, 'L');

        // Add signature if available
        $this->addSignatureWithHeight($pdf, $certificate, $leftMargin + 50, $y - 2, 8);
        $pdf->Line($leftMargin + 50, $y + 5, $leftMargin + 130, $y + 5);
        $y += 10;

        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(50, 6, 'Printed Name:', 0, 0, 'L');
        $pdf->Cell(80, 6, $data->signerName, 0, 1, 'L');
        $y += 6;

        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(50, 6, 'Title:', 0, 0, 'L');
        $pdf->Cell(80, 6, $data->signerTitle, 0, 1, 'L');
        $y += 6;

        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(50, 6, 'Date:', 0, 0, 'L');
        $pdf->Cell(80, 6, $data->issueDate, 0, 1, 'L');
        $y += 10;

        // Important Information section
        $pdf->SetDrawColor(187, 187, 187);
        $pdf->Line($leftMargin, $y, $rightMargin, $y);
        $y += 5;

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(5, 5, '*', 0, 0, 'L');
        $pdf->SetXY($leftMargin + 5, $y);
        $pdf->MultiCell(170, 5, 'The seller is required to verify that the purchaser\'s tax registration is valid and active. This certificate should be retained by the seller and not sent to the Department of Revenue.', 0, 'L');

        // Get updated Y position after MultiCell
        $y = $pdf->GetY();

        // Add state-specific disclaimer if needed
        $this->addStateSpecificDisclaimer($pdf, $certificate, $data, $leftMargin, $rightMargin, $y);
    }

    /**
     * Add state-specific disclaimer text (can be overridden by child classes)
     *
     * @param  ResaleCertificate  $certificate
     * @param  object  $data
     * @param  float  $leftMargin
     * @param  float  $rightMargin
     * @param  float  $y  Current Y position
     * @return float Updated Y position
     */
    protected function addStateSpecificDisclaimer(Fpdi $pdf, $certificate, $data, $leftMargin, $rightMargin, $y): float
    {
        // Default: no additional disclaimer
        return $y;
    }

    /**
     * Get the full state name from state code
     */
    protected function getStateName(string $stateCode): string
    {
        $states = [
            'AL' => 'Alabama',
            'LA' => 'Louisiana',
            'OK' => 'Oklahoma',
        ];

        return $states[$stateCode] ?? $stateCode;
    }
}

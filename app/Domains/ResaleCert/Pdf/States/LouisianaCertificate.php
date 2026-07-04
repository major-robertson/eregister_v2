<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Pdf\BaseCustomCertificate;
use setasign\Fpdi\Fpdi;

class LouisianaCertificate extends BaseCustomCertificate
{
    /**
     * Note: Louisiana uses custom PDF generation (BaseCustomCertificate), so out-of-state
     * tax ID support is handled automatically in the base class rather than through
     * coordinate-based positioning. The tax ID from business_snapshot is used directly.
     */

    /**
     * Add Louisiana-specific disclaimer after notice
     */
    protected function addStateSpecificDisclaimer(Fpdi $pdf, $certificate, $data, $leftMargin, $rightMargin, $y): float
    {
        $y += 2;

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(5, 5, '**', 0, 0, 'L');
        $pdf->SetXY($leftMargin + 5, $y);
        $pdf->MultiCell(170, 5,
            'The purchaser certifies that the property being purchased is for resale. The purchaser does not have physical presence or economic nexus in Louisiana and therefore is not required to register as a dealer under Louisiana law.',
            0, 'L');

        return $y;
    }
}

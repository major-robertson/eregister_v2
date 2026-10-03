<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

/**
 * North Dakota Certificate of Resale, SFN 21950 (3-2026).
 */
class NorthDakotaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/north_dakota.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // "I hold ____ (Enter State)": the state that issued the buyer's
        // permit, so out-of-state buyers write their own state here
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? $data->businessState;
        $this->writeAt($pdf, 74, 38.6, $taxIdSourceState);

        // "Sales and Use Tax permit number ____"
        $this->writeAt($pdf, 162, 38.6, $data->businessTaxId);

        $pdf->SetFont('Helvetica', '', 9);

        // "business of selling, leasing, or renting ____ (Enter Property Type)"
        $this->writeAt($pdf, 124, 50, $data->productDescription);

        // "purchased from ____ (Enter Name of Seller)"
        $this->writeAt($pdf, 139, 59.4, $data->vendorName);

        // Business Name and Business Address lines
        $this->writeAt($pdf, 16, 106.3, $data->businessName);
        $this->writeAt($pdf, 111, 106.3, $data->businessFullAddress);

        $pdf->SetFont('Helvetica', '', 10);

        // Authorized Signature and Date lines
        $this->addSignatureWithHeight($pdf, $certificate, 16, 124.2, 8);
        $this->writeAt($pdf, 111, 128.2, $data->issueDate);
    }
}

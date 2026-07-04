<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class ConnecticutCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID and state (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 70, 'y' => 177];

    protected $stateCoordinates = ['x' => 30, 'y' => 177];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/connecticut.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        $this->writeAt($pdf, 30, 75, $data->vendorName);
        $this->writeAt($pdf, 112, 75, $data->vendorStreetAddress).' '.' '.$data->vendorCity.' '.$data->vendorState.' '.$data->vendorZip;

        $this->writeAt($pdf, 50, 93, $data->businessName);

        $this->writeAt($pdf, 50, 108, $data->businessStreetAddress);

        $this->writeAt($pdf, 50, 123, $data->businessCity);
        $this->writeAt($pdf, 86, 123, $data->businessState);
        $this->writeAt($pdf, 116, 123, $data->businessZip);

        // Mark the chosen box
        $this->writeAt($pdf, 158, 98, $data->checkmarkX);

        // ---------------------------------------------------------------------
        // "We are in the business of … leasing (renting) the following:" line(s)
        // Use productDescription as the freeform description here.
        // ---------------------------------------------------------------------
        $this->writeAt($pdf, 30, 153, $data->productDescription);

        // -------------------------------------------------------------------------
        // Registration lines ("City or state" + "State Registration or I.D. No.")
        // -------------------------------------------------------------------------
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;

        $this->writeAt($pdf, $this->stateCoordinates['x'], $this->stateCoordinates['y'], $taxIdSourceState ?? 'CT');
        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);

        // If you have additional registrations you want to show, add them here…
        // Example:
        // $this->writeAt($pdf, 120, 160.0, 'NY');
        // $this->writeAt($pdf, 178, 160.0, 'NY-1234567');

        // -------------------------------------------------------------------------
        // General description of products to be purchased from the seller (bottom)
        // -------------------------------------------------------------------------
        $this->writeAt($pdf, 30, 246, $data->productDescription);

        // --------------------------------
        // Signature block / Title / Date
        // --------------------------------
        $pdf->SetAutoPageBreak(false);

        // Signature image sits on the "Authorized Signature" line
        $this->addSignatureWithHeight($pdf, $certificate, 65, 258, 8);

        // Title (right of signature)
        $this->writeAt($pdf, 132, 263, $data->signerTitle);

        // Date (farther right)
        $this->writeAt($pdf, 158, 263, $data->issueDate);

        $pdf->SetAutoPageBreak(true);
    }
}

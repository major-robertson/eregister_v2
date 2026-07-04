<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class IllinoisCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 145, 'y' => 114];

    /**
     * Coordinates for in-state checkmark
     */
    protected $inStateCheckmarkCoordinates = ['x' => 10.0, 'y' => 113.5];

    /**
     * Coordinates for out-of-state checkmark and state/tax ID
     */
    protected $outOfStateCheckmarkCoordinates = ['x' => 10.0, 'y' => 121.5];

    protected $outOfStateStateTaxIdCoordinates = ['x' => 122.3, 'y' => 129.8];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/illinois.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // -----------------------------
        // Step 1: Identify the seller
        // -----------------------------
        $this->writeAt($pdf, 26, 50, $data->vendorName);
        $this->writeAt($pdf, 26, 58, $data->vendorStreetAddress);
        $this->writeAt($pdf, 26, 67, $data->vendorCity);
        $this->writeAt($pdf, 132, 67, $data->vendorState);
        $this->writeAt($pdf, 167, 67, $data->vendorZip);

        // -----------------------------
        // Step 2: Identify the purchaser
        // -----------------------------
        $this->writeAt($pdf, 26, 82, $data->businessName);
        $this->writeAt($pdf, 26, 91, $data->businessStreetAddress);
        $this->writeAt($pdf, 26, 99, $data->businessCity);
        $this->writeAt($pdf, 130, 99, $data->businessState);
        $this->writeAt($pdf, 167, 99, $data->businessZip);

        // Checkbox X and Account ID - different handling for in-state vs out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'IL');

        if ($isInState) {
            // In-state: upper checkmark and tax ID at normal position
            $this->writeAt($pdf, $this->inStateCheckmarkCoordinates['x'], $this->inStateCheckmarkCoordinates['y'], $data->checkmarkX);
            $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);
        } else {
            // Out-of-state: lower checkmark and "STATE, TAXID" on the same line
            $this->writeAt($pdf, $this->outOfStateCheckmarkCoordinates['x'], $this->outOfStateCheckmarkCoordinates['y'], $data->checkmarkX);
            $this->writeAt($pdf, $this->outOfStateStateTaxIdCoordinates['x'], $this->outOfStateStateTaxIdCoordinates['y'], $taxIdSourceState.', '.$data->businessTaxId);
        }

        // -----------------------------
        // Step 3: Describe the property
        // -----------------------------
        // Line 1: product description
        $this->writeAt($pdf, 12, 158, $data->productDescription);

        // Step 4: Blanket certificate checkbox
        $this->writeAt($pdf, 10, 193.5, $data->checkmarkX);

        // -----------------------------------------------
        // Step 6: Purchaser's signature / contact / date
        // -----------------------------------------------
        $pdf->SetAutoPageBreak(false);

        // Signature
        $this->addSignatureWithHeight($pdf, $certificate, 10, 249, 7);

        // Contact information
        $this->writeAt($pdf, 110, 253, $data->email);
        $this->writeAt($pdf, 184, 253, $data->issueDate);
        $this->writeAt($pdf, 10, 262, $data->signerName);
        $this->writeAt($pdf, 110, 262, $data->phone);

        $pdf->SetAutoPageBreak(true);
    }
}

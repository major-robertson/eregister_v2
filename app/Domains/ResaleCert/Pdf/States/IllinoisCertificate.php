<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

/**
 * Illinois CRT-61 Certificate of Resale, R-04/25.
 */
class IllinoisCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for the IDOR Account ID number (in-state)
     */
    protected $taxIdCoordinates = ['x' => 145, 'y' => 112.6];

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
        $this->writeAt($pdf, 26, 49.5, $data->vendorName);
        $this->writeAt($pdf, 26, 57.7, $data->vendorStreetAddress);
        $this->writeAt($pdf, 26, 66.3, $data->vendorCity);
        $this->writeAt($pdf, 132, 66.3, $data->vendorState);
        $this->writeAt($pdf, 167, 66.3, $data->vendorZip);

        // -----------------------------
        // Step 2: Identify the purchaser
        // -----------------------------
        $this->writeAt($pdf, 26, 81.5, $data->businessName);
        $this->writeAt($pdf, 26, 90.2, $data->businessStreetAddress);
        $this->writeAt($pdf, 26, 98.3, $data->businessCity);
        $this->writeAt($pdf, 131, 98.3, $data->businessState);
        $this->writeAt($pdf, 167, 98.3, $data->businessZip);

        // Checkbox X and Account ID - different handling for in-state vs out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'IL');

        if ($isInState) {
            // In-state: upper checkmark and the Account ID number
            $this->writeAt($pdf, $this->inStateCheckmarkCoordinates['x'], $this->inStateCheckmarkCoordinates['y'], $data->checkmarkX);
            $this->writeAccountId($pdf, $data->businessTaxId);
        } else {
            // Out-of-state: lower checkmark and "STATE, TAXID" on the same line
            $this->writeAt($pdf, $this->outOfStateCheckmarkCoordinates['x'], $this->outOfStateCheckmarkCoordinates['y'], $data->checkmarkX);
            $this->writeAt($pdf, $this->outOfStateStateTaxIdCoordinates['x'], $this->outOfStateStateTaxIdCoordinates['y'], $taxIdSourceState.', '.$data->businessTaxId);
        }

        // -----------------------------
        // Step 3: Describe the property
        // -----------------------------
        // Line 1: product description
        $this->writeAt($pdf, 12, 157.5, $data->productDescription);

        // Step 4: full blanket certificate checkbox (Step 5, the percentage
        // blanket, is left blank)
        $this->writeAt($pdf, 10, 193.5, $data->checkmarkX);

        // -----------------------------------------------
        // Step 6: Purchaser's signature / contact / date
        // -----------------------------------------------
        $pdf->SetAutoPageBreak(false);

        // Signature
        $this->addSignatureWithHeight($pdf, $certificate, 10, 249, 7);

        // Contact information
        $this->writeAt($pdf, 110, 252.6, $data->email);
        $this->writeAt($pdf, 184.5, 252.2, $data->issueDate);
        $this->writeAt($pdf, 10, 261.6, $data->signerName);
        $this->writeAt($pdf, 110, 261.6, $data->phone);

        $pdf->SetAutoPageBreak(true);
    }

    /**
     * The form prints the Account ID as "_ _ _ _ - _ _ _ _". An 8-digit
     * IDOR Account ID goes either side of the dash; anything else is
     * written as entered.
     */
    protected function writeAccountId(Fpdi $pdf, string $taxId): void
    {
        $digits = preg_replace('/\D+/', '', $taxId);

        if (strlen($digits) === 8) {
            $this->writeAt($pdf, 146.5, $this->taxIdCoordinates['y'], substr($digits, 0, 4));
            $this->writeAt($pdf, 168, $this->taxIdCoordinates['y'], substr($digits, 4));

            return;
        }

        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $taxId);
    }
}

<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class ArkansasCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for in-state (AR) tax ID
     */
    protected $inStateTaxIdCoordinates = ['x' => 102, 'y' => 57];

    /**
     * Coordinates for out-of-state tax ID (the "Number" line after
     * "similar permit issued by the State of")
     */
    protected $outOfStateTaxIdCoordinates = ['x' => 126.5, 'y' => 66.6];

    /**
     * Coordinates for state abbreviation (out-of-state only)
     */
    protected $stateAbbreviationCoordinates = ['x' => 67.0, 'y' => 66.6];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/arkansas.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // -----------------------------------------
        // Permit number and seller (vendor) details
        // -----------------------------------------
        // Arkansas Sales/Use Tax Permit Number - use appropriate coordinates
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'AR');
        $taxIdCoords = $isInState ? $this->inStateTaxIdCoordinates : $this->outOfStateTaxIdCoordinates;
        $this->writeAt($pdf, $taxIdCoords['x'], $taxIdCoords['y'], $data->businessTaxId);

        // For out-of-state tax IDs, also write the state abbreviation
        if (! $isInState && $taxIdSourceState) {
            $this->writeAt($pdf, $this->stateAbbreviationCoordinates['x'], $this->stateAbbreviationCoordinates['y'], $taxIdSourceState);
        }

        // "purchased from" (seller/vendor name)
        $this->writeAt($pdf, 106, 76, $data->vendorName);

        // -------------------------------------------------
        // Merchandise description & exemption (blanket resale)
        // -------------------------------------------------
        // Description of the merchandise to be purchased
        $this->writeAt($pdf, 25, 129.4, $data->productDescription);

        // Reason merchandise is exempt — treat as blanket resale
        $this->writeAt($pdf, 25, 148.4, $data->businessType);

        // Purchaser's business activity
        $this->writeAt($pdf, 82, 168.5, $data->businessType);

        // ------------------------------------
        // Purchaser name and signature section
        // ------------------------------------
        // Purchaser's Business Name (as stated on permit)
        $this->writeAt($pdf, 25, 187.4, $data->businessName);

        // Signature (right side)
        $this->addSignatureWithHeight($pdf, $certificate, 128, 182, 9);

        // Address (street) and Title/Position with Company
        $this->writeAt($pdf, 25, 202.4, $data->businessStreetAddress);
        $this->writeAt($pdf, 96, 202.4, $data->signerTitle);

        // City, State, Zip (left) and Date (right)
        $this->writeAt($pdf, 25, 217.4, $data->businessCityStateZip);
        $this->writeAt($pdf, 96, 217.4, $data->issueDate);
    }
}

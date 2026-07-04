<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class ColoradoCertificate extends BaseStateCertificate
{
    /**
     * Coordinates for tax ID and state (same for both in-state and out-of-state)
     */
    protected $taxIdCoordinates = ['x' => 148, 'y' => 49];

    protected $stateCoordinates = ['x' => 148, 'y' => 58];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/colorado.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // CO form asks for Date (MM/DD/YY)
        $issueDateCO = $certificate->issue_date
            ? $certificate->issue_date->format('m/d/y')
            : $data->issueDate;

        // -----------------------------
        // 1) Purchaser Information (left)
        // -----------------------------
        // Legal Name
        $this->writeAt($pdf, 15, 50, $data->businessName);
        // Trade Name (if different)
        $this->writeAt($pdf, 15, 58, $data->businessDba);
        // Mailing Address
        $this->writeAt($pdf, 15, 67, $data->businessStreetAddress);
        // City / State / ZIP
        $this->writeAt($pdf, 15, 75, $data->businessCity);
        $this->writeAt($pdf, 148, 75, $data->businessState);
        $this->writeAt($pdf, 160, 75, $data->businessZip);
        // Phone Number (right of Mailing Address row)
        $this->writeAt($pdf, 148, 67, $data->phone);

        // ---------------------------------------------
        // License or Exemption Information (right pane)
        // ---------------------------------------------
        // Sales Tax License or Exemption Number
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;

        $this->writeAt($pdf, $this->taxIdCoordinates['x'], $this->taxIdCoordinates['y'], $data->businessTaxId);
        // State (license issuing state) - write the source state, not the certificate state
        $this->writeAt($pdf, $this->stateCoordinates['x'], $this->stateCoordinates['y'], $taxIdSourceState ?? $data->businessState);
        // Expiration Date — leave blank if unknown

        // ----------------------------------------------------
        // 2) Wholesale Exemption (default: Purchase for Resale)
        // ----------------------------------------------------
        // Checkbox: "Purchase for Resale"
        $this->writeAt($pdf, 15, 87, $data->checkmarkX);

        // Ordinary Course of Business, incl. products manufactured and/or sold
        $this->writeAt($pdf, 100, 92, $data->productDescription ?: 'General Merchandise');

        // If you ever support manufacturing sub‑types, add additional X's here.
        // Left intentionally blank for resale-only use.

        // ---------------------------------------------
        // 3) Entity Exemption (a & b) — not used here
        // ---------------------------------------------
        // Leave all checkboxes/fields blank for a resale certificate.

        // ---------------------------------------------
        // 4) Other Exemption — not used for resale
        // ---------------------------------------------

        // -----------------------------
        // 5) Purchaser Signature block
        // -----------------------------
        // Printed Name / Title (upper line of the signature block)
        $this->writeAt($pdf, 15, 256, $data->signerName);
        $this->writeAt($pdf, 130, 256, $data->signerTitle);

        $pdf->SetAutoPageBreak(false);

        // Signature (lower line, left)
        $this->addSignatureWithHeight($pdf, $certificate, 15, 262, 6);

        // Date (MM/DD/YY) — lower line, right
        $this->writeAt($pdf, 165, 265, $issueDateCO);

        $pdf->SetAutoPageBreak(true);
    }
}

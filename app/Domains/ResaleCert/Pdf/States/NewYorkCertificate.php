<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class NewYorkCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/new_york.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Buyer (Business) Information
        $this->writeAt($pdf, 110, 33.5, $data->businessName);
        $this->writeAt($pdf, 110, 42.5, $data->businessStreetAddress);
        $this->writeAt($pdf, 110, 50.5, $data->businessCity);
        $this->writeAt($pdf, 164, 50.5, $data->businessState);
        $this->writeAt($pdf, 185, 50.5, $data->businessZip);
        $this->writeAt($pdf, 101.5, 57.5, $data->checkmarkX);

        // Seller (Vendor) Information
        $this->writeAt($pdf, 10, 34, $data->vendorName);
        $this->writeAt($pdf, 10, 42, $data->vendorStreetAddress);
        $this->writeAt($pdf, 10, 50, $data->vendorCity);
        $this->writeAt($pdf, 67, 50, $data->vendorState);
        $this->writeAt($pdf, 85, 50, $data->vendorZip);

        // Checkboxes and Tax ID - different for in-state vs out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'NY');

        if ($isInState) {
            // Part 1: "a New York State vendor", the sales tax id, and purchasing box A
            $this->writeAt($pdf, 8.5, 117, $data->checkmarkX);
            $this->writeAt($pdf, 107, 121.3, $data->businessTaxId);
            $this->writeAt($pdf, 8.5, 138, $data->checkmarkX);
        } else {
            // Part 2: purchasing box D (delivered to a customer or fulfillment provider in NY),
            // then the registration state and number
            $this->writeAt($pdf, 8.5, 206.5, $data->checkmarkX);
            $this->writeAt($pdf, 96.2, 182.7, $taxIdSourceState);
            $this->writeAt($pdf, 74.8, 187.0, $data->businessTaxId);
        }

        // Business type and product description
        $this->writeAt($pdf, 54, 96, $data->businessType);
        // "and principally sell" line is about 60mm wide: 8pt keeps the sample on it
        $pdf->SetFont('Helvetica', '', 8);
        $this->writeAt($pdf, 145.5, 96.3, $data->productDescription);
        $pdf->SetFont('Helvetica', '', 10);

        // Signer information and date
        $this->writeAt($pdf, 10, 252.75, $data->signerNameAndTitle);

        $pdf->SetAutoPageBreak(false);
        $this->writeAt($pdf, 160, 261.5, $data->issueDate);
        $pdf->SetAutoPageBreak(true);

        // Add signature
        $this->addSignatureWithHeight($pdf, $certificate, 10, 258, 5);
    }
}

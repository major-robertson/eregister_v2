<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class SouthCarolinaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/south_carolina.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 42.3, 149.3, $data->businessType);
        $this->writeAt($pdf, 12.9, 164.9, $data->businessName);
        $this->writeAt($pdf, 110.5, 164.9, $data->businessStreetAddress);

        // Tax ID - same location for both in-state and out-of-state, but add state prefix for out-of-state
        $business = $certificate->business_snapshot;
        $taxIdSourceState = $business['tax_id_source_state'] ?? null;
        $isInState = ($taxIdSourceState === 'SC');

        $taxIdDisplay = $isInState
            ? $data->businessTaxId
            : $taxIdSourceState.', '.$data->businessTaxId;

        $this->writeAt($pdf, 12.9, 177.1, $taxIdDisplay);
        $this->writeAt($pdf, 110.5, 177.1, $data->businessCity);
        $this->writeAt($pdf, 147.0, 177.1, $data->businessState);
        $this->writeAt($pdf, 178.7, 177.1, $data->businessZip);

        // Vendor Information
        $this->writeAt($pdf, 12.9, 118.8, $data->vendorName);
        $this->writeAt($pdf, 12.9, 127.5, $data->vendorStreetAddress);
        $this->writeAt($pdf, 88.2, 127.5, $data->vendorCity);
        $this->writeAt($pdf, 138.8, 127.5, $data->vendorState);
        $this->writeAt($pdf, 190.5, 127.5, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 87.0, 157.6, $data->productDescription);
        $this->writeAt($pdf, 12.9, 240.1, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 12.9, 231.7, $data->signerName);
        $this->writeAt($pdf, 111.7, 240.1, $data->signerTitle);

        // Special Elements
        $this->addSignatureWithHeight($pdf, $certificate, 111.7, 227.4, 6);
    }
}

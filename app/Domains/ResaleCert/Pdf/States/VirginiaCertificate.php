<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

/**
 * Virginia Form ST-10, Sales and Use Tax Certificate of Exemption, Rev. 05/26.
 */
class VirginiaCertificate extends BaseStateCertificate
{
    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/virginia.pdf';
    }

    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void
    {
        // For single-page certificates, only fill on page 1
        if ($currentPage !== 1) {
            return;
        }

        $data = $this->extractCertificateData($certificate);

        // Supplier information ("To:" and "Date:", then the address line)
        $this->writeAt($pdf, 18.5, 57.4, $data->vendorName);
        $this->writeAt($pdf, 150.5, 57.4, $data->issueDate);
        $this->writeAt($pdf, 12.5, 68.2, $data->vendorStreetAddress);
        $this->writeAt($pdf, 97, 68.2, $data->vendorCity);
        $this->writeAt($pdf, 162.5, 68.2, $data->vendorState);
        $this->writeAt($pdf, 185.5, 68.2, $data->vendorZip);

        // Box 1: tangible personal property for resale
        $this->writeAt($pdf, 12.85, 135.1, $data->checkmarkX);

        // Name of Dealer and Virginia Account No. The form has no field for
        // the state, so an out-of-state number carries its state ("CT 123").
        $this->writeAt($pdf, 39, 178.3, $data->businessName);
        $this->writeAt($pdf, 137.5, 178.3, $this->labelledTaxId($certificate, 'VA'));

        // Trading as
        $this->writeAt($pdf, 31, 185.8, $data->businessDba);

        // Business address
        $this->writeAt($pdf, 28.5, 192.9, $data->businessStreetAddress);
        $this->writeAt($pdf, 102.5, 192.9, $data->businessCity);
        $this->writeAt($pdf, 159.5, 192.9, $data->businessState);
        $this->writeAt($pdf, 184, 192.9, $data->businessZip);

        // Kind of business engaged in by dealer
        $this->writeAt($pdf, 75, 208.7, $data->businessType);

        // "By ____ Signature" and Title
        $this->addSignatureWithHeight($pdf, $certificate, 20, 224.8, 8);
        $this->writeAt($pdf, 126, 229.1, $data->signerTitle);
    }
}

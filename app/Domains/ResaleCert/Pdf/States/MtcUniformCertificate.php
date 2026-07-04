<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class MtcUniformCertificate extends BaseStateCertificate
{
    /**
     * State-specific coordinates for Tax ID / Permit Number field
     * Format: 'STATE_CODE' => ['x' => X_COORDINATE, 'y' => Y_COORDINATE]
     * Coordinates are in millimeters (mm) - FPDI default unit
     *
     * TODO: Update these placeholder coordinates with actual positions using grid overlay
     */
    protected $taxIdCoordinates = [
        'AK' => ['x' => 39, 'y' => 133.0],  // Alaska
        'AL' => ['x' => 39, 'y' => 137.5],  // Alabama
        'AR' => ['x' => 39, 'y' => 141.7],  // Arkansas
        'AZ' => ['x' => 39, 'y' => 145.8],  // Arizona
        'CA' => ['x' => 39, 'y' => 149.7],  // California
        'CO' => ['x' => 39, 'y' => 153.6],  // Colorado
        'CT' => ['x' => 39, 'y' => 157.5],  // Connecticut
        'FL' => ['x' => 39, 'y' => 161.1],  // Florida
        'GA' => ['x' => 39, 'y' => 164.8],  // Georgia
        'HI' => ['x' => 39, 'y' => 168.7],  // Hawaii
        'ID' => ['x' => 39, 'y' => 172.4],  // Idaho
        'IL' => ['x' => 39, 'y' => 176.2],  // Illinois
        'IA' => ['x' => 39, 'y' => 180.2],  // Iowa
        'KS' => ['x' => 39, 'y' => 183.7],  // Kansas
        'KY' => ['x' => 39, 'y' => 187.5],  // Kentucky
        'ME' => ['x' => 39, 'y' => 191.3],  // Maine
        'MD' => ['x' => 39, 'y' => 195.2],  // Maryland
        'MI' => ['x' => 39, 'y' => 199.1],  // Michigan
        'MN' => ['x' => 39, 'y' => 203.0],  // Minnesota

        /* 2nd Row */
        'MO' => ['x' => 121, 'y' => 133.0],  // Missouri
        'NE' => ['x' => 121, 'y' => 137.5],  // Nebraska
        'NV' => ['x' => 121, 'y' => 141.7],  // Nevada
        'NJ' => ['x' => 121, 'y' => 145.8],  // New Jersey
        'NM' => ['x' => 121, 'y' => 149.7],  // New Mexico
        'NC' => ['x' => 121, 'y' => 153.6],  // North Carolina
        'ND' => ['x' => 121, 'y' => 157.5],  // North Dakota
        'OH' => ['x' => 121, 'y' => 161.1],  // Ohio
        'OK' => ['x' => 121, 'y' => 164.8],  // Oklahoma
        'PA' => ['x' => 121, 'y' => 168.7],  // Pennsylvania
        'RI' => ['x' => 121, 'y' => 172.4],  // Rhode Island
        'SC' => ['x' => 121, 'y' => 176.2],  // South Carolina
        'SD' => ['x' => 121, 'y' => 180.2],  // South Dakota
        'TN' => ['x' => 121, 'y' => 183.7],  // Tennessee
        'TX' => ['x' => 121, 'y' => 187.5],  // Texas
        'UT' => ['x' => 121, 'y' => 191.3],  // Utah
        'VT' => ['x' => 121, 'y' => 195.2],  // Vermont
        'WA' => ['x' => 121, 'y' => 199.1],  // Washington
        'WI' => ['x' => 121, 'y' => 203.0],  // Wisconsin
    ];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/mtc.pdf';
    }

    /**
     * Get Tax ID coordinates for a specific state
     *
     * @return array|null Returns ['x' => float, 'y' => float] or null if not found
     */
    protected function getTaxIdCoordinates(string $stateCode): ?array
    {
        return $this->taxIdCoordinates[$stateCode] ?? null;
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
        $this->writeAt($pdf, 45.9, 64.0, $data->businessName);
        $this->writeAt($pdf, 44.7, 105.1, $data->businessType);
        $this->writeAt($pdf, 14.1, 72.0, $data->businessAddress1);

        // If no address line 2, put city/state/zip on line 2 instead of line 3
        if (empty($data->businessAddress2)) {
            $this->writeAt($pdf, 14.1, 76.0, $data->businessCityStateZip);
        } else {
            $this->writeAt($pdf, 14.1, 76.0, $data->businessAddress2);
            $this->writeAt($pdf, 14.1, 80.4, $data->businessCityStateZip);
        }

        // Tax IDs / Permit Numbers (state-specific coordinates)
        // For MTC uniform certificates, we may have multiple tax IDs for different states
        $business = $certificate->business_snapshot;
        $selectedStatesTaxIds = $business['selected_states_tax_ids'] ?? [];

        if (! empty($selectedStatesTaxIds)) {
            // Write tax IDs for each selected state covered by this MTC certificate
            // Format: SOURCE_STATE + TAX_ID (e.g., "TX 12345678")
            foreach ($selectedStatesTaxIds as $stateCode => $taxIdInfo) {
                $taxIdCoords = $this->getTaxIdCoordinates($stateCode);
                if ($taxIdCoords && ! empty($taxIdInfo['tax_id'])) {
                    // Use source state (where the tax ID is from) instead of covered state
                    $sourceState = $taxIdInfo['source_state'] ?? $stateCode;
                    $formattedTaxId = $sourceState.', '.$taxIdInfo['tax_id'];
                    $this->writeAt($pdf, $taxIdCoords['x'], $taxIdCoords['y'], $formattedTaxId);
                }
            }
        }

        // Vendor Information
        $this->writeAt($pdf, 35.3, 43.5, $data->vendorName);
        $this->writeAt($pdf, 25.2, 51.7, $data->vendorFullAddress);

        // Certificate Details
        $this->writeAt($pdf, 14.1, 118.1, $data->productDescription);
        $this->writeAt($pdf, 70.6, 256.8, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 70.6, 252.8, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 120.2, 67.7, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 85.8, 235.7, 8);
    }
}

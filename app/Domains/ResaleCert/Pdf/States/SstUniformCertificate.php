<?php

namespace App\Domains\ResaleCert\Pdf\States;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\BaseStateCertificate;
use setasign\Fpdi\Fpdi;

class SstUniformCertificate extends BaseStateCertificate
{
    /**
     * State-specific coordinates for Tax ID / Permit Number field
     * Format: 'STATE_CODE' => ['x' => X_COORDINATE, 'y' => Y_COORDINATE]
     * Coordinates are in millimeters (mm) - FPDI default unit
     *
     * TODO: Update these placeholder coordinates with actual positions using grid overlay
     */
    protected $taxIdCoordinates = [
        'AR' => ['x' => 17.0, 'y' => 187.0],  // Arkansas
        'GA' => ['x' => 17.0, 'y' => 193.2],  // Georgia
        'IA' => ['x' => 17.0, 'y' => 198.5],  // Iowa
        'IN' => ['x' => 17.0, 'y' => 204.4],  // Indiana
        'KS' => ['x' => 17.0, 'y' => 210.7],  // Kansas
        'KY' => ['x' => 17.0, 'y' => 216.8],  // Kentucky
        'MI' => ['x' => 17.0, 'y' => 222.7],  // Michigan
        'MN' => ['x' => 17.0, 'y' => 228.7],  // Minnesota
        'NC' => ['x' => 17.0, 'y' => 234.0],   // North Carolina
        'ND' => ['x' => 17.0, 'y' => 240],  // North Dakota
        'NE' => ['x' => 17.0, 'y' => 245.2],  // Nebraska
        'NJ' => ['x' => 17.0, 'y' => 250.3],  // New Jersey
        'NV' => ['x' => 116.5, 'y' => 187.0],  // Nevada
        'OH' => ['x' => 116.5, 'y' => 192.8],  // Ohio
        'OK' => ['x' => 116.5, 'y' => 199.0],  // Oklahoma
        'RI' => ['x' => 116.5, 'y' => 204.5],  // Rhode Island
        'SD' => ['x' => 116.5, 'y' => 210.8],  // South Dakota
        'TN' => ['x' => 116.5, 'y' => 216.5],  // Tennessee
        'UT' => ['x' => 116.5, 'y' => 222.7],  // Utah
        'VT' => ['x' => 116.5, 'y' => 228.1],  // Vermont
        'WA' => ['x' => 116.5, 'y' => 233.9],  // Washington
        'WI' => ['x' => 116.5, 'y' => 239.4],  // Wisconsin
        'WV' => ['x' => 116.5, 'y' => 245.3],  // West Virginia
        'WY' => ['x' => 116.5, 'y' => 250.7],  // Wyoming
    ];

    public function getTemplatePath(): string
    {
        return 'pdfs/state_resale_certificates/sst.pdf';
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

        $pdf->SetAutoPageBreak(false);

        $data = $this->extractCertificateData($certificate);

        // Coordinates are in millimeters (mm) - FPDI default unit
        // Note: Y coordinates include a +4mm offset adjustment for PDF rendering alignment
        // Business Information
        $this->writeAt($pdf, 20.0, 53.4, $data->businessName);
        $this->writeAt($pdf, 20.0, 62.8, $data->businessStreetAddress);
        $this->writeAt($pdf, 109.4, 62.8, $data->businessCity);
        $this->writeAt($pdf, 145.8, 62.8, $data->businessState);
        $this->writeAt($pdf, 177.6, 62.8, $data->businessZip);
        $this->writeAt($pdf, 145.8, 121.6, $data->businessType);

        // Tax IDs / Permit Numbers (state-specific coordinates)
        // For SST uniform certificates, we may have multiple tax IDs for different states
        $business = $certificate->business_snapshot;
        $selectedStatesTaxIds = $business['selected_states_tax_ids'] ?? [];

        if (! empty($selectedStatesTaxIds)) {
            // Write tax IDs for each selected state covered by this SST certificate
            foreach ($selectedStatesTaxIds as $stateCode => $taxIdInfo) {
                $taxIdCoords = $this->getTaxIdCoordinates($stateCode);
                if ($taxIdCoords && ! empty($taxIdInfo['tax_id'])) {
                    $this->writeAt($pdf, $taxIdCoords['x'], $taxIdCoords['y'], $taxIdInfo['tax_id']);
                    // Write source state code (where the tax ID is from) 67 units to the right of Tax ID
                    $sourceState = $taxIdInfo['source_state'] ?? $stateCode;
                    $this->writeAt($pdf, $taxIdCoords['x'] + 67, $taxIdCoords['y'], $sourceState);
                    // Write "G" 82 units to the right of Tax ID
                    $this->writeAt($pdf, $taxIdCoords['x'] + 82, $taxIdCoords['y'], 'G');
                }
            }
        }

        // Vendor Information
        $this->writeAt($pdf, 20.0, 72.7, $data->vendorName);
        $this->writeAt($pdf, 20.0, 81.8, $data->vendorStreetAddress);
        $this->writeAt($pdf, 109.4, 81.8, $data->vendorCity);
        $this->writeAt($pdf, 145.8, 81.8, $data->vendorState);
        $this->writeAt($pdf, 177.6, 81.8, $data->vendorZip);

        // Certificate Details
        $this->writeAt($pdf, 174.0, 268.8, $data->issueDate);

        // Contact Information
        $this->writeAt($pdf, 81.1, 268.8, $data->signerName);
        $this->writeAt($pdf, 131.7, 268.8, $data->signerTitle);

        // Special Elements
        $this->writeAt($pdf, 144.6, 116.7, $data->checkmarkX);
        $this->writeAt($pdf, 12.0, 160.4, $data->checkmarkX);
        $this->addSignatureWithHeight($pdf, $certificate, 16.5, 265.1, 6);

        $pdf->SetAutoPageBreak(true);
    }
}

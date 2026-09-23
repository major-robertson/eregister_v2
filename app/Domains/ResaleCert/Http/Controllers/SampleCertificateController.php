<?php

namespace App\Domains\ResaleCert\Http\Controllers;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Services\CertificatePdfService;
use Illuminate\Http\Response;

/**
 * A sample certificate a customer can look at before subscribing: the
 * state's official form filled with fixed sample data (nothing saved, no
 * signature). Same sample shape as the admin coordinate mapper's preview,
 * without the grid overlay.
 */
class SampleCertificateController
{
    public function __invoke(string $stateCode, CertificatePdfService $pdfService): Response
    {
        $stateCode = strtoupper($stateCode);

        abort_unless(config()->has("resale_cert.states.{$stateCode}"), 404, 'Unknown state.');

        $bytes = $pdfService->renderCertificate($this->sampleCertificate($stateCode), false);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sample-resale-certificate-'.strtolower($stateCode).'.pdf"',
        ]);
    }

    protected function sampleCertificate(string $stateCode): ResaleCertificate
    {
        $certificate = new ResaleCertificate([
            'state_code' => $stateCode,
            'is_blanket' => true,
            'item_description' => 'All tangible personal property for resale',
            'business_snapshot' => [
                'legal_name' => 'Sample Trading LLC',
                'dba' => 'Sample Wholesale',
                'ein' => '12-3456789',
                'products_description' => 'General merchandise and consumer goods',
                'email' => 'billing@sample.test',
                'phone' => '(512) 555-1234',
                'signer_title' => 'Owner',
                'address' => [
                    'line1' => '100 Congress Ave',
                    'line2' => 'Suite 200',
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'US',
                ],
                'tax_id' => '11122233344',
                'tax_id_source_state' => $stateCode,
                'selected_states_tax_ids' => [
                    $stateCode => ['tax_id' => '11122233344', 'source_state' => $stateCode],
                ],
            ],
            'vendor_snapshot' => [
                'legal_name' => 'Sample Supplier Co',
                'address' => [
                    'line1' => '200 Main St',
                    'line2' => null,
                    'city' => 'Dallas',
                    'state' => 'TX',
                    'postal_code' => '75201',
                    'country' => 'US',
                ],
                'contact' => [
                    'name' => 'Pat Vendor',
                    'email' => 'pat@supplier.test',
                    'phone' => '(214) 555-9876',
                ],
            ],
            'issue_date' => now(),
        ]);

        $certificate->id = 0;

        return $certificate;
    }
}

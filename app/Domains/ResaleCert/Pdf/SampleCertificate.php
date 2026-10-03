<?php

namespace App\Domains\ResaleCert\Pdf;

use App\Domains\ResaleCert\Models\ResaleCertificate;

/**
 * An unsaved certificate filled with fixed sample data, for previews and
 * rendering checks: the admin coordinate mapper, the customer-facing sample
 * and the rendering tests all build it here.
 *
 * $sourceState is the state that issued the buyer's tax id. Pass a state
 * other than $stateCode to exercise a form's out-of-state branch.
 */
class SampleCertificate
{
    public static function make(string $stateCode, ?string $sourceState = null, string $brand = 'Acme'): ResaleCertificate
    {
        $sourceState ??= $stateCode;

        $certificate = new ResaleCertificate([
            'state_code' => $stateCode,
            'is_blanket' => true,
            'item_description' => 'All tangible personal property for resale',
            'business_snapshot' => [
                'legal_name' => $brand.' Trading LLC',
                'dba' => $brand.' Wholesale',
                'ein' => '12-3456789',
                'products_description' => 'General merchandise and consumer goods',
                'email' => 'billing@'.strtolower($brand).'.test',
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
                'tax_id_source_state' => $sourceState,
                'selected_states_tax_ids' => [
                    $stateCode => ['tax_id' => '11122233344', 'source_state' => $sourceState],
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

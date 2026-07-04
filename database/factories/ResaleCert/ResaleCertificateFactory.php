<?php

namespace Database\Factories\ResaleCert;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResaleCertificate>
 */
class ResaleCertificateFactory extends Factory
{
    protected $model = ResaleCertificate::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'resale_vendor_id' => ResaleVendor::factory(),
            'created_by_user_id' => null,
            'state_code' => 'TX',
            'is_blanket' => true,
            'item_description' => 'All taxable items purchased for resale',
            'business_snapshot' => [
                'legal_name' => 'Acme Trading LLC',
                'dba' => null,
                'ein' => '12-3456789',
                'products_description' => 'General merchandise',
                'email' => 'billing@acme.test',
                'phone' => '(512) 555-1234',
                'signer_title' => 'Owner',
                'address' => [
                    'line1' => '100 Congress Ave',
                    'line2' => null,
                    'city' => 'Austin',
                    'state' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'US',
                ],
                'tax_id' => '11122233344',
                'tax_id_source_state' => 'TX',
            ],
            'vendor_snapshot' => [
                'legal_name' => 'Supplier Co',
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
            'issue_date' => now()->toDateString(),
            'expiration_date' => now()->endOfYear()->toDateString(),
            'pdf_path' => null,
        ];
    }

    public function expiringSoon(int $days = 30): static
    {
        return $this->state(fn () => ['expiration_date' => now()->addDays($days)->toDateString()]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'issue_date' => now()->subYear()->toDateString(),
            'expiration_date' => now()->subDay()->toDateString(),
        ]);
    }
}

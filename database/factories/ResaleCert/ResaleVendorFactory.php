<?php

namespace Database\Factories\ResaleCert;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResaleVendor>
 */
class ResaleVendorFactory extends Factory
{
    protected $model = ResaleVendor::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'created_by_user_id' => null,
            'legal_name' => fake()->company(),
            'address_line1' => fake()->streetAddress(),
            'address_line2' => null,
            'city' => fake()->city(),
            'state' => 'TX',
            'postal_code' => fake()->numerify('#####'),
            'country' => 'US',
            'contact_name' => fake()->name(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => '5125559876',
        ];
    }
}

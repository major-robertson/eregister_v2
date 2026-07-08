<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LienContact>
 */
class LienContactFactory extends Factory
{
    protected $model = LienContact::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'company_name' => fake()->company(),
            'contact_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['CA', 'TX', 'FL', 'GA', 'NY']),
            'postal_code' => fake()->numerify('#####'),
        ];
    }

    public function forBusiness(Business $business): static
    {
        return $this->state(fn () => [
            'business_id' => $business->id,
        ]);
    }
}

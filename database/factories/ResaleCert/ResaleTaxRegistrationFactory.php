<?php

namespace Database\Factories\ResaleCert;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleTaxRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResaleTaxRegistration>
 */
class ResaleTaxRegistrationFactory extends Factory
{
    protected $model = ResaleTaxRegistration::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'state_code' => 'TX',
            'tax_id' => (string) fake()->numerify('###########'),
            'is_home_state' => false,
        ];
    }

    public function homeState(): static
    {
        return $this->state(fn () => ['is_home_state' => true]);
    }
}

<?php

namespace Database\Factories\Business;

use App\Domains\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition(): array
    {
        $companyName = fake()->company();

        return [
            'name' => $companyName,
            'legal_name' => $companyName,
            'entity_type' => fake()->randomElement(['llc', 'corporation', 'sole_proprietorship', 'partnership']),
            'business_address' => [
                'line1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
            ],
            'timezone' => 'America/New_York',
        ];
    }

    public function withLienOnboarding(): static
    {
        return $this->state(fn () => [
            'lien_onboarding_completed_at' => now(),
            'phone' => fake()->phoneNumber(),
            'state_of_incorporation' => fake()->stateAbbr(),
        ]);
    }

    public function onboarded(): static
    {
        return $this->state(fn () => [
            'onboarding_completed_at' => now(),
        ]);
    }
}

<?php

namespace Database\Factories\ResaleCert;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResaleProfile>
 */
class ResaleProfileFactory extends Factory
{
    protected $model = ResaleProfile::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'products_description' => 'General merchandise and consumer goods',
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => '5125551234',
            'mtc_enabled' => false,
            'default_expiration_rule' => 'end_of_current_year',
            'state_expiration_rules' => null,
            'completed_at' => now(),
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn () => ['completed_at' => null]);
    }

    public function mtcEnabled(): static
    {
        return $this->state(fn () => ['mtc_enabled' => true]);
    }
}

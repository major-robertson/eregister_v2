<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LienProject>
 */
class LienProjectFactory extends Factory
{
    protected $model = LienProject::class;

    public function definition(): array
    {
        $states = ['CA', 'TX', 'FL', 'NY', 'IL', 'PA', 'OH', 'GA', 'NC', 'MI'];

        return [
            'public_id' => Str::ulid()->toBase32(),
            'business_id' => Business::factory(),
            'name' => fake()->company().' - '.fake()->streetName(),
            'job_number' => fake()->optional()->numerify('JOB-####'),
            'claimant_type' => fake()->randomElement(ClaimantType::cases()),
            'jobsite_address1' => fake()->streetAddress(),
            'jobsite_city' => fake()->city(),
            'jobsite_state' => fake()->randomElement($states),
            'jobsite_zip' => fake()->postcode(),
            'jobsite_county' => fake()->city().' County',
            'first_furnish_date' => fake()->optional()->dateTimeBetween('-6 months', '-1 month'),
            'last_furnish_date' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function forBusiness(Business $business): static
    {
        return $this->state(fn () => [
            'business_id' => $business->id,
        ]);
    }

    public function inState(string $state): static
    {
        return $this->state(fn () => [
            'jobsite_state' => $state,
        ]);
    }

    public function asSubcontractor(): static
    {
        return $this->state(fn () => [
            'claimant_type' => ClaimantType::Subcontractor,
        ]);
    }

    public function asSupplier(): static
    {
        return $this->state(fn () => [
            'claimant_type' => ClaimantType::Supplier,
        ]);
    }

    public function withDates(): static
    {
        return $this->state(fn () => [
            'contract_date' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'first_furnish_date' => fake()->dateTimeBetween('-6 months', '-3 months'),
            'last_furnish_date' => fake()->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }
}

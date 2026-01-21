<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LienParty>
 */
class LienPartyFactory extends Factory
{
    protected $model = LienParty::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'project_id' => LienProject::factory(),
            'role' => fake()->randomElement(PartyRole::cases()),
            'name' => fake()->name(),
            'company_name' => fake()->optional()->company(),
            'address1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'zip' => fake()->postcode(),
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
        ];
    }

    public function forProject(LienProject $project): static
    {
        return $this->state(fn () => [
            'business_id' => $project->business_id,
            'project_id' => $project->id,
        ]);
    }

    public function asOwner(): static
    {
        return $this->state(fn () => [
            'role' => PartyRole::Owner,
        ]);
    }

    public function asCustomer(): static
    {
        return $this->state(fn () => [
            'role' => PartyRole::Customer,
        ]);
    }

    public function asClaimant(): static
    {
        return $this->state(fn () => [
            'role' => PartyRole::Claimant,
        ]);
    }

    public function asGc(): static
    {
        return $this->state(fn () => [
            'role' => PartyRole::Gc,
        ]);
    }
}

<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LienFiling>
 */
class LienFilingFactory extends Factory
{
    protected $model = LienFiling::class;

    public function definition(): array
    {
        return [
            'public_id' => Str::ulid()->toBase32(),
            'business_id' => Business::factory(),
            'project_id' => LienProject::factory(),
            'document_type_id' => fn () => LienDocumentType::first()?->id ?? 1,
            'service_level' => fake()->randomElement(ServiceLevel::cases()),
            'status' => FilingStatus::Draft,
            'jurisdiction_state' => 'CA',
            'jurisdiction_county' => 'Los Angeles',
            'amount_claimed_cents' => fake()->numberBetween(100000, 10000000),
            'description_of_work' => fake()->sentence(),
        ];
    }

    public function forProject(LienProject $project): static
    {
        return $this->state(fn () => [
            'business_id' => $project->business_id,
            'project_id' => $project->id,
            'jurisdiction_state' => $project->jobsite_state,
            'jurisdiction_county' => $project->jobsite_county,
        ]);
    }

    public function selfServe(): static
    {
        return $this->state(fn () => [
            'service_level' => ServiceLevel::SelfServe,
        ]);
    }

    public function fullService(): static
    {
        return $this->state(fn () => [
            'service_level' => ServiceLevel::FullService,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => FilingStatus::Draft,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function complete(): static
    {
        return $this->state(fn () => [
            'status' => FilingStatus::Complete,
            'paid_at' => now()->subDays(7),
            'completed_at' => now(),
        ]);
    }
}

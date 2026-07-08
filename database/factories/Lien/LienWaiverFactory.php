<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LienWaiver>
 */
class LienWaiverFactory extends Factory
{
    protected $model = LienWaiver::class;

    public function definition(): array
    {
        return [
            'public_id' => Str::ulid()->toBase32(),
            'business_id' => Business::factory(),
            'project_id' => LienProject::factory(),
            'direction' => WaiverDirection::Provide,
            'kind' => WaiverKind::ConditionalProgress,
            'status' => WaiverStatus::Draft,
            'source' => 'generated',
            'state' => 'TX',
            'amount_cents' => fake()->numberBetween(50_000, 5_000_000),
            'through_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'counterparty_company' => fake()->company(),
            'counterparty_name' => fake()->name(),
            'counterparty_email' => fake()->safeEmail(),
        ];
    }

    public function forBusiness(Business $business): static
    {
        return $this->state(fn () => [
            'business_id' => $business->id,
        ]);
    }

    public function forProject(LienProject $project): static
    {
        return $this->state(fn () => [
            'business_id' => $project->business_id,
            'project_id' => $project->id,
            'state' => $project->jobsite_state,
        ]);
    }

    public function inState(string $state): static
    {
        return $this->state(fn () => [
            'state' => $state,
        ]);
    }

    public function collect(): static
    {
        return $this->state(fn () => [
            'direction' => WaiverDirection::Collect,
            'signer_name' => fake()->name(),
            'signer_email' => fake()->safeEmail(),
        ]);
    }

    public function generated(): static
    {
        return $this->state(fn () => [
            'status' => WaiverStatus::Generated,
            'generated_at' => now(),
        ]);
    }

    public function signed(): static
    {
        return $this->state(fn () => [
            'status' => WaiverStatus::Signed,
            'generated_at' => now()->subDay(),
            'signed_at' => now(),
        ]);
    }
}

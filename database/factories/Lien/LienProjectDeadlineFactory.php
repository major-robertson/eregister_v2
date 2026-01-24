<?php

namespace Database\Factories\Lien;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LienProjectDeadline>
 */
class LienProjectDeadlineFactory extends Factory
{
    protected $model = LienProjectDeadline::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'project_id' => LienProject::factory(),
            'deadline_rule_id' => fn () => LienDeadlineRule::first()?->id ?? 1,
            'document_type_id' => fn () => LienDocumentType::first()?->id ?? 1,
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => DeadlineStatus::Pending,
        ];
    }

    public function forProject(LienProject $project): static
    {
        return $this->state(fn () => [
            'business_id' => $project->business_id,
            'project_id' => $project->id,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => DeadlineStatus::Pending,
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('now', '+7 days'),
            'status' => DeadlineStatus::Pending,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => DeadlineStatus::Completed,
        ]);
    }

    public function missed(): static
    {
        return $this->state(fn () => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'status' => DeadlineStatus::Missed,
        ]);
    }
}

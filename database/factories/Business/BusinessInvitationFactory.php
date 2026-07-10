<?php

namespace Database\Factories\Business;

use App\Domains\Business\Models\Business;
use App\Domains\Business\Models\BusinessInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessInvitation>
 */
class BusinessInvitationFactory extends Factory
{
    protected $model = BusinessInvitation::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'email' => strtolower(fake()->unique()->safeEmail()),
            'role' => 'member',
            'invited_by_user_id' => User::factory(),
            'expires_at' => now()->addDays(7),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }
}

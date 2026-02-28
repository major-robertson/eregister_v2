<?php

namespace Database\Factories;

use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailUnsubscribe>
 */
class EmailUnsubscribeFactory extends Factory
{
    protected $model = EmailUnsubscribe::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category' => EmailUnsubscribe::CATEGORY_ABANDON_CHECKOUT,
            'created_at' => now(),
        ];
    }

    public function marketing(): static
    {
        return $this->state(fn () => [
            'category' => EmailUnsubscribe::CATEGORY_MARKETING,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Domains\Business\Models\Business;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmailSequence>
 */
class EmailSequenceFactory extends Factory
{
    protected $model = EmailSequence::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_id' => Business::factory(),
            'sequence_type' => 'abandon_checkout',
            'customer_type' => 'new',
            'resume_url' => fake()->url(),
            'next_send_at' => now()->addHour(),
        ];
    }

    public function returning(): static
    {
        return $this->state(fn () => [
            'customer_type' => 'returning',
        ]);
    }

    public function readyToSend(): static
    {
        return $this->state(fn () => [
            'next_send_at' => now()->subMinute(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SentEmail>
 */
class SentEmailFactory extends Factory
{
    protected $model = SentEmail::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'email_type' => 'welcome',
            'scheduled_at' => now(),
            'sent_at' => now(),
        ];
    }

    public function forEmailable(\Illuminate\Database\Eloquent\Model $emailable): static
    {
        return $this->state(fn () => [
            'emailable_type' => $emailable->getMorphClass(),
            'emailable_id' => $emailable->getKey(),
        ]);
    }
}

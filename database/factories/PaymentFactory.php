<?php

namespace Database\Factories;

use App\Domains\Business\Models\Business;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'purchasable_type' => 'App\\Domains\\Lien\\Models\\LienFiling',
            'purchasable_id' => 0,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => fake()->numberBetween(5000, 50000),
            'currency' => 'usd',
            'status' => PaymentStatus::Initiated,
            'billing_type' => 'one_time',
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
            'stripe_charge_id' => 'ch_'.fake()->uuid(),
        ]);
    }

    public function forPurchasable($purchasable): static
    {
        return $this->state(fn () => [
            'purchasable_type' => $purchasable->getMorphClass(),
            'purchasable_id' => $purchasable->getKey(),
            'business_id' => $purchasable->business_id,
        ]);
    }
}

<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\Dashboard;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'postal_code' => '78701'],
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

it('streams a sample certificate on the official form for a customer who has not subscribed', function () {
    $response = $this->get(route('resale-cert.sample', 'TX'))->assertOk();

    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and(substr($response->getContent(), 0, 4))->toBe('%PDF');
});

it('answers 404 for a state without a certificate form', function () {
    $this->get(route('resale-cert.sample', 'ZZ'))->assertNotFound();
});

it('links the sample for the business state from the pricing pitch', function () {
    Livewire::test(Dashboard::class)
        ->assertSee('See a sample certificate')
        ->assertSee(route('resale-cert.sample', 'TX'));
});

it('requires a signed-in customer', function () {
    auth()->logout();

    $this->get(route('resale-cert.sample', 'TX'))->assertRedirect(route('login'));
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

/**
 * LLC Formation collects payment at the END of the wizard (like Sales Tax
 * and Liens), rather than gating the form behind an upfront subscription.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Payment Flow Co',
        'legal_name' => 'Payment Flow LLC',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function bootReviewReadyLlc(Business $business, User $user): FormApplication
{
    $address = [
        'line1' => '100 Market St',
        'city' => 'Dover',
        'state' => 'DE',
        'zip' => '19901',
    ];

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'draft',
        'current_phase' => 'review',
        'current_step_key' => null,
        'current_state_index' => 0,
        'core_data' => [
            'llc_name' => 'Acme Ventures LLC',
            'purpose' => 'General software consulting services.',
            'formation_date' => now()->addWeek()->toDateString(),
            'management_type' => 'member_managed',
            'principal_address' => $address,
            'members' => [
                [
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'email' => 'jane@example.com',
                    'address' => $address,
                    'ownership_percent' => 100,
                ],
            ],
        ],
        'created_by_user_id' => $user->id,
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'DE',
        'status' => 'complete',
        'data' => [],
    ]);

    return $application;
}

it('redirects an unpaid LLC to the formations checkout on submit', function () {
    $application = bootReviewReadyLlc($this->business, $this->user);

    Livewire::test(MultiStateFormRunner::class, ['application' => $application])
        ->call('submit')
        ->assertRedirect(route('formations.checkout', $application));

    expect($application->fresh()->status)->toBe('draft');
});

it('still routes to checkout even when the business already holds an LLC subscription', function () {
    // Billing is per-application: each LLC owes its own one-time state filing
    // fee, so an existing membership subscription does NOT bypass checkout.
    $this->business->subscriptions()->create([
        'type' => 'llc',
        'stripe_id' => 'sub_test_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => 'price_test',
        'quantity' => 1,
    ]);

    $application = bootReviewReadyLlc($this->business, $this->user);

    Livewire::test(MultiStateFormRunner::class, ['application' => $application])
        ->call('submit')
        ->assertRedirect(route('formations.checkout', $application));

    expect($application->fresh()->status)->toBe('draft');
});

it('submits an already-paid LLC directly on submit', function () {
    $application = bootReviewReadyLlc($this->business, $this->user);
    $application->update(['paid_at' => now()]);

    Livewire::test(MultiStateFormRunner::class, ['application' => $application])
        ->call('submit')
        ->assertRedirect(route('dashboard'));

    $fresh = $application->fresh();
    expect($fresh->status)->toBe('submitted');
    expect($fresh->submitted_at)->not->toBeNull();
    expect($fresh->locked_at)->not->toBeNull();
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\RegistrationCheckout;
use App\Models\Price;
use App\Models\User;
use App\Support\Analytics\Gtag;
use Livewire\Livewire;

/**
 * @param  array<int, string>  $states
 * @param  array<string, mixed>  $overrides
 */
function trackedRegistration(Business $business, User $user, array $states, array $overrides = []): FormApplication
{
    $application = FormApplication::create(array_merge([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $user->id,
    ], $overrides));

    foreach ($states as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    return $application;
}

beforeEach(function () {
    config(['cashier.secret' => null]);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'rush', 'billing_type' => 'one_time'],
        ['amount_cents' => 9900, 'currency' => 'usd', 'active' => true],
    );

    $this->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

it('queues a registration_order event with the order value when the order screen is shown', function () {
    $application = trackedRegistration($this->business, $this->user, ['CA', 'TX']);

    Livewire::test(RegistrationCheckout::class, ['application' => $application])
        ->assertSet('step', 'order');

    $events = Gtag::drain();

    expect($events)->toHaveCount(1)
        ->and($events[0]['name'])->toBe('registration_order')
        ->and($events[0]['params'])->toMatchArray(['currency' => 'USD', 'value' => 398.0, 'states' => 2, 'rush' => false]);
});

it('queues begin_checkout with the rush fee included when the card form is shown', function () {
    $application = trackedRegistration($this->business, $this->user, ['CA'], ['rush_requested_at' => now()]);

    // Keyless stub: pays and redirects, the event drains on the confirmation page.
    Livewire::withQueryParams(['step' => 'pay'])
        ->test(RegistrationCheckout::class, ['application' => $application]);

    $events = Gtag::drain();

    expect($events)->toHaveCount(1)
        ->and($events[0]['name'])->toBe('begin_checkout')
        ->and($events[0]['params'])->toMatchArray(['value' => 298.0, 'states' => 1, 'rush' => true]);
});

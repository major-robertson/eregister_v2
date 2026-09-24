<?php

use App\Domains\Business\Livewire\BusinessSwitcher;
use App\Domains\Business\Models\Business;
use App\Models\User;
use Livewire\Livewire;

/**
 * A waiver sign-up sets the business up on one screen (name and address)
 * and goes straight to the wizard. Everyone else keeps the two screens, and
 * a lien sign-up still continues from the address screen into lien
 * onboarding.
 */
it('gives a waiver sign-up one screen for the business name and address', function () {
    $user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver/tx']);
    $this->actingAs($user)->withSession(['just_registered' => true]);

    Livewire::test(BusinessSwitcher::class)
        ->assertSet('oneScreenSetup', true)
        ->assertSet('justRegistered', true)
        ->assertSee('Step 2 of 4')
        ->assertSee('Your business')
        ->assertDontSee("What's your business name?", false)
        // Everything is checked at once.
        ->call('createBusiness')
        ->assertHasErrors(['newBusinessName', 'businessAddress.line1', 'businessAddress.city', 'businessAddress.state', 'businessAddress.zip'])
        ->set('newBusinessName', 'Walk Contracting LLC')
        ->set('businessAddress.line1', '100 Congress Ave')
        ->set('businessAddress.city', 'Austin')
        ->set('businessAddress.state', 'TX')
        ->set('businessAddress.zip', '78701')
        ->call('createBusiness')
        ->assertHasNoErrors()
        ->assertRedirect(route('lien.waivers.create'));

    $business = Business::query()->sole();
    expect($business->name)->toBe('Walk Contracting LLC');
    expect($business->legal_name)->toBe('Walk Contracting LLC');
    expect($business->business_address)->toMatchArray([
        'line1' => '100 Congress Ave',
        'city' => 'Austin',
        'state' => 'TX',
        'zip' => '78701',
    ]);
    expect($business->isOnboardingComplete())->toBeTrue();
    expect($user->businesses()->count())->toBe(1);
    expect(session('current_business_id'))->toBe($business->id);
    // Consumed here: the address screen that normally pulls it is skipped.
    expect(session('just_registered'))->toBeNull();
});

it('fills the address from the autocomplete pick', function () {
    $user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver']);
    $this->actingAs($user);

    Livewire::test(BusinessSwitcher::class)
        ->call('updateAddressFromAutocomplete', [
            'line1' => '1600 Amphitheatre Parkway',
            'city' => 'Mountain View',
            'state' => 'ca',
            'zip' => '94043',
            'county' => 'Santa Clara County',
        ])
        ->assertSet('businessAddress.line1', '1600 Amphitheatre Parkway')
        ->assertSet('businessAddress.city', 'Mountain View')
        ->assertSet('businessAddress.state', 'CA')
        ->assertSet('businessAddress.zip', '94043')
        ->assertSet('businessAddress.county', 'Santa Clara County');
});

it('fires the sign-up conversion scripts once on the one-screen setup', function () {
    $user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver']);
    $this->actingAs($user)->withSession(['just_registered' => true]);

    $this->get(route('portal.select-business'))
        ->assertOk()
        ->assertSee("gtag('event', 'sign_up'", false)
        ->assertSee('AW-984288380/XDg5CMWk_7oZEPyYrNUD', false);

    // The marker is consumed: a refresh must not re-fire the conversions.
    $this->get(route('portal.select-business'))
        ->assertOk()
        ->assertDontSee('AW-984288380/XDg5CMWk_7oZEPyYrNUD', false);
});

it('keeps the two screens for a lien sign-up', function () {
    $user = User::factory()->create(['signup_landing_path' => '/liens']);
    $this->actingAs($user)->withSession(['just_registered' => true]);

    Livewire::test(BusinessSwitcher::class)
        ->assertSet('oneScreenSetup', false)
        ->assertSee("What's your business name?", false)
        ->assertDontSee('Step 2 of 4')
        ->set('newBusinessName', 'Lien Business')
        ->call('createBusiness')
        ->assertHasNoErrors()
        ->assertRedirect(route('portal.onboarding'));

    $business = Business::query()->sole();
    expect($business->isOnboardingComplete())->toBeFalse();
    // The address screen still gets to fire the sign-up conversion.
    expect(session('just_registered'))->toBeTrue();
});

it('shows a waiver sign-up who already has a business the normal switcher', function () {
    $user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver']);
    $business = Business::create(['name' => 'Existing Co', 'onboarding_completed_at' => now()]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);
    $this->actingAs($user);

    Livewire::test(BusinessSwitcher::class)
        ->assertSet('oneScreenSetup', false)
        ->assertSee('Welcome back')
        ->assertDontSee('Step 2 of 4');
});

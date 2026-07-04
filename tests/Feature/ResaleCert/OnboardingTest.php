<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\ResaleOnboarding;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Models\User;
use App\Models\UserSignature;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Resale Onboard Co',
        'legal_name' => 'Resale Onboard Co LLC',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
    subscribeToResaleCerts($this->business);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function giveResaleSignature(User $user): UserSignature
{
    return UserSignature::create([
        'user_id' => $user->id,
        'image_path' => 'resale-certificates/signatures/test.png',
        'is_current' => true,
        'agreed_to_terms' => true,
        'agreed_at' => now(),
    ]);
}

it('redirects unsubscribed businesses to the dashboard pricing card', function () {
    $this->business->subscriptions()->delete();

    $this->get(route('resale-cert.onboarding'))
        ->assertRedirect(route('resale-cert.dashboard'));
});

it('validates step one before continuing', function () {
    Livewire::test(ResaleOnboarding::class)
        ->set('products_description', 'short')
        ->set('signer_title', '')
        ->call('nextStep')
        ->assertHasErrors(['products_description', 'signer_title']);
});

it('persists step one and records the signer title on the business', function () {
    Livewire::test(ResaleOnboarding::class)
        ->set('products_description', 'Apparel, footwear, and accessories')
        ->set('contact_email', 'shop@resale.test')
        ->set('contact_phone', '(512) 555-1234')
        ->set('signer_title', 'Owner')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 2);

    $profile = ResaleProfile::withoutGlobalScopes()->where('business_id', $this->business->id)->first();

    expect($profile)->not->toBeNull()
        ->and($profile->products_description)->toBe('Apparel, footwear, and accessories')
        ->and($profile->completed_at)->toBeNull();

    expect($this->business->fresh()->getResponsiblePersonForUser($this->user->id)['title'])->toBe('Owner');
});

it('requires at least one tax registration with unique states', function () {
    Livewire::test(ResaleOnboarding::class)
        ->set('step', 2)
        ->set('registrations', [
            ['state_code' => 'TX', 'tax_id' => '111'],
            ['state_code' => 'TX', 'tax_id' => '222'],
        ])
        ->call('nextStep')
        ->assertHasErrors(['registrations']);
});

it('saves registrations with the first as home state', function () {
    Livewire::test(ResaleOnboarding::class)
        ->set('step', 2)
        ->set('registrations', [
            ['state_code' => 'TX', 'tax_id' => '11122233344'],
            ['state_code' => 'CA', 'tax_id' => '99-887766'],
        ])
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 3);

    $registrations = $this->business->resaleTaxRegistrations()->orderByDesc('is_home_state')->get();

    expect($registrations)->toHaveCount(2)
        ->and($registrations[0]->state_code)->toBe('TX')
        ->and($registrations[0]->is_home_state)->toBeTrue()
        ->and($registrations[0]->tax_id)->toBe('11122233344')
        ->and($registrations[1]->is_home_state)->toBeFalse();
});

it('blocks finishing without a signature', function () {
    ResaleProfile::factory()->incomplete()->create(['business_id' => $this->business->id]);

    Livewire::test(ResaleOnboarding::class)
        ->set('step', 3)
        ->call('finish')
        ->assertHasErrors(['signature']);

    expect($this->business->resaleProfile->completed_at)->toBeNull();
});

it('completes onboarding once a signature exists', function () {
    ResaleProfile::factory()->incomplete()->create(['business_id' => $this->business->id]);
    giveResaleSignature($this->user);

    Livewire::test(ResaleOnboarding::class)
        ->set('step', 3)
        ->call('finish')
        ->assertHasNoErrors()
        ->assertRedirect(route('resale-cert.dashboard'));

    expect($this->business->resaleProfile->fresh()->completed_at)->not->toBeNull();
});

it('redirects to the dashboard when the profile is already complete', function () {
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $this->get(route('resale-cert.onboarding'))
        ->assertRedirect(route('resale-cert.dashboard'));
});

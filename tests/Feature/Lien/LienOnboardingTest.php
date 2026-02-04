<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\LienOnboarding;
use App\Domains\Lien\Livewire\LienProfileComplete;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => null, // Not completed yet
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('redirects to lien onboarding when not completed', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->get(route('lien.projects.index'))
        ->assertRedirect(route('lien.onboarding'));
});

it('allows access to lien routes when onboarding is complete', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->business->update(['lien_onboarding_completed_at' => now()]);

    $this->get(route('lien.projects.index'))
        ->assertSuccessful();
});

it('can view lien onboarding page', function () {
    $this->get(route('lien.onboarding'))
        ->assertSuccessful();
});

it('redirects to profile complete when user has no projects', function () {
    Livewire::test(LienOnboarding::class)
        // Step 1: Business Contact
        ->assertSet('step', 1)
        ->set('phone', '5551234567')
        ->set('contractorLicenseNumber', 'ABC123456')
        ->call('nextStep')
        ->assertSet('step', 2)
        // Step 2: Authorized Signer
        ->set('signerFirstName', 'John')
        ->set('signerLastName', 'Smith')
        ->set('signerTitle', 'Owner')
        ->call('complete')
        ->assertRedirect(route('lien.profile-complete'));

    $this->business->refresh();

    expect($this->business->phone)->toBe('5551234567');
    expect($this->business->contractor_license_number)->toBe('ABC123456');
    expect($this->business->isLienOnboardingComplete())->toBeTrue();

    // Check responsible person was saved
    $responsiblePerson = $this->business->getResponsiblePersonForUser($this->user->id);
    expect($responsiblePerson)->not->toBeNull();
    expect($responsiblePerson['name'])->toBe('John Smith');
    expect($responsiblePerson['title'])->toBe('Owner');
});

it('redirects to projects index when user has existing projects', function () {
    // Create a project for this business
    LienProject::factory()->create([
        'business_id' => $this->business->id,
        'created_by_user_id' => $this->user->id,
    ]);

    Livewire::test(LienOnboarding::class)
        // Step 1: Business Contact
        ->assertSet('step', 1)
        ->set('phone', '5551234567')
        ->set('contractorLicenseNumber', 'ABC123456')
        ->call('nextStep')
        ->assertSet('step', 2)
        // Step 2: Authorized Signer
        ->set('signerFirstName', 'John')
        ->set('signerLastName', 'Smith')
        ->set('signerTitle', 'Owner')
        ->call('complete')
        ->assertRedirect(route('lien.projects.index'));

    expect($this->business->fresh()->isLienOnboardingComplete())->toBeTrue();
});

it('validates step 1 before proceeding', function () {
    Livewire::test(LienOnboarding::class)
        ->assertSet('step', 1)
        ->set('phone', '')
        ->call('nextStep')
        ->assertHasErrors(['phone'])
        ->assertSet('step', 1);
});

it('validates step 2 before completing', function () {
    Livewire::test(LienOnboarding::class)
        ->assertSet('step', 1)
        ->set('phone', '5551234567')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('signerFirstName', '')
        ->set('signerLastName', '')
        ->set('signerTitle', '')
        ->call('complete')
        ->assertHasErrors(['signerFirstName', 'signerLastName', 'signerTitle']);
});

it('pre-populates from existing business data', function () {
    $this->business->update([
        'phone' => '5559998888',
    ]);

    Livewire::test(LienOnboarding::class)
        ->assertSet('phone', '5559998888');
});

it('can view profile complete page', function () {
    $this->get(route('lien.profile-complete'))
        ->assertSuccessful();
});

it('profile complete page redirects to project creation', function () {
    Livewire::test(LienProfileComplete::class)
        ->assertSee('Great, your profile has now been set up.')
        ->assertSee("Let's add the project you need to track or file a lien on.", escape: false)
        ->call('proceed')
        ->assertRedirect(route('lien.projects.create'));
});

it('profile complete page shows correct progress dots for continuous flow', function () {
    // Set up continuous flow conditions
    $this->user->update(['signup_landing_path' => '/liens']);

    Livewire::test(LienProfileComplete::class)
        ->assertSet('isContinuousFlow', true);
});

it('profile complete page shows correct progress dots for standalone flow', function () {
    // Default user without liens signup path
    $this->user->update(['signup_landing_path' => null]);

    Livewire::test(LienProfileComplete::class)
        ->assertSet('isContinuousFlow', false);
});

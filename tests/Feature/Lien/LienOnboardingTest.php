<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\LienOnboarding;
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

it('can complete lien onboarding wizard', function () {
    Livewire::test(LienOnboarding::class)
        // Step 1: Entity Info
        ->assertSet('step', 1)
        ->set('entityType', 'llc')
        ->set('stateOfIncorporation', 'CA')
        ->call('nextStep')
        ->assertSet('step', 2)
        // Step 2: Business Contact
        ->set('phone', '5551234567')
        ->set('contractorLicenseNumber', 'ABC123456')
        ->call('nextStep')
        ->assertSet('step', 3)
        // Step 3: Authorized Signer
        ->set('signerFirstName', 'John')
        ->set('signerLastName', 'Smith')
        ->set('signerTitle', 'Owner')
        ->call('complete')
        ->assertRedirect(route('lien.projects.index'));

    $this->business->refresh();

    expect($this->business->entity_type)->toBe('llc');
    expect($this->business->state_of_incorporation)->toBe('CA');
    expect($this->business->phone)->toBe('5551234567');
    expect($this->business->contractor_license_number)->toBe('ABC123456');
    expect($this->business->isLienOnboardingComplete())->toBeTrue();

    // Check responsible person was saved
    $responsiblePerson = $this->business->getResponsiblePersonForUser($this->user->id);
    expect($responsiblePerson)->not->toBeNull();
    expect($responsiblePerson['name'])->toBe('John Smith');
    expect($responsiblePerson['title'])->toBe('Owner');
});

it('validates step 1 before proceeding', function () {
    // Create business without entity type set
    $this->business->update([
        'entity_type' => null,
        'state_of_incorporation' => null,
    ]);

    Livewire::test(LienOnboarding::class)
        ->assertSet('step', 1)
        ->set('entityType', '') // Clear any pre-populated value
        ->call('nextStep')
        ->assertHasErrors(['entityType'])
        ->assertSet('step', 1);
});

it('requires state of incorporation for LLC', function () {
    Livewire::test(LienOnboarding::class)
        ->assertSet('step', 1)
        ->set('entityType', 'llc')
        ->set('stateOfIncorporation', '')
        ->call('nextStep')
        ->assertHasErrors(['stateOfIncorporation'])
        ->assertSet('step', 1);
});

it('validates step 2 before proceeding', function () {
    Livewire::test(LienOnboarding::class)
        ->set('entityType', 'llc')
        ->set('stateOfIncorporation', 'CA')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('phone', '')
        ->call('nextStep')
        ->assertHasErrors(['phone'])
        ->assertSet('step', 2);
});

it('validates step 3 before completing', function () {
    Livewire::test(LienOnboarding::class)
        ->set('entityType', 'llc')
        ->set('stateOfIncorporation', 'CA')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('phone', '5551234567')
        ->call('nextStep')
        ->assertSet('step', 3)
        ->set('signerFirstName', '')
        ->set('signerLastName', '')
        ->set('signerTitle', '')
        ->call('complete')
        ->assertHasErrors(['signerFirstName', 'signerLastName', 'signerTitle']);
});

it('pre-populates from existing business data', function () {
    $this->business->update([
        'entity_type' => 'corporation',
        'state_of_incorporation' => 'NY',
        'phone' => '5559998888',
    ]);

    Livewire::test(LienOnboarding::class)
        ->assertSet('entityType', 'corporation')
        ->assertSet('stateOfIncorporation', 'NY')
        ->assertSet('phone', '5559998888');
});

<?php

use App\Domains\Business\Livewire\OnboardingWizard;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

describe('OnboardingWizard', function () {
    it('saves legal name to business on step 1', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => '']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class)
            ->set('legalName', 'Acme Corporation')
            ->call('nextStep')
            ->assertHasNoErrors();

        $business->refresh();
        expect($business->name)->toBe('Acme Corporation');
        expect($business->legal_name)->toBe('Acme Corporation');
    });

    it('saves business address as JSON on complete', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class)
            ->set('businessAddress.line1', '123 Main Street')
            ->set('businessAddress.line2', 'Suite 100')
            ->set('businessAddress.city', 'Los Angeles')
            ->set('businessAddress.state', 'CA')
            ->set('businessAddress.zip', '90001')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $business->refresh();
        expect($business->business_address)->toBe([
            'line1' => '123 Main Street',
            'line2' => 'Suite 100',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90001',
        ]);
        expect($business->isOnboardingComplete())->toBeTrue();
    });

    it('omits empty line2 from address JSON', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class)
            ->set('businessAddress.line1', '456 Oak Avenue')
            ->set('businessAddress.line2', '')
            ->set('businessAddress.city', 'San Francisco')
            ->set('businessAddress.state', 'CA')
            ->set('businessAddress.zip', '94102')
            ->call('complete')
            ->assertHasNoErrors();

        $business->refresh();
        expect($business->business_address)->not->toHaveKey('line2');
        expect($business->business_address)->toBe([
            'line1' => '456 Oak Avenue',
            'city' => 'San Francisco',
            'state' => 'CA',
            'zip' => '94102',
        ]);
    });

    it('validates required address fields', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class)
            ->set('businessAddress.line1', '')
            ->set('businessAddress.city', '')
            ->set('businessAddress.state', '')
            ->set('businessAddress.zip', '')
            ->call('complete')
            ->assertHasErrors([
                'businessAddress.line1',
                'businessAddress.city',
                'businessAddress.state',
                'businessAddress.zip',
            ]);
    });

    it('loads existing data on mount', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Existing Business',
            'legal_name' => 'Existing Legal Name',
            'business_address' => [
                'line1' => '789 Pine St',
                'city' => 'Seattle',
                'state' => 'WA',
                'zip' => '98101',
            ],
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $component = Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class);

        expect($component->get('legalName'))->toBe('Existing Legal Name');
        expect($component->get('businessAddress.line1'))->toBe('789 Pine St');
        expect($component->get('businessAddress.city'))->toBe('Seattle');
        expect($component->get('businessAddress.state'))->toBe('WA');
        expect($component->get('businessAddress.zip'))->toBe('98101');
        expect($component->get('step'))->toBe(2); // Should skip to step 2 since name exists
    });

    it('falls back to name if legal_name is not set', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'My Business Name',
            // legal_name is null - should fallback to name
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $component = Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(OnboardingWizard::class);

        expect($component->get('legalName'))->toBe('My Business Name');
        expect($component->get('step'))->toBe(2); // Should skip to step 2 since name exists
    });
});

describe('Form Application Prefill', function () {
    it('prefills form core data from business profile', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Prefill Test',
            'legal_name' => 'Prefill Legal Name',
            'dba_name' => 'Prefill DBA',
            'entity_type' => 'llc',
            'business_address' => [
                'line1' => '100 Prefill St',
                'line2' => 'Floor 5',
                'city' => 'Portland',
                'state' => 'OR',
                'zip' => '97201',
            ],
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Create an application with empty core_data to trigger prefill
        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => null, // Empty to trigger prefill
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);

        $component = Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(MultiStateFormRunner::class, ['application' => $application]);

        // Check that business data was prefilled into coreData
        $coreData = $component->get('coreData');
        expect($coreData['legal_name'])->toBe('Prefill Legal Name');
        expect($coreData['dba_name'])->toBe('Prefill DBA');
        expect($coreData['entity_type'])->toBe('llc');
        expect($coreData['business_address'])->toBe([
            'line1' => '100 Prefill St',
            'line2' => 'Floor 5',
            'city' => 'Portland',
            'state' => 'OR',
            'zip' => '97201',
        ]);
    });

    it('persists form data with persist_to_business flag back to business', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Test Business',
            'legal_name' => 'Test Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Create application with empty core_data
        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => null,
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);

        $component = Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(MultiStateFormRunner::class, ['application' => $application]);

        // Update entity_type (which has persist_to_business: true)
        $component->set('coreData.entity_type', 'corp')
            ->set('coreData.dba_name', 'Test DBA')
            ->call('nextStep');

        $business->refresh();
        expect($business->entity_type)->toBe('corp');
        expect($business->dba_name)->toBe('Test DBA');
    });

    it('does not override existing core data', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Override Test',
            'legal_name' => 'Business Legal Name',
            'dba_name' => 'Business DBA',
            'business_address' => [
                'line1' => '200 Business St',
                'city' => 'Denver',
                'state' => 'CO',
                'zip' => '80201',
            ],
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        // Create an application with existing core_data
        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [
                'legal_name' => 'User Entered Name',
                'dba_name' => 'User Entered DBA',
            ],
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'TX',
            'status' => 'pending',
            'data' => [],
        ]);

        $component = Livewire::actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->test(MultiStateFormRunner::class, ['application' => $application]);

        // Check that existing data was NOT overwritten
        $coreData = $component->get('coreData');
        expect($coreData['legal_name'])->toBe('User Entered Name');
        expect($coreData['dba_name'])->toBe('User Entered DBA');
    });
});

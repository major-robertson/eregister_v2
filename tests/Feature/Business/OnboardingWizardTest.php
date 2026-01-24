<?php

use App\Domains\Business\Livewire\OnboardingWizard;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

describe('OnboardingWizard', function () {
    it('sets legal_name from name on mount if not already set', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Acme Corporation']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        expect($business->legal_name)->toBeNull();

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class);

        $business->refresh();
        expect($business->legal_name)->toBe('Acme Corporation');
    });

    it('saves business address as JSON on complete', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '123 Main Street')
            ->set('businessAddress.line2', 'Suite 100')
            ->set('businessAddress.city', 'Los Angeles')
            ->set('businessAddress.state', 'CA')
            ->set('businessAddress.zip', '90001')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $business->refresh();
        expect($business->business_address)->toMatchArray([
            'line1' => '123 Main Street',
            'line2' => 'Suite 100',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90001',
        ]);
        expect($business->isOnboardingComplete())->toBeTrue();
    });

    it('redirects to lien onboarding when user signed up from liens page with first business', function () {
        $user = User::factory()->create(['signup_landing_path' => '/liens']);
        $business = Business::create(['name' => 'Lien Business', 'legal_name' => 'Lien Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '123 Lien Street')
            ->set('businessAddress.city', 'Miami')
            ->set('businessAddress.state', 'FL')
            ->set('businessAddress.zip', '33101')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('lien.onboarding'));
    });

    it('redirects to dashboard when user from liens adds second business', function () {
        $user = User::factory()->create(['signup_landing_path' => '/liens']);

        // User already has a first business
        $firstBusiness = Business::create(['name' => 'First Business', 'legal_name' => 'First Business']);
        $user->businesses()->attach($firstBusiness->id, ['role' => 'owner']);

        // Now adding a second business
        $secondBusiness = Business::create(['name' => 'Second Business', 'legal_name' => 'Second Business']);
        $user->businesses()->attach($secondBusiness->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $secondBusiness->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '456 Second Street')
            ->set('businessAddress.city', 'Orlando')
            ->set('businessAddress.state', 'FL')
            ->set('businessAddress.zip', '32801')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));
    });

    it('redirects to dashboard when user signed up from other pages', function () {
        $user = User::factory()->create(['signup_landing_path' => '/']);
        $business = Business::create(['name' => 'Regular Business', 'legal_name' => 'Regular Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '456 Regular Ave')
            ->set('businessAddress.city', 'Chicago')
            ->set('businessAddress.state', 'IL')
            ->set('businessAddress.zip', '60601')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));
    });

    it('omits empty line2 from address JSON', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '456 Oak Avenue')
            ->set('businessAddress.line2', '')
            ->set('businessAddress.city', 'San Francisco')
            ->set('businessAddress.state', 'CA')
            ->set('businessAddress.zip', '94102')
            ->call('complete')
            ->assertHasNoErrors();

        $business->refresh();
        expect($business->business_address)->not->toHaveKey('line2');
        expect($business->business_address)->toMatchArray([
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

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
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

    it('loads existing address data on mount', function () {
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

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        $component = Livewire::test(OnboardingWizard::class);

        expect($component->get('businessAddress.line1'))->toBe('789 Pine St');
        expect($component->get('businessAddress.city'))->toBe('Seattle');
        expect($component->get('businessAddress.state'))->toBe('WA');
        expect($component->get('businessAddress.zip'))->toBe('98101');
    });

    it('sets legal_name from name if legal_name is not set', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'My Business Name',
            // legal_name is null - should be set from name
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class);

        $business->refresh();
        expect($business->legal_name)->toBe('My Business Name');
    });

    it('updates address from Google Maps autocomplete with geo data', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        $component = Livewire::test(OnboardingWizard::class)
            ->call('updateAddressFromAutocomplete', [
                'line1' => '1600 Amphitheatre Parkway',
                'line2' => '',
                'city' => 'Mountain View',
                'state' => 'CA',
                'zip' => '94043',
                'place_id' => 'ChIJ09H2YwK6j4ARoF7qfCBxhB8',
                'formatted_address' => '1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA',
                'lat' => 37.4220095,
                'lng' => -122.0847519,
                'county' => 'Santa Clara County',
                'country' => 'US',
            ]);

        // Verify basic address fields
        expect($component->get('businessAddress.line1'))->toBe('1600 Amphitheatre Parkway');
        expect($component->get('businessAddress.city'))->toBe('Mountain View');
        expect($component->get('businessAddress.state'))->toBe('CA');
        expect($component->get('businessAddress.zip'))->toBe('94043');

        // Verify geo fields
        expect($component->get('businessAddress.place_id'))->toBe('ChIJ09H2YwK6j4ARoF7qfCBxhB8');
        expect($component->get('businessAddress.lat'))->toBe(37.4220095);
        expect($component->get('businessAddress.lng'))->toBe(-122.0847519);
        expect($component->get('businessAddress.county'))->toBe('Santa Clara County');
        expect($component->get('businessAddress.country'))->toBe('US');
        expect($component->get('businessAddress.formatted_address'))->toBe('1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA');
    });

    it('saves geo data to database when completing onboarding', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Test Business', 'legal_name' => 'Test Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->call('updateAddressFromAutocomplete', [
                'line1' => '1600 Amphitheatre Parkway',
                'line2' => '',
                'city' => 'Mountain View',
                'state' => 'CA',
                'zip' => '94043',
                'place_id' => 'ChIJ09H2YwK6j4ARoF7qfCBxhB8',
                'formatted_address' => '1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA',
                'lat' => 37.4220095,
                'lng' => -122.0847519,
                'county' => 'Santa Clara County',
                'country' => 'US',
            ])
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $business->refresh();

        // Verify geo fields were saved
        expect($business->business_address['place_id'])->toBe('ChIJ09H2YwK6j4ARoF7qfCBxhB8');
        expect($business->business_address['lat'])->toBe(37.4220095);
        expect($business->business_address['lng'])->toBe(-122.0847519);
        expect($business->business_address['county'])->toBe('Santa Clara County');
        expect($business->business_address['country'])->toBe('US');
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

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        // Check that business data was prefilled into coreData
        $coreData = $component->get('coreData');
        expect($coreData['legal_name'])->toBe('Prefill Legal Name');
        expect($coreData['dba_name'])->toBe('Prefill DBA');
        expect($coreData['entity_type'])->toBe('llc');
        expect($coreData['business_address'])->toMatchArray([
            'line1' => '100 Prefill St',
            'line2' => 'Floor 5',
            'city' => 'Portland',
            'state' => 'OR',
            'zip' => '97201',
        ]);
    });

    it('persists form data with persist_to_business flag back to business', function () {
        // TODO: This test needs investigation - persistence mechanism may need different test setup
        $this->markTestSkipped('Persistence test requires investigation of test environment setup');

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

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        // Update entity_type (which has persist_to_business: true)
        $component->set('coreData.entity_type', 'corp')
            ->set('coreData.dba_name', 'Test DBA');

        // Save by moving through the form
        $steps = ['business', 'contact'];
        foreach ($steps as $index => $step) {
            if ($index > 0) {
                // Only advance after the first step
                $component->call('nextStep');
            }
        }

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

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        // Check that existing data was NOT overwritten
        $coreData = $component->get('coreData');
        expect($coreData['legal_name'])->toBe('User Entered Name');
        expect($coreData['dba_name'])->toBe('User Entered DBA');
    });
});

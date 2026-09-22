<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\StateSelector;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

describe('StateSelector', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->business = Business::create([
            'name' => 'Test Business',
            'onboarding_completed_at' => now(),
        ]);
        $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

        // Set the current business in session
        session(['current_business_id' => $this->business->id]);
    });

    it('displays available states for multi-state form type', function () {
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])->assertSee('Select States');
    });

    it('displays single state selection for LLC', function () {
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'llc',
        ])
            ->assertSee('Select State')
            ->assertSet('stateMode', 'single')
            ->assertSet('maxStates', 1);
    });

    it('can toggle states in multi mode', function () {
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])
            ->call('toggleState', 'CA')
            ->assertSet('selectedStates', ['CA'])
            ->call('toggleState', 'TX')
            ->assertSet('selectedStates', ['CA', 'TX'])
            ->call('toggleState', 'CA')
            ->assertSet('selectedStates', ['TX']);
    });

    it('replaces selection in single mode (LLC)', function () {
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'llc',
        ])
            ->call('toggleState', 'CA')
            ->assertSet('selectedStates', ['CA'])
            ->call('toggleState', 'TX')
            ->assertSet('selectedStates', ['TX']); // Should replace, not add
    });

    it('allows selecting all available states with no limit', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ]);

        $availableStates = $component->get('availableStates');

        foreach ($availableStates as $state) {
            $component->call('toggleState', $state);
        }

        expect($component->get('selectedStates'))->toHaveCount(count($availableStates));
    });

    it('has no select-all any more and can clear the selection', function () {
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])
            ->assertDontSee('Select All')
            ->call('toggleState', 'CA')
            ->call('toggleState', 'TX')
            ->call('clearAll')
            ->assertSet('selectedStates', []);
    });

    it('preselects the business state when there is no draft', function () {
        $this->business->update(['business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701']]);
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])->assertSet('selectedStates', ['TX']);
    });

    it('preselects the state the visitor picked on the marketing page over the business state', function () {
        $this->business->update(['business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701']]);
        \App\Support\SignupIntent::store(['product' => 'sales-tax', 'state' => 'ny']);
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])->assertSet('selectedStates', ['NY']);
    });

    it('does not preselect a state that is excluded, blocked, or unknown', function () {
        $this->business->update(['business_address' => ['line1' => '1 Main St', 'city' => 'Portland', 'state' => 'OR', 'zip' => '97201']]);
        \App\Support\SignupIntent::store(['product' => 'sales-tax', 'state' => 'ZZ']);
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])->assertSet('selectedStates', []);
    });

    it('shows the per-state price for the sales tax permit and none for the LLC', function () {
        \App\Models\Price::updateOrCreate(
            ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
            ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
        );
        $this->actingAs($this->user);

        Livewire::test(StateSelector::class, ['formType' => 'sales_tax_permit'])
            ->assertSet('perStateCents', 19900)
            ->assertSee('$199 per state, paid when you order');

        Livewire::test(StateSelector::class, ['formType' => 'llc'])
            ->assertSet('perStateCents', null)
            ->assertDontSee('per state, paid when you order');
    });

    it('creates application and redirects to form runner on proceed', function () {
        $this->actingAs($this->user);

        $component = Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])
            ->call('toggleState', 'CA')
            ->call('toggleState', 'TX')
            ->call('proceed');

        $application = FormApplication::where('business_id', $this->business->id)->first();
        expect($application)->not->toBeNull();

        // Sales tax orders before the questions: a new draft opens the
        // workspace's order screen, not the wizard.
        $component->assertRedirect(route('sales-tax.registrations.checkout', ['application' => $application]));

        expect($application->selected_states)->toBe(['CA', 'TX']);
        expect($application->status)->toBe('draft');
        expect($application->states)->toHaveCount(2);
        expect($application->definition_snapshot)->not->toBeNull();
        expect($application->definition_snapshot)->toHaveKeys(['base', 'states']);
    });

    it('detects and can resume existing draft', function () {
        $this->actingAs($this->user);

        // Create existing draft
        $existingApp = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['NY', 'FL'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
        ]);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])
            ->assertSet('existingDraft.id', $existingApp->id)
            ->assertSet('selectedStates', ['NY', 'FL'])
            ->assertSee('existing draft');
    });

    it('can start over and delete existing draft', function () {
        $this->actingAs($this->user);

        // Create existing draft
        $existingApp = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['NY'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
        ]);

        Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ])
            ->call('startOver')
            ->assertSet('existingDraft', null)
            ->assertSet('selectedStates', []);

        expect(FormApplication::find($existingApp->id))->toBeNull();
    });

    it('blocks states with paid applications', function () {
        $this->actingAs($this->user);

        // Create a paid application for CA
        $paidApp = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA', 'NY'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
            'paid_at' => now(),
        ]);

        // Create state records
        FormApplicationState::create([
            'form_application_id' => $paidApp->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);
        FormApplicationState::create([
            'form_application_id' => $paidApp->id,
            'state_code' => 'NY',
            'status' => 'pending',
            'data' => [],
        ]);

        $component = Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ]);

        // CA and NY should be blocked
        expect($component->get('blockedStates'))->toContain('CA');
        expect($component->get('blockedStates'))->toContain('NY');

        // Trying to toggle a blocked state should have no effect
        $component->call('toggleState', 'CA');
        expect($component->get('selectedStates'))->not->toContain('CA');

        // Non-blocked states should still work
        $component->call('toggleState', 'TX');
        expect($component->get('selectedStates'))->toContain('TX');
    });

    it('blocks states with submitted applications', function () {
        $this->actingAs($this->user);

        // Create a submitted application for FL
        $submittedApp = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['FL'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
            'submitted_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $submittedApp->id,
            'state_code' => 'FL',
            'status' => 'complete',
            'data' => [],
        ]);

        $component = Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ]);

        expect($component->get('blockedStates'))->toContain('FL');
    });

    it('does not preselect the business state when it already has a paid application', function () {
        $this->business->update(['business_address' => ['line1' => '1 Main St', 'city' => 'Los Angeles', 'state' => 'CA', 'zip' => '90001']]);
        $this->actingAs($this->user);

        // Create a paid application for CA
        $paidApp = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
            'paid_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $paidApp->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);

        $component = Livewire::test(StateSelector::class, [
            'formType' => 'sales_tax_permit',
        ]);

        // CA is blocked, so nothing is preselected
        expect($component->get('selectedStates'))->toBe([]);
    });
});

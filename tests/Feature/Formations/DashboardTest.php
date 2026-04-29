<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\Formations\Livewire\Dashboard;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Formations Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('redirects guests to login', function () {
    $this->get(route('formations.dashboard'))->assertRedirect(route('login'));
});

it('redirects authenticated users without a business to the selector', function () {
    $loneUser = User::factory()->create();

    $this->actingAs($loneUser)
        ->get(route('formations.dashboard'))
        ->assertRedirect(route('portal.select-business'));
});

it('renders the dashboard with the Form an LLC CTA for an authenticated user', function () {
    $this->actingAs($this->user)
        ->withSession(['current_business_id' => $this->business->id]);

    $this->get(route('formations.dashboard'))
        ->assertSuccessful()
        ->assertSee('Formations')
        ->assertSee('Form an LLC')
        ->assertSee('No formations yet');
});

it('lists past LLC formations for the current business', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $draft = FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    FormApplicationState::create([
        'form_application_id' => $draft->id,
        'state_code' => 'DE',
        'status' => 'pending',
        'data' => [],
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('LLC Formation')
        ->assertSee('Draft')
        ->assertSee('Continue')
        ->assertSee('DE');
});

it('does not leak formations from other businesses', function () {
    $otherBusiness = Business::create([
        'name' => 'Someone Else Inc',
        'onboarding_completed_at' => now(),
    ]);

    FormApplication::create([
        'business_id' => $otherBusiness->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['NV'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('No formations yet')
        ->assertDontSee('NV');
});

it('does not show sales tax applications under formations', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('No formations yet')
        ->assertDontSee('CA');
});

it('uses View label for paid/submitted formations', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['DE'],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
        'submitted_at' => now(),
        'paid_at' => now(),
    ]);

    Livewire::test(Dashboard::class)
        ->assertSee('Submitted')
        ->assertSee('View')
        ->assertDontSee('Continue');
});

it('limits visible formations to ten with a hint when more exist', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    for ($i = 0; $i < 11; $i++) {
        FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['DE'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSet('hasMoreFormations', true)
        ->assertSee('Showing the 10 most recent');
});

it('renders the formations state selector inside the Formations workspace sidebar', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $response = $this->get(route('formations.start', ['formType' => 'llc']));

    $response->assertSuccessful()
        ->assertSee('Select State')
        // Workspace sidebar markers prove we're inside <x-layouts.workspace key="formations">
        ->assertSee('Formations')
        ->assertSee('Exit to Dashboard');
});

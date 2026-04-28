<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\Dashboard;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Sales Tax Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
});

it('redirects guests to login', function () {
    $this->get(route('sales-tax.dashboard'))->assertRedirect(route('login'));
});

it('redirects authenticated users without a business to the selector', function () {
    $loneUser = User::factory()->create();

    $this->actingAs($loneUser)
        ->get(route('sales-tax.dashboard'))
        ->assertRedirect(route('portal.select-business'));
});

it('renders the dashboard with the start registration CTA for an authenticated user', function () {
    $this->actingAs($this->user)
        ->withSession(['current_business_id' => $this->business->id]);

    $this->get(route('sales-tax.dashboard'))
        ->assertSuccessful()
        ->assertSee('Sales Tax')
        ->assertSee('Start New Registration')
        ->assertSee('No sales tax registrations yet');
});

it('lists past sales tax registrations for the current business', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $draft = FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA', 'TX'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    foreach ($draft->selected_states as $state) {
        FormApplicationState::create([
            'form_application_id' => $draft->id,
            'state_code' => $state,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    Livewire::test(Dashboard::class)
        ->assertSee('Sales & Use Tax Permit')
        ->assertSee('Draft')
        ->assertSee('Continue')
        ->assertSee('CA');
});

it('does not leak registrations from other businesses', function () {
    $otherBusiness = Business::create([
        'name' => 'Someone Else Inc',
        'onboarding_completed_at' => now(),
    ]);

    FormApplication::create([
        'business_id' => $otherBusiness->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['NY'],
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    Livewire::test(Dashboard::class)
        ->assertSee('No sales tax registrations yet')
        ->assertDontSee('NY');
});

it('uses View label for paid/submitted registrations and locks editing', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    FormApplication::create([
        'business_id' => $this->business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
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

it('limits visible registrations to ten with a hint when more exist', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    for ($i = 0; $i < 11; $i++) {
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
    }

    Livewire::test(Dashboard::class)
        ->assertSet('hasMoreRegistrations', true)
        ->assertSee('Showing the 10 most recent');
});

it('ignores form applications of other types', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

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

    Livewire::test(Dashboard::class)
        ->assertSee('No sales tax registrations yet')
        ->assertDontSee('DE');
});

it('renders the sales tax state selector inside the Sales Tax workspace sidebar', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $response = $this->get(route('forms.start', 'sales_tax_permit'));

    $response->assertSuccessful()
        ->assertSee('Select States')
        // Workspace sidebar markers prove we're inside <x-layouts.workspace key="sales_tax">
        // and not the generic <x-layouts.app> shell.
        ->assertSee('Sales Tax')
        ->assertSee('Exit to Dashboard');
});

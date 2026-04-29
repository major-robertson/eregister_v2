<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

function makeSalesTaxApplication(Business $business, User $creator, array $states): FormApplication
{
    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => [],
        'created_by_user_id' => $creator->id,
        'paid_at' => now(),
        'submitted_at' => now(),
    ]);

    foreach ($states as $stateCode) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $stateCode,
            'status' => 'complete',
            'data' => [],
            'completed_at' => now(),
        ]);
    }

    return $application;
}

describe('access control', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.sales-tax.board'))
            ->assertRedirect(route('login'));
    });

    it('denies users without tax.view permission', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.sales-tax.board'))
            ->assertForbidden();
    });

    it('allows users with tax.view permission', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('tax.view');

        $this->actingAs($admin)
            ->get(route('admin.sales-tax.board'))
            ->assertSuccessful()
            ->assertSee('Sales Tax Registrations Board');
    });
});

describe('default board contents', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('tax.view');
    });

    it('shows a card for each paid application state', function () {
        $business = Business::factory()->create(['name' => 'Acme Co']);
        makeSalesTaxApplication($business, $this->admin, ['CA', 'TX', 'NY']);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertSee('Acme Co')
            ->assertSee('CA')
            ->assertSee('TX')
            ->assertSee('NY');
    });

    it('does not show unpaid applications', function () {
        $business = Business::factory()->create(['name' => 'Unpaid Inc']);

        // Create a draft application without paid_at
        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->admin->id,
        ]);
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertDontSee('Unpaid Inc');
    });

    it('does not leak non-sales-tax form applications onto the board', function () {
        $business = Business::factory()->create(['name' => 'LLC Only Co']);

        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['DE'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $this->admin->id,
            'paid_at' => now(),
        ]);
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'DE',
            'status' => 'complete',
            'data' => [],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertDontSee('LLC Only Co');
    });

    it('hides AwaitingClient and Approved cards from the default board', function () {
        $business = Business::factory()->create(['name' => 'Hidden Co']);
        $application = makeSalesTaxApplication($business, $this->admin, ['CA', 'TX']);

        $application->states->first()->update([
            'current_admin_status' => FormApplicationStateAdminStatus::AwaitingClient,
        ]);
        $application->states->last()->update([
            'current_admin_status' => FormApplicationStateAdminStatus::Approved,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertDontSee('Hidden Co');
    });

    it('shows sibling progress strip for multi-state applications', function () {
        $business = Business::factory()->create(['name' => 'Multi State Inc']);
        makeSalesTaxApplication($business, $this->admin, ['CA', 'TX', 'NY']);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertSee('Multi State Inc')
            ->assertSee('State 1 of 3');
    });

    it('hides sibling progress strip for single-state applications', function () {
        $business = Business::factory()->create(['name' => 'Single State Inc']);
        makeSalesTaxApplication($business, $this->admin, ['CA']);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board'))
            ->assertSee('Single State Inc')
            ->assertDontSee('State 1 of 1');
    });
});

describe('search', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('tax.view');
    });

    it('returns matching businesses when searching', function () {
        $business = Business::factory()->create(['name' => 'Searchable Holdings']);
        makeSalesTaxApplication($business, $this->admin, ['CA']);

        $this->actingAs($this->admin);

        \Livewire\Livewire::test(\App\Domains\Forms\Admin\Livewire\SalesTaxBoard::class)
            ->set('search', 'Searchable')
            ->assertSee('Searchable Holdings');
    });
});

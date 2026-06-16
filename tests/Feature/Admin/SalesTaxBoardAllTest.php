<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

function makeSalesTaxAppForAllTest(Business $business, User $creator, array $states): FormApplication
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
    it('denies users without tax.view permission', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.sales-tax.board-all'))
            ->assertForbidden();
    });

    it('allows users with tax.view permission', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('tax.view');

        $this->actingAs($admin)
            ->get(route('admin.sales-tax.board-all'))
            ->assertSuccessful()
            ->assertSee('Sales Tax Registrations Board');
    });
});

describe('all-statuses board contents', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('tax.view');
    });

    it('shows AwaitingClient cards on the all-statuses board', function () {
        $business = Business::factory()->create(['name' => 'Awaiting Client Co']);
        $application = makeSalesTaxAppForAllTest($business, $this->admin, ['CA']);
        $application->states->first()->update([
            'current_admin_status' => FormApplicationStateAdminStatus::AwaitingClient,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board-all'))
            ->assertSee('Awaiting Client Co');
    });

    it('shows Approved cards on the all-statuses board', function () {
        $business = Business::factory()->create(['name' => 'Approved Co']);
        $application = makeSalesTaxAppForAllTest($business, $this->admin, ['CA']);
        $application->states->first()->update([
            'current_admin_status' => FormApplicationStateAdminStatus::Approved,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.board-all'))
            ->assertSee('Approved Co');
    });
});

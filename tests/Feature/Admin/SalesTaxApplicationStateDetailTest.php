<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Admin\Livewire\SalesTaxApplicationStateDetail;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

function makeSalesTaxStateForDetail(User $creator, string $stateCode = 'CA'): FormApplicationState
{
    $business = Business::factory()->create(['name' => 'Detail Test Co']);

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => [$stateCode],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => ['legal_name' => 'Detail Test LLC'],
        'created_by_user_id' => $creator->id,
        'paid_at' => now(),
        'submitted_at' => now(),
    ]);

    return FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => $stateCode,
        'status' => 'complete',
        'data' => [],
        'completed_at' => now(),
    ]);
}

describe('access control', function () {
    it('denies users without tax.view permission', function () {
        $user = User::factory()->create();
        $state = makeSalesTaxStateForDetail($user);

        $this->actingAs($user)
            ->get(route('admin.sales-tax.states.show', $state))
            ->assertForbidden();
    });

    it('allows users with tax.view permission', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('tax.view');
        $state = makeSalesTaxStateForDetail($admin);

        $this->actingAs($admin)
            ->get(route('admin.sales-tax.states.show', $state))
            ->assertSuccessful()
            ->assertSee('Detail Test Co');
    });
});

describe('detail page rendering', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('tax.view');
        $this->admin->givePermissionTo('tax.change_status');
    });

    it('shows current admin status badge', function () {
        $state = makeSalesTaxStateForDetail($this->admin);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.states.show', $state))
            ->assertSee('New');
    });

    it('shows allowed-next dropdown options', function () {
        $state = makeSalesTaxStateForDetail($this->admin);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.states.show', $state))
            ->assertSee('Needs Review')
            ->assertSee('Submitted')
            ->assertSee('Rejected')
            ->assertSee('Approved');
    });

    it('hides the change-status form when current status is terminal (Approved)', function () {
        $state = makeSalesTaxStateForDetail($this->admin);
        $state->update(['current_admin_status' => FormApplicationStateAdminStatus::Approved]);

        $this->actingAs($this->admin)
            ->get(route('admin.sales-tax.states.show', $state))
            ->assertSee('No further transitions allowed');
    });
});

describe('changeStatus action', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('tax.view');
        $this->admin->givePermissionTo('tax.change_status');
    });

    it('writes a transition row and updates denormalized fields on success', function () {
        $state = makeSalesTaxStateForDetail($this->admin);

        $this->actingAs($this->admin);

        Livewire::test(SalesTaxApplicationStateDetail::class, [
            'formApplicationState' => $state,
        ])
            ->set('newStatus', FormApplicationStateAdminStatus::SubmittedToState->value)
            ->set('comment', 'Filed with state agency portal')
            ->call('changeStatus')
            ->assertHasNoErrors();

        $state->refresh();

        expect($state->current_admin_status)
            ->toBe(FormApplicationStateAdminStatus::SubmittedToState)
            ->and($state->current_admin_status_changed_at)->not->toBeNull()
            ->and($state->transitions()->count())->toBe(1);

        $transition = $state->transitions()->first();
        expect($transition->from_status)->toBe(FormApplicationStateAdminStatus::New)
            ->and($transition->to_status)->toBe(FormApplicationStateAdminStatus::SubmittedToState)
            ->and($transition->comment)->toBe('Filed with state agency portal')
            ->and($transition->changed_by_user_id)->toBe($this->admin->id);
    });

    it('rejects users without tax.change_status permission', function () {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('tax.view'); // can view but not change

        $state = makeSalesTaxStateForDetail($viewer);

        $this->actingAs($viewer);

        Livewire::test(SalesTaxApplicationStateDetail::class, [
            'formApplicationState' => $state,
        ])
            ->set('newStatus', FormApplicationStateAdminStatus::NeedsReview->value)
            ->call('changeStatus')
            ->assertForbidden();

        expect($state->fresh()->current_admin_status)
            ->toBe(FormApplicationStateAdminStatus::New);
    });

    it('rejects an attempt to transition out of a terminal status', function () {
        $state = makeSalesTaxStateForDetail($this->admin);
        $state->update(['current_admin_status' => FormApplicationStateAdminStatus::Approved]);

        $this->actingAs($this->admin);

        Livewire::test(SalesTaxApplicationStateDetail::class, [
            'formApplicationState' => $state,
        ])
            ->set('newStatus', FormApplicationStateAdminStatus::New->value)
            ->call('changeStatus')
            ->assertHasErrors(['newStatus']);
    });

    it('allows SubmittedToState to transition to Rejected (state agency rejection)', function () {
        $state = makeSalesTaxStateForDetail($this->admin);
        $state->update(['current_admin_status' => FormApplicationStateAdminStatus::SubmittedToState]);

        $this->actingAs($this->admin);

        Livewire::test(SalesTaxApplicationStateDetail::class, [
            'formApplicationState' => $state,
        ])
            ->set('newStatus', FormApplicationStateAdminStatus::Rejected->value)
            ->call('changeStatus')
            ->assertHasNoErrors();

        expect($state->fresh()->current_admin_status)
            ->toBe(FormApplicationStateAdminStatus::Rejected);
    });

    it('allows Rejected to transition back into the active workflow', function () {
        $state = makeSalesTaxStateForDetail($this->admin);
        $state->update(['current_admin_status' => FormApplicationStateAdminStatus::Rejected]);

        $this->actingAs($this->admin);

        Livewire::test(SalesTaxApplicationStateDetail::class, [
            'formApplicationState' => $state,
        ])
            ->set('newStatus', FormApplicationStateAdminStatus::SubmittedToState->value)
            ->call('changeStatus')
            ->assertHasNoErrors();

        expect($state->fresh()->current_admin_status)
            ->toBe(FormApplicationStateAdminStatus::SubmittedToState);
    });
});

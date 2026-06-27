<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Admin\Livewire\FormationApplicationStateDetail;
use App\Domains\Forms\Admin\Livewire\FormationsBoard;
use App\Domains\Forms\Admin\Livewire\FormationsBoardAll;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

/**
 * Build a paid LLC formation with its single state row at the given admin
 * status, and return that FormApplicationState (the board card).
 */
function paidLlcFormationState(string $adminStatus = 'new', string $businessName = 'Board Co', bool $paid = true): FormApplicationState
{
    $business = Business::factory()->create(['name' => $businessName]);
    $owner = User::factory()->create();
    $business->users()->attach($owner->id, ['role' => 'owner']);

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['WY'],
        'status' => $paid ? 'submitted' : 'draft',
        'current_phase' => $paid ? 'review' : 'core',
        'core_data' => ['llc_name' => 'Board Co LLC'],
        'created_by_user_id' => $owner->id,
        'paid_at' => $paid ? now() : null,
        'submitted_at' => $paid ? now() : null,
    ]);

    return FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'WY',
        'status' => $paid ? 'complete' : 'pending',
        'current_admin_status' => $adminStatus,
        'completed_at' => $paid ? now() : null,
        'data' => [],
    ]);
}

describe('access control', function () {
    it('allows an admin to view the formations board', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.formations.board'))
            ->assertSuccessful()
            ->assertSee('Formations Board');
    });

    it('allows an llc_agent (llc.view) to view the formations board', function () {
        $agent = User::factory()->create();
        $agent->assignRole('llc_agent');

        $this->actingAs($agent)
            ->get(route('admin.formations.board'))
            ->assertSuccessful();
    });

    it('denies a user without llc.view', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.formations.board'))
            ->assertForbidden();
    });

    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.formations.board'))
            ->assertRedirect(route('login'));
    });
});

describe('board cards', function () {
    it('shows a paid formation in the New column', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        paidLlcFormationState('new', 'Acme Formation Co');

        $this->actingAs($admin);

        Livewire::test(FormationsBoard::class)
            ->assertSee('Acme Formation Co');
    });

    it('does not show unpaid formations', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        paidLlcFormationState('new', 'Unpaid Formation Co', paid: false);

        $this->actingAs($admin);

        Livewire::test(FormationsBoard::class)
            ->assertDontSee('Unpaid Formation Co');
    });

    it('hides AwaitingClient on the default board but shows it on board-all', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        paidLlcFormationState('awaiting_client', 'Parked Formation Co');

        $this->actingAs($admin);

        Livewire::test(FormationsBoard::class)
            ->assertDontSee('Parked Formation Co');

        Livewire::test(FormationsBoardAll::class)
            ->assertSee('Parked Formation Co');
    });
});

describe('status changes', function () {
    it('lets an llc_agent change a formation status and records the transition', function () {
        $agent = User::factory()->create();
        $agent->assignRole('llc_agent');

        $state = paidLlcFormationState('new');

        $this->actingAs($agent);

        Livewire::test(FormationApplicationStateDetail::class, ['formApplicationState' => $state])
            ->set('newStatus', 'submitted_to_state')
            ->call('changeStatus')
            ->assertHasNoErrors();

        $state->refresh();

        expect($state->current_admin_status->value)->toBe('submitted_to_state')
            ->and($state->transitions()->count())->toBe(1);
    });

    it('forbids a viewer (no llc.change_status) from changing status', function () {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer'); // has llc.view but not llc.change_status

        $state = paidLlcFormationState('new');

        $this->actingAs($viewer);

        Livewire::test(FormationApplicationStateDetail::class, ['formApplicationState' => $state])
            ->set('newStatus', 'submitted_to_state')
            ->call('changeStatus')
            ->assertForbidden();

        expect($state->fresh()->current_admin_status->value)->toBe('new');
    });
});

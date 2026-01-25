<?php

use App\Domains\Admin\Livewire\RolesBoard;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Seed permissions and roles
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the roles page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.roles'))
            ->assertSuccessful()
            ->assertSee('Roles Management');
    });

    it('denies non-admin users access to the roles page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.roles'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the roles page', function () {
        $this->get(route('admin.roles'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the roles page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.roles'))
            ->assertForbidden();
    });
});

describe('viewing users with roles', function () {
    it('displays users who have roles', function () {
        $admin = User::factory()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $admin->assignRole('admin');

        $lienAgent = User::factory()->create(['first_name' => 'Lien', 'last_name' => 'Agent']);
        $lienAgent->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->assertSee('Admin User')
            ->assertSee('Lien Agent')
            ->assertSee('lien agent'); // Role badge
    });

    it('does not display users without roles', function () {
        $admin = User::factory()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $admin->assignRole('admin');

        $regularUser = User::factory()->create(['first_name' => 'Regular', 'last_name' => 'User']);

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->assertSee('Admin User')
            ->assertDontSee('Regular User');
    });

    it('can search users by name', function () {
        $admin = User::factory()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $admin->assignRole('admin');

        $lienAgent = User::factory()->create(['first_name' => 'John', 'last_name' => 'Smith']);
        $lienAgent->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->set('search', 'John')
            ->assertSee('John Smith')
            ->assertDontSee('Admin User');
    });

    it('can search users by email', function () {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole('admin');

        $lienAgent = User::factory()->create(['email' => 'agent@company.org', 'first_name' => 'Agent', 'last_name' => 'One']);
        $lienAgent->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->set('search', 'company.org')
            ->assertSee('Agent One')
            ->assertDontSee('admin@example.com');
    });
});

describe('roles and permissions reference', function () {
    it('displays all roles', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->assertSee('admin')
            ->assertSee('lien agent')
            ->assertSee('llc agent')
            ->assertSee('tax agent')
            ->assertSee('viewer');
    });

    it('displays permissions count for each role', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $lienAgentRole = Role::findByName('lien_agent');
        $permissionCount = $lienAgentRole->permissions->count();

        Livewire::test(RolesBoard::class)
            ->assertSee("{$permissionCount} permissions");
    });
});

describe('assigning roles', function () {
    it('can open the assign role modal', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openAssignModal')
            ->assertSet('showAssignModal', true);
    });

    it('can search for users in the assign modal', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create([
            'first_name' => 'Target',
            'last_name' => 'Person',
            'email' => 'target@example.com',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(RolesBoard::class)
            ->call('openAssignModal')
            ->set('assignUserSearch', 'target@example');

        expect($component->get('searchableUsers'))->toHaveCount(1);
        expect($component->get('searchableUsers')->first()->email)->toBe('target@example.com');
    });

    it('can assign a role to a user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create([
            'first_name' => 'Target',
            'last_name' => 'Person',
        ]);

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openAssignModal')
            ->call('selectUser', $targetUser->id)
            ->set('selectedRole', 'lien_agent')
            ->call('assignRole')
            ->assertSet('showAssignModal', false)
            ->assertDispatched('toast');

        expect($targetUser->fresh()->hasRole('lien_agent'))->toBeTrue();
    });

    it('shows warning when user already has the role', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $existingAgent = User::factory()->create();
        $existingAgent->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openAssignModal')
            ->call('selectUser', $existingAgent->id)
            ->set('selectedRole', 'lien_agent')
            ->call('assignRole')
            ->assertDispatched('toast', message: "{$existingAgent->name} already has the lien_agent role.", type: 'warning');
    });

    it('requires both user and role to be selected', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openAssignModal')
            ->call('assignRole')
            ->assertDispatched('toast', type: 'error');
    });
});

describe('editing user roles', function () {
    it('can open the edit roles modal for a user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $lienAgent = User::factory()->create();
        $lienAgent->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openEditModal', $lienAgent->id)
            ->assertSet('editingUserId', $lienAgent->id)
            ->assertSet('userRoles.lien_agent', true)
            ->assertSet('userRoles.admin', false);
    });

    it('can update user roles', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openEditModal', $targetUser->id)
            ->set('userRoles.lien_agent', false)
            ->set('userRoles.llc_agent', true)
            ->call('saveUserRoles')
            ->assertSet('editingUserId', null)
            ->assertDispatched('toast');

        $targetUser->refresh();
        expect($targetUser->hasRole('lien_agent'))->toBeFalse();
        expect($targetUser->hasRole('llc_agent'))->toBeTrue();
    });

    it('can add multiple roles to a user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('lien_agent');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('openEditModal', $targetUser->id)
            ->set('userRoles.llc_agent', true)
            ->set('userRoles.tax_agent', true)
            ->call('saveUserRoles');

        $targetUser->refresh();
        expect($targetUser->hasRole('lien_agent'))->toBeTrue();
        expect($targetUser->hasRole('llc_agent'))->toBeTrue();
        expect($targetUser->hasRole('tax_agent'))->toBeTrue();
    });
});

describe('revoking roles', function () {
    it('can remove all roles from a user', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $targetUser->assignRole('lien_agent');
        $targetUser->assignRole('viewer');

        $this->actingAs($admin);

        Livewire::test(RolesBoard::class)
            ->call('removeAllRoles', $targetUser->id)
            ->assertDispatched('toast');

        $targetUser->refresh();
        expect($targetUser->roles)->toHaveCount(0);
    });
});

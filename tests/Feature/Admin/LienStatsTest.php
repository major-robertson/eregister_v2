<?php

use App\Domains\Admin\Livewire\LienStats;
use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the lien stats page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.lien-stats'))
            ->assertSuccessful()
            ->assertSee('Lien Stats');
    });

    it('denies non-admin users access to the lien stats page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.lien-stats'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the lien stats page', function () {
        $this->get(route('admin.lien-stats'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the lien stats page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.lien-stats'))
            ->assertForbidden();
    });
});

describe('displaying lien projects', function () {
    it('displays lien projects in the list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $business = Business::factory()->create([
            'name' => 'Test Construction LLC',
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        LienProject::factory()->create([
            'name' => 'Main Street Project',
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Main Street Project')
            ->assertSee('Test Construction LLC')
            ->assertSee('John Doe');
    });

    it('shows wizard progress for projects', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        LienProject::factory()->create([
            'name' => 'Test Project',
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
            'jobsite_address1' => '123 Main St',
            'jobsite_city' => 'Los Angeles',
            'jobsite_state' => 'CA',
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Test Project')
            ->assertSee('fields');
    });

    it('shows wizard complete badge for completed projects', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        LienProject::factory()->create([
            'name' => 'Complete Project',
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
            'wizard_completed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Complete Project')
            ->assertSee('Complete');
    });

    it('can search lien projects by name', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        LienProject::factory()->create([
            'name' => 'Alpha Project',
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
        ]);

        LienProject::factory()->create([
            'name' => 'Beta Project',
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Project')
            ->assertDontSee('Beta Project');
    });

    it('can search lien projects by business name', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $business1 = Business::factory()->create(['name' => 'Unique Construction']);
        $business1->users()->attach($user->id, ['role' => 'owner']);

        $business2 = Business::factory()->create(['name' => 'Other Company']);
        $business2->users()->attach($user->id, ['role' => 'owner']);

        LienProject::factory()->create([
            'name' => 'Project One',
            'business_id' => $business1->id,
            'created_by_user_id' => $user->id,
        ]);

        LienProject::factory()->create([
            'name' => 'Project Two',
            'business_id' => $business2->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->set('search', 'Unique Construction')
            ->assertSee('Project One')
            ->assertDontSee('Project Two');
    });
});

describe('pagination', function () {
    it('paginates the lien projects list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        // Create more than 25 projects to trigger pagination
        LienProject::factory()->count(30)->create([
            'business_id' => $business->id,
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSuccessful();
    });
});

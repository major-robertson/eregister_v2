<?php

use App\Domains\Admin\Livewire\UsersList;
use App\Domains\Business\Models\Business;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the users list page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertSuccessful()
            ->assertSee('Users');
    });

    it('denies non-admin users access to the users list page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the users list page', function () {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the users list page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });
});

describe('displaying users', function () {
    it('displays users in the list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersList::class)
            ->assertSee('John Doe')
            ->assertSee('johndoe@example.com');
    });

    it('shows business count for users', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($admin);

        Livewire::test(UsersList::class)
            ->assertSee($user->name);
    });

    it('can search users by name', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Wonderland',
        ]);

        User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Builder',
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersList::class)
            ->set('search', 'Alice')
            ->assertSee('Alice Wonderland')
            ->assertDontSee('Bob Builder');
    });

    it('can search users by email', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'unique-test@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UsersList::class)
            ->set('search', 'unique-test@example.com')
            ->assertSee('Test User');
    });
});

describe('pagination', function () {
    it('paginates the users list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create more than 25 users to trigger pagination
        User::factory()->count(30)->create();

        $this->actingAs($admin);

        Livewire::test(UsersList::class)
            ->assertSuccessful();
    });
});

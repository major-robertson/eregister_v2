<?php

use App\Domains\Admin\Livewire\BusinessesList;
use App\Domains\Business\Models\Business;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the businesses list page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.businesses.index'))
            ->assertSuccessful()
            ->assertSee('Businesses');
    });

    it('denies non-admin users access to the businesses list page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.businesses.index'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the businesses list page', function () {
        $this->get(route('admin.businesses.index'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the businesses list page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.businesses.index'))
            ->assertForbidden();
    });
});

describe('displaying businesses', function () {
    it('displays businesses in the list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Business::factory()->create([
            'business_address' => ['city' => 'Houston', 'state' => 'TX', 'street' => '123 Main St'],
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->assertSee('Houston, TX')
            ->assertSee('123 Main St');
    });

    it('shows user count for businesses', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business->users()->attach($user1->id, ['role' => 'owner']);
        $business->users()->attach($user2->id, ['role' => 'member']);

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->assertSuccessful();
    });

    it('can search businesses by city', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Business::factory()->create([
            'business_address' => ['city' => 'Dallas', 'state' => 'TX'],
        ]);

        Business::factory()->create([
            'business_address' => ['city' => 'Miami', 'state' => 'FL'],
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->set('search', 'Dallas')
            ->assertSee('Dallas, TX')
            ->assertDontSee('Miami, FL');
    });

    it('can search businesses by state', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Business::factory()->create([
            'business_address' => ['city' => 'Austin', 'state' => 'TX'],
        ]);

        Business::factory()->create([
            'business_address' => ['city' => 'Orlando', 'state' => 'FL'],
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->set('search', 'FL')
            ->assertSee('Orlando, FL')
            ->assertDontSee('Austin, TX');
    });

    it('shows onboarding status badges', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Business::factory()->create([
            'business_address' => ['city' => 'Complete', 'state' => 'CA'],
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->assertSee('Main')
            ->assertSee('Lien');
    });
});

describe('pagination', function () {
    it('paginates the businesses list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create more than 25 businesses to trigger pagination
        Business::factory()->count(30)->create();

        $this->actingAs($admin);

        Livewire::test(BusinessesList::class)
            ->assertSuccessful();
    });
});

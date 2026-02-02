<?php

use App\Domains\Admin\Livewire\UserOverview;
use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the user overview page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create([
            'first_name' => 'Target',
            'last_name' => 'User',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $targetUser))
            ->assertSuccessful()
            ->assertSee('Target User');
    });

    it('denies non-admin users access to the user overview page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $targetUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.show', $targetUser))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the user overview page', function () {
        $targetUser = User::factory()->create();

        $this->get(route('admin.users.show', $targetUser))
            ->assertRedirect(route('login'));
    });
});

describe('displaying user details', function () {
    it('displays user information', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'janesmith@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $targetUser])
            ->assertSee('Jane Smith')
            ->assertSee('janesmith@example.com')
            ->assertSee('User Details');
    });

    it('displays businesses the user is assigned to', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $business = Business::factory()->create([
            'business_address' => ['city' => 'Austin', 'state' => 'TX'],
        ]);
        $business->users()->attach($targetUser->id, ['role' => 'owner']);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $targetUser])
            ->assertSee('Austin, TX')
            ->assertSee('Owner');
    });

    it('displays payments made by businesses the user belongs to', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $business = Business::factory()->create();
        $business->users()->attach($targetUser->id, ['role' => 'owner']);

        Payment::create([
            'business_id' => $business->id,
            'purchasable_type' => LienFiling::class,
            'purchasable_id' => 1,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => 19900,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $targetUser])
            ->assertSee('$199.00');
    });

    it('shows 2FA status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $userWith2FA = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $userWith2FA])
            ->assertSee('Enabled');
    });

    it('shows email verification status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $verifiedUser])
            ->assertSee('Verified');
    });
});

describe('summary cards', function () {
    it('displays business count', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $targetUser = User::factory()->create();
        $business1 = Business::factory()->create();
        $business2 = Business::factory()->create();
        $business1->users()->attach($targetUser->id, ['role' => 'owner']);
        $business2->users()->attach($targetUser->id, ['role' => 'member']);

        $this->actingAs($admin);

        Livewire::test(UserOverview::class, ['user' => $targetUser])
            ->assertSee('Businesses')
            ->assertSee('2');
    });
});

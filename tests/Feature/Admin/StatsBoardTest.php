<?php

use App\Domains\Admin\Livewire\StatsBoard;
use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    // Seed permissions and roles
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the stats page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.stats'))
            ->assertSuccessful()
            ->assertSee('Stats Dashboard');
    });

    it('denies non-admin users access to the stats page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.stats'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the stats page', function () {
        $this->get(route('admin.stats'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the stats page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.stats'))
            ->assertForbidden();
    });
});

describe('displaying signup stats', function () {
    it('displays signup counts for today', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create users for today
        User::factory()->count(3)->create(['created_at' => now()]);

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Signups')
            ->assertSee('Today');
    });

    it('displays the last 20 signups table', function () {
        $admin = User::factory()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $admin->assignRole('admin');

        $recentUser = User::factory()->create([
            'first_name' => 'Recent',
            'last_name' => 'Signup',
            'email' => 'recent@example.com',
        ]);

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Last 20 Signups')
            ->assertSee('Recent Signup')
            ->assertSee('recent@example.com');
    });
});

describe('displaying payment stats', function () {
    it('displays the payments section', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Payments')
            ->assertSee('Last 20 Payments');
    });

    it('shows recent payments with user info', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $payer = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Payer',
            'email' => 'payer@example.com',
        ]);
        $business->users()->attach($payer->id, ['role' => 'owner']);

        Payment::create([
            'business_id' => $business->id,
            'purchasable_type' => LienFiling::class,
            'purchasable_id' => 1,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => 9900,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('John Payer')
            ->assertSee('payer@example.com')
            ->assertSee('$99.00');
    });
});

describe('displaying subscription stats', function () {
    it('displays the subscriptions section', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Subscriptions')
            ->assertSee('Last 20 Subscriptions');
    });
});

describe('displaying lien filing stats', function () {
    it('displays lien filings paid section', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Lien Filings Paid');
    });
});

describe('user business info in signups table', function () {
    it('shows state from business address', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create([
            'business_address' => ['state' => 'TX', 'city' => 'Austin'],
        ]);

        $userWithBusiness = User::factory()->create([
            'first_name' => 'Texas',
            'last_name' => 'User',
        ]);
        $business->users()->attach($userWithBusiness->id, ['role' => 'owner']);

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('Texas User')
            ->assertSee('TX');
    });

    it('shows None for users without business address state', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $userWithoutBusiness = User::factory()->create([
            'first_name' => 'No',
            'last_name' => 'Business',
        ]);

        $this->actingAs($admin);

        Livewire::test(StatsBoard::class)
            ->assertSee('No Business')
            ->assertSee('None');
    });
});

<?php

use App\Domains\Admin\Livewire\BusinessOverview;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
});

describe('access control', function () {
    it('allows admin to access the business overview page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create([
            'business_address' => ['city' => 'Test City', 'state' => 'CA'],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.businesses.show', $business))
            ->assertSuccessful()
            ->assertSee('Test City, CA');
    });

    it('denies non-admin users access to the business overview page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $business = Business::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.businesses.show', $business))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the business overview page', function () {
        $business = Business::factory()->create();

        $this->get(route('admin.businesses.show', $business))
            ->assertRedirect(route('login'));
    });
});

describe('displaying business details', function () {
    it('displays business address information', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create([
            'business_address' => ['city' => 'San Francisco', 'state' => 'CA', 'street' => '456 Market St'],
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('San Francisco, CA')
            ->assertSee('456 Market St');
    });

    it('displays users assigned to the business', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $user = User::factory()->create([
            'first_name' => 'Member',
            'last_name' => 'User',
            'email' => 'member@example.com',
        ]);
        $business->users()->attach($user->id, ['role' => 'owner']);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Member User')
            ->assertSee('member@example.com')
            ->assertSee('Owner');
    });

    it('displays payments for the business', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        Payment::create([
            'business_id' => $business->id,
            'purchasable_type' => LienFiling::class,
            'purchasable_id' => 1,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => 29900,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('$299.00');
    });

    it('displays form applications for the business', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['role' => 'owner']);

        FormApplication::factory()->create([
            'business_id' => $business->id,
            'form_type' => 'llc_formation',
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Llc formation');
    });

    it('displays lien projects for the business', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        LienProject::withoutGlobalScopes()->create([
            'business_id' => $business->id,
            'name' => 'Test Lien Project',
            'jobsite_city' => 'Los Angeles',
            'jobsite_state' => 'CA',
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Test Lien Project')
            ->assertSee('Los Angeles, CA');
    });
});

describe('onboarding status', function () {
    it('shows complete onboarding status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Main Onboarding')
            ->assertSee('Lien Onboarding')
            ->assertSee('Complete');
    });

    it('shows incomplete onboarding status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create([
            'onboarding_completed_at' => null,
            'lien_onboarding_completed_at' => null,
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Incomplete')
            ->assertSee('Not Started');
    });
});

describe('summary cards', function () {
    it('displays user count', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $business->users()->attach($user1->id, ['role' => 'owner']);
        $business->users()->attach($user2->id, ['role' => 'member']);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Users')
            ->assertSee('2');
    });

    it('displays total payment amount', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        Payment::create([
            'business_id' => $business->id,
            'purchasable_type' => LienFiling::class,
            'purchasable_id' => 1,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => 10000,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);

        Payment::create([
            'business_id' => $business->id,
            'purchasable_type' => LienFiling::class,
            'purchasable_id' => 2,
            'provider' => 'stripe',
            'livemode' => false,
            'amount_cents' => 15000,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(BusinessOverview::class, ['business' => $business])
            ->assertSee('Total Paid')
            ->assertSee('$250.00');
    });
});

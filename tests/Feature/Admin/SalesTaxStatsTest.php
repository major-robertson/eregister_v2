<?php

use App\Domains\Admin\Livewire\SalesTaxStats;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Livewire\Livewire;

function makeTaxPrice(): Price
{
    return Price::create([
        'product_family' => 'tax',
        'product_key' => 'sales_tax_permit',
        'variant_key' => 'per_state',
        'billing_type' => 'one_time',
        'amount_cents' => 19900,
        'currency' => 'usd',
        'active' => true,
    ]);
}

function makeLienPrice(): Price
{
    return Price::create([
        'product_family' => 'lien',
        'product_key' => 'lien_filing',
        'variant_key' => 'default',
        'billing_type' => 'one_time',
        'amount_cents' => 29900,
        'currency' => 'usd',
        'active' => true,
    ]);
}

describe('access control', function () {
    it('allows admin to access the sales tax stats page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.sales-tax-stats'))
            ->assertSuccessful()
            ->assertSee('Sales Tax Stats');
    });

    it('denies non-admin users access to the sales tax stats page', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.sales-tax-stats'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the sales tax stats page', function () {
        $this->get(route('admin.sales-tax-stats'))
            ->assertRedirect(route('login'));
    });

    it('denies users without any role access to the sales tax stats page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.sales-tax-stats'))
            ->assertForbidden();
    });
});

describe('revenue stats', function () {
    it('only counts tax-family succeeded payments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $taxPrice = makeTaxPrice();
        $lienPrice = makeLienPrice();

        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $taxPrice->id,
            'amount_cents' => 19900,
        ]);

        // Lien payment must NOT be included in tax revenue.
        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $lienPrice->id,
            'amount_cents' => 29900,
        ]);

        $this->actingAs($admin);

        Livewire::test(SalesTaxStats::class)
            ->assertSee('Revenue')
            ->assertSee('$199.00')
            ->assertDontSee('$498.00');
    });

    it('excludes non-succeeded tax payments from revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $taxPrice = makeTaxPrice();

        Payment::factory()->create([
            'business_id' => $business->id,
            'price_id' => $taxPrice->id,
            'amount_cents' => 19900,
            'status' => PaymentStatus::Initiated,
        ]);

        $this->actingAs($admin);

        Livewire::test(SalesTaxStats::class)
            ->assertSee('$0.00');
    });
});

describe('recent registrations', function () {
    it('lists paid sales tax registrations in the recent table', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Taxable Co']);
        $owner = User::factory()->create([
            'first_name' => 'Reg',
            'last_name' => 'Owner',
            'email' => 'reg@example.com',
        ]);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA', 'TX'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $owner->id,
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(SalesTaxStats::class)
            ->assertSee('Last 20 Registrations')
            ->assertSee('Taxable Co')
            ->assertSee('Reg Owner')
            ->assertSee('reg@example.com');
    });

    it('does not list non-sales-tax form applications', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'LLC Only Co']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['DE'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $admin->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(SalesTaxStats::class)
            ->assertDontSee('LLC Only Co');
    });
});

describe('wizard progress', function () {
    it('reports partial step progress for a draft registration', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Draft Co']);

        // current_step_key 'activity' is the 3rd core step (index 2), so two
        // steps are considered done regardless of the total step count.
        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['NY'],
            'status' => 'draft',
            'current_phase' => 'core',
            'current_step_key' => 'activity',
            'current_state_index' => 0,
            'core_data' => [],
            'created_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $registrations = Livewire::test(SalesTaxStats::class)
            ->assertSee('Core')
            ->viewData('recentRegistrations');

        $progress = $registrations->first()['progress'];

        expect($progress['done'])->toBe(2)
            ->and($progress['done'])->toBeLessThan($progress['total']);
    });

    it('reports full step progress for a submitted registration', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Done Co']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['NY'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $admin->id,
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin);

        $registrations = Livewire::test(SalesTaxStats::class)
            ->viewData('recentRegistrations');

        $progress = $registrations->first()['progress'];

        expect($progress['done'])->toBe($progress['total'])
            ->and($progress['total'])->toBeGreaterThan(0);
    });
});

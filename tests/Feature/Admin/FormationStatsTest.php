<?php

use App\Domains\Admin\Livewire\FormationStats;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Livewire\Livewire;

function makeFormationPrice(): Price
{
    return Price::create([
        'product_family' => 'formation',
        'product_key' => 'llc',
        'variant_key' => 'membership',
        'billing_type' => 'subscription',
        'amount_cents' => 29900,
        'currency' => 'usd',
        'active' => true,
    ]);
}

function makeFormationLienPrice(): Price
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
    it('allows admin to access the formation stats page', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.formation-stats'))
            ->assertSuccessful()
            ->assertSee('Formation Stats');
    });

    it('denies non-admin users access to the formation stats page', function () {
        $user = User::factory()->create();
        $user->assignRole('llc_agent');

        $this->actingAs($user)
            ->get(route('admin.formation-stats'))
            ->assertForbidden();
    });

    it('denies unauthenticated users access to the formation stats page', function () {
        $this->get(route('admin.formation-stats'))
            ->assertRedirect(route('login'));
    });
});

describe('revenue stats', function () {
    it('only counts formation-family succeeded payments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $formationPrice = makeFormationPrice();
        $lienPrice = makeFormationLienPrice();

        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $formationPrice->id,
            'amount_cents' => 39900,
        ]);

        // Lien payment must NOT be included in formation revenue.
        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $lienPrice->id,
            'amount_cents' => 29900,
        ]);

        $this->actingAs($admin);

        Livewire::test(FormationStats::class)
            ->assertSee('Revenue')
            ->assertSee('$399.00')
            ->assertDontSee('$698.00');
    });

    it('excludes non-succeeded formation payments from revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $formationPrice = makeFormationPrice();

        Payment::factory()->create([
            'business_id' => $business->id,
            'price_id' => $formationPrice->id,
            'amount_cents' => 39900,
            'status' => PaymentStatus::Initiated,
        ]);

        $this->actingAs($admin);

        Livewire::test(FormationStats::class)
            ->assertSee('$0.00');
    });
});

describe('recent formations', function () {
    it('lists paid LLC formations in the recent table', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Formed Co']);
        $owner = User::factory()->create([
            'first_name' => 'Form',
            'last_name' => 'Owner',
            'email' => 'form@example.com',
        ]);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['WY'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $owner->id,
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(FormationStats::class)
            ->assertSee('Last 20 Formations')
            ->assertSee('Formed Co')
            ->assertSee('Form Owner')
            ->assertSee('form@example.com');
    });

    it('does not list non-llc form applications', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Sales Tax Only Co']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $admin->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(FormationStats::class)
            ->assertDontSee('Sales Tax Only Co');
    });
});

describe('wizard progress', function () {
    it('reports partial step progress for a draft formation', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Draft Formation Co']);

        // current_step_key 'members' is the 3rd core step (index 2), so two
        // steps are considered done regardless of the total step count.
        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['WY'],
            'status' => 'draft',
            'current_phase' => 'core',
            'current_step_key' => 'members',
            'current_state_index' => 0,
            'core_data' => [],
            'created_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $formations = Livewire::test(FormationStats::class)
            ->viewData('recentFormations');

        $progress = $formations->first()['progress'];

        expect($progress['done'])->toBe(2)
            ->and($progress['done'])->toBeLessThan($progress['total']);
    });

    it('reports full step progress for a submitted formation', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Done Formation Co']);

        FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['WY'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $admin->id,
            'paid_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin);

        $formations = Livewire::test(FormationStats::class)
            ->viewData('recentFormations');

        $progress = $formations->first()['progress'];

        expect($progress['done'])->toBe($progress['total'])
            ->and($progress['total'])->toBeGreaterThan(0);
    });
});

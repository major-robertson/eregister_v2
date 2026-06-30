<?php

use App\Domains\Admin\Livewire\LienStats;
use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Livewire\Livewire;

function makeLienStatsLienPrice(): Price
{
    return Price::create([
        'product_family' => 'lien',
        'product_key' => 'demand_letter',
        'variant_key' => 'default',
        'billing_type' => 'one_time',
        'amount_cents' => 29900,
        'currency' => 'usd',
        'active' => true,
    ]);
}

function makeLienStatsTaxPrice(): Price
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

describe('revenue stats', function () {
    it('only counts lien-family succeeded payments', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $lienPrice = makeLienStatsLienPrice();
        $taxPrice = makeLienStatsTaxPrice();

        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $lienPrice->id,
            'amount_cents' => 29900,
        ]);

        // Tax payment must NOT be included in lien revenue.
        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => $taxPrice->id,
            'amount_cents' => 19900,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Revenue')
            ->assertSee('$299.00')
            ->assertDontSee('$498.00');
    });

    it('excludes non-succeeded lien payments from revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        $lienPrice = makeLienStatsLienPrice();

        Payment::factory()->create([
            'business_id' => $business->id,
            'price_id' => $lienPrice->id,
            'amount_cents' => 29900,
            'status' => PaymentStatus::Initiated,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('$0.00');
    });
});

describe('recent filings', function () {
    it('lists lien filings with payer, document type, and status', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Filing Co']);
        $owner = User::factory()->create([
            'first_name' => 'Lien',
            'last_name' => 'Owner',
            'email' => 'lien@example.com',
        ]);
        $business->users()->attach($owner->id, ['role' => 'owner']);

        // Unique slug avoids colliding with document types seeded in the
        // shared test database.
        $docType = LienDocumentType::create([
            'slug' => 'lien_stats_test_doc',
            'name' => 'Lien Stats Test Document',
            'is_active' => true,
        ]);

        LienFiling::factory()->paid()->create([
            'business_id' => $business->id,
            'document_type_id' => $docType->id,
            'jurisdiction_state' => 'CA',
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Last 20 Filings')
            ->assertSee('Filing Co')
            ->assertSee('Lien Owner')
            ->assertSee('lien@example.com')
            ->assertSee('Lien Stats Test Document')
            ->assertSee('Submitted'); // FilingStatus::Paid label
    });
});

describe('filing stats', function () {
    it('displays started and paid filing cards', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Filings Started')
            ->assertSee('Filings Paid');
    });

    it('counts started and paid filings for this month', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        // A paid filing (counts toward both started and paid).
        LienFiling::factory()->paid()->create([
            'business_id' => $business->id,
        ]);

        // A draft filing (counts toward started only).
        LienFiling::factory()->draft()->create([
            'business_id' => $business->id,
        ]);

        $this->actingAs($admin);

        $stats = Livewire::test(LienStats::class)->viewData('filingStats');

        expect($stats['started']['this_month'])->toBe(2)
            ->and($stats['paid']['this_month'])->toBe(1);
    });
});

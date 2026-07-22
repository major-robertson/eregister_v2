<?php

use App\Domains\Admin\Livewire\LienStats;
use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienWaiver;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Livewire\Livewire;

/** The lien-waiver subscription prices ship with a migration, so just resolve. */
function lienStatsWaiverPrice(string $variant = 'monthly'): Price
{
    return Price::resolve('lien', 'lien_waiver', $variant, 'subscription');
}

/** An active stub lien-waiver subscription (no Stripe calls). */
function lienStatsSubscribe(Business $business, int $seats = 1, array $overrides = []): void
{
    $business->subscriptions()->create(array_merge([
        'type' => config('lien_waivers.subscription_type'),
        'stripe_id' => 'stub_'.uniqid(),
        'stripe_status' => 'active',
        'stripe_price' => 'stub_price',
        'quantity' => $seats,
    ], $overrides));
}

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

describe('waiver revenue', function () {
    it('reports waiver subscription revenue separately from total lien revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => makeLienStatsLienPrice()->id,
            'amount_cents' => 29900,
        ]);

        Payment::factory()->succeeded()->create([
            'business_id' => $business->id,
            'price_id' => lienStatsWaiverPrice()->id,
            'amount_cents' => 9900,
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(LienStats::class);

        // Waiver revenue is a subset: the filing charge is excluded from it,
        // but both roll up into the family-wide revenue card.
        expect($component->viewData('waiverRevenueStats')['this_month'])->toBe(9900)
            ->and($component->viewData('revenueStats')['this_month'])->toBe(39800);
    });

    it('excludes non-succeeded waiver payments from waiver revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Payment::factory()->create([
            'business_id' => Business::factory()->create()->id,
            'price_id' => lienStatsWaiverPrice()->id,
            'amount_cents' => 9900,
            'status' => PaymentStatus::Initiated,
        ]);

        $this->actingAs($admin);

        expect(Livewire::test(LienStats::class)->viewData('waiverRevenueStats')['this_month'])->toBe(0);
    });
});

describe('waiver volume', function () {
    it('counts waivers created, sent, and signed for this month', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        LienWaiver::factory()->forBusiness($business)->create();
        LienWaiver::factory()->forBusiness($business)->signed()->create(['sent_at' => now()->subHour()]);

        $this->actingAs($admin);

        $stats = Livewire::test(LienStats::class)->viewData('waiverStats');

        expect($stats['created']['this_month'])->toBe(2)
            ->and($stats['sent']['this_month'])->toBe(1)
            ->and($stats['signed']['this_month'])->toBe(1);
    });

    it('still counts a soft-deleted waiver as created, but hides it from the recent list', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();
        LienWaiver::factory()->forBusiness($business)->create()->delete();

        $this->actingAs($admin);

        $component = Livewire::test(LienStats::class);

        // Saving consumed a free-tier slot, so deleting must not erase the
        // volume history — but the table only lists live records.
        expect($component->viewData('waiverStats')['created']['this_month'])->toBe(1)
            ->and($component->viewData('recentWaivers'))->toHaveCount(0);
    });

    it('counts waivers from every business, not just the admin current one', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $adminBusiness = Business::factory()->create();
        $adminBusiness->users()->attach($admin->id, ['role' => 'owner']);

        LienWaiver::factory()->forBusiness(Business::factory()->create())->create();

        $this->actingAs($admin);
        session(['current_business_id' => $adminBusiness->id]);

        $component = Livewire::test(LienStats::class);

        expect($component->viewData('waiverStats')['created']['this_month'])->toBe(1)
            ->and($component->viewData('recentWaivers'))->toHaveCount(1);
    });
});

describe('waiver pipeline and mix', function () {
    it('breaks waivers down by status, direction, source, and state', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create();

        LienWaiver::factory()->forBusiness($business)->inState('TX')->create();
        LienWaiver::factory()->forBusiness($business)->inState('TX')->signed()->create();
        LienWaiver::factory()->forBusiness($business)->inState('CA')->collect()->create([
            'source' => 'uploaded',
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(LienStats::class);

        $pipeline = collect($component->viewData('waiverPipeline'))->keyBy('label');
        $mix = $component->viewData('waiverMix');
        $directions = collect($mix['directions'])->keyBy('label');

        expect($pipeline[WaiverStatus::Draft->label()]['count'])->toBe(2)
            ->and($pipeline[WaiverStatus::Signed->label()]['count'])->toBe(1)
            ->and($directions[WaiverDirection::Provide->label()]['count'])->toBe(2)
            ->and($directions[WaiverDirection::Collect->label()]['count'])->toBe(1)
            ->and($mix['sources']['generated'])->toBe(2)
            ->and($mix['sources']['uploaded'])->toBe(1)
            ->and($mix['top_states']->all())->toBe(['TX' => 2, 'CA' => 1]);
    });
});

describe('waiver subscriptions', function () {
    it('sums active seats and monthly recurring revenue', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        lienStatsSubscribe(Business::factory()->create(), seats: 3);
        lienStatsSubscribe(Business::factory()->create(), seats: 1);

        $this->actingAs($admin);

        $stats = Livewire::test(LienStats::class)->viewData('waiverSubscriptionStats');

        // Stub prices fall back to the configured monthly amount ($99/seat).
        expect($stats['active'])->toBe(2)
            ->and($stats['seats'])->toBe(4)
            ->and($stats['mrr_cents'])->toBe(4 * 9900)
            ->and($stats['new_this_month'])->toBe(2)
            ->and($stats['cancelling'])->toBe(0);
    });

    it('normalizes a yearly plan to a twelfth of its price in MRR', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        lienStatsSubscribe(Business::factory()->create(), seats: 2, overrides: [
            'stripe_price' => lienStatsWaiverPrice('yearly')->stripe_price_id_test,
        ]);

        $this->actingAs($admin);

        $stats = Livewire::test(LienStats::class)->viewData('waiverSubscriptionStats');

        expect($stats['mrr_cents'])->toBe(2 * (int) round(99000 / 12));
    });

    it('counts a grace-period subscription as cancelling but still active', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        lienStatsSubscribe(Business::factory()->create(), overrides: [
            'ends_at' => now()->addWeek(),
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(LienStats::class);
        $stats = $component->viewData('waiverSubscriptionStats');

        expect($stats['cancelling'])->toBe(1)
            ->and($stats['active'])->toBe(1)
            ->and($component->viewData('waiverSubscriptionRows')->first()['status_label'])->toBe('Cancelling');
    });

    it('leaves an ended subscription out of the active rollup', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        lienStatsSubscribe(Business::factory()->create(), seats: 5, overrides: [
            'stripe_status' => 'canceled',
            'ends_at' => now()->subDay(),
        ]);

        $this->actingAs($admin);

        $component = Livewire::test(LienStats::class);
        $stats = $component->viewData('waiverSubscriptionStats');

        expect($stats['active'])->toBe(0)
            ->and($stats['seats'])->toBe(0)
            ->and($stats['mrr_cents'])->toBe(0)
            ->and($component->viewData('waiverSubscriptionRows')->first()['status_label'])->toBe('Ended');
    });
});

describe('recent waivers', function () {
    it('lists waivers with the account, author, and document details', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $business = Business::factory()->create(['name' => 'Waiver Co']);
        $author = User::factory()->create([
            'first_name' => 'Wanda',
            'last_name' => 'Waiver',
            'email' => 'wanda@example.com',
        ]);
        $business->users()->attach($author->id, ['role' => 'owner']);

        LienWaiver::factory()->forBusiness($business)->inState('GA')->create([
            'created_by_user_id' => $author->id,
            'amount_cents' => 250000,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Last 20 Waivers')
            ->assertSee('Waiver Co')
            ->assertSee('Wanda Waiver')
            ->assertSee('wanda@example.com')
            ->assertSee('GA')
            ->assertSee('$2,500.00')
            ->assertSee('Conditional · Progress', escape: false);
    });

    it('renders the waiver sections with empty states when there is nothing yet', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        Livewire::test(LienStats::class)
            ->assertSee('Lien Waivers')
            ->assertSee('Waiver Revenue')
            ->assertSee('Waivers Created')
            ->assertSee('Waivers Signed')
            ->assertSee('Waiver Subscriptions')
            ->assertSee('Waiver Pipeline')
            ->assertSee('Waiver Mix')
            ->assertSee('No waiver subscriptions yet.')
            ->assertSee('No waivers yet.');
    });
});

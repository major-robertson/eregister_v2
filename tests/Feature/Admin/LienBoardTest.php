<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Enums\SearchMode;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

describe('access control', function () {
    it('allows users with lien.view permission to access the board', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin)
            ->get(route('admin.liens.board'))
            ->assertSuccessful()
            ->assertSee('Lien Filings Board');
    });

    it('denies users without lien.view permission', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.liens.board'))
            ->assertForbidden();
    });

    it('denies unauthenticated users', function () {
        $this->get(route('admin.liens.board'))
            ->assertRedirect(route('login'));
    });
});

describe('board cards display', function () {
    it('displays business name on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Acme Construction']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Acme Construction');
    });

    it('displays filed by name and email on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $filer = User::factory()->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
        ]);

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'created_by_user_id' => $filer->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Jane Doe')
            ->assertSee('jane@example.com');
    });

    it('displays property address on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'jobsite_address1' => '123 Main St',
            'jobsite_city' => 'Louisville',
            'jobsite_state' => 'KY',
            'jobsite_zip' => '40202',
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('123 Main St');
    });

    it('displays abbreviated property state on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'jobsite_state' => 'NJ',
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('NJ');
    });

    it('displays document type and service level on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'service_level' => ServiceLevel::FullService,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Full Service');
    });

    it('displays current status on the card', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::InFulfillment,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('In Fulfillment');
    });

    it('displays most recent comment limited to 200 chars', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'note_added',
            'payload_json' => ['comment' => 'Missing notarization on page 3.'],
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Missing notarization on page 3.');
    });

    it('places awaiting_client filings in the Awaiting Client column', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Waiting Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::AwaitingClient,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Awaiting Client')
            ->assertSee('Waiting Co');
    });

    it('places awaiting_esign filings in the Awaiting Signatures column', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Signing Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::AwaitingEsign,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Awaiting Signatures')
            ->assertSee('Signing Co');
    });

    it('places awaiting_notary filings in the Awaiting Signatures column', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Notary Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::AwaitingNotary,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSee('Awaiting Signatures')
            ->assertSee('Notary Co');
    });

    it('filters by business name when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $matchBusiness = Business::factory()->create(['name' => 'Skyline Builders']);
        $matchProject = LienProject::factory()->create(['business_id' => $matchBusiness->id]);
        LienFiling::factory()->forProject($matchProject)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'Ocean Plumbing']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Skyline')
            ->assertSee('Skyline Builders')
            ->assertDontSee('Ocean Plumbing');
    });

    it('filters by filer email when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $filer = User::factory()->create(['email' => 'uniquefiler@test.com']);

        $business = Business::factory()->create(['name' => 'Email Match Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'created_by_user_id' => $filer->id,
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'No Match Co']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'uniquefiler')
            ->assertSee('Email Match Co')
            ->assertDontSee('No Match Co');
    });

    it('filters by property address when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Address Match Co']);
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'jobsite_address1' => '742 Evergreen Terrace',
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'Other Address Co']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Evergreen')
            ->assertSee('Address Match Co')
            ->assertDontSee('Other Address Co');
    });

    it('shows all filings when search is empty', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business1 = Business::factory()->create(['name' => 'Alpha Corp']);
        $project1 = LienProject::factory()->create(['business_id' => $business1->id]);
        LienFiling::factory()->forProject($project1)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $business2 = Business::factory()->create(['name' => 'Beta LLC']);
        $project2 = LienProject::factory()->create(['business_id' => $business2->id]);
        LienFiling::factory()->forProject($project2)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', '')
            ->assertSee('Alpha Corp')
            ->assertSee('Beta LLC');
    });

    it('does not show draft, canceled, or refunded filings', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Draft Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Draft,
        ]);

        $canceledBusiness = Business::factory()->create(['name' => 'Canceled Co']);
        $canceledProject = LienProject::factory()->create(['business_id' => $canceledBusiness->id]);
        LienFiling::factory()->forProject($canceledProject)->create([
            'status' => FilingStatus::Canceled,
        ]);

        $refundedBusiness = Business::factory()->create(['name' => 'Refunded Co']);
        $refundedProject = LienProject::factory()->create(['business_id' => $refundedBusiness->id]);
        LienFiling::factory()->forProject($refundedProject)->create([
            'status' => FilingStatus::Refunded,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertDontSee('Draft Co')
            ->assertDontSee('Canceled Co')
            ->assertDontSee('Refunded Co');
    });
});

describe('board search shows all statuses', function () {
    it('shows filings from all statuses when searching in board mode', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $draftBusiness = Business::factory()->create(['name' => 'Searchable Draft']);
        $draftProject = LienProject::factory()->create(['business_id' => $draftBusiness->id]);
        LienFiling::factory()->forProject($draftProject)->draft()->create();

        $completeBusiness = Business::factory()->create(['name' => 'Searchable Complete']);
        $completeProject = LienProject::factory()->create(['business_id' => $completeBusiness->id]);
        LienFiling::factory()->forProject($completeProject)->complete()->create();

        $canceledBusiness = Business::factory()->create(['name' => 'Searchable Canceled']);
        $canceledProject = LienProject::factory()->create(['business_id' => $canceledBusiness->id]);
        LienFiling::factory()->forProject($canceledProject)->create([
            'status' => FilingStatus::Canceled,
        ]);

        $paidBusiness = Business::factory()->create(['name' => 'Searchable Paid']);
        $paidProject = LienProject::factory()->create(['business_id' => $paidBusiness->id]);
        LienFiling::factory()->forProject($paidProject)->paid()->create();

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Searchable')
            ->assertSee('Searchable Draft')
            ->assertSee('Searchable Complete')
            ->assertSee('Searchable Canceled')
            ->assertSee('Searchable Paid');
    });

    it('shows all FilingStatus column headings when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'anything')
            ->assertSee('Draft')
            ->assertSee('Complete')
            ->assertSee('Canceled')
            ->assertSee('Refunded');
    });
});

describe('search modes', function () {
    it('sets search mode to businesses when searchBusinesses is called', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchBusinesses')
            ->assertSet('searchMode', SearchMode::Businesses);
    });

    it('sets search mode to liens when searchLiens is called', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchLiens')
            ->assertSet('searchMode', SearchMode::Liens);
    });

    it('finds businesses by name in business search mode', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        Business::factory()->create(['name' => 'Skyline Builders']);
        Business::factory()->create(['name' => 'Ocean Plumbing']);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Skyline')
            ->call('searchBusinesses')
            ->assertSee('Skyline Builders')
            ->assertDontSee('Ocean Plumbing');
    });

    it('finds businesses by associated user email in business search mode', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $user = User::factory()->create(['email' => 'findme@unique.com']);
        $business = Business::factory()->create(['name' => 'User Match Corp']);
        $business->users()->attach($user);

        Business::factory()->create(['name' => 'No Match Corp']);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'findme@unique')
            ->call('searchBusinesses')
            ->assertSee('User Match Corp')
            ->assertDontSee('No Match Corp');
    });

    it('finds all lien filings regardless of status in lien search mode', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $draftBusiness = Business::factory()->create(['name' => 'Draft Biz']);
        $draftProject = LienProject::factory()->create(['business_id' => $draftBusiness->id]);
        LienFiling::factory()->forProject($draftProject)->create([
            'status' => FilingStatus::Draft,
        ]);

        $completeBusiness = Business::factory()->create(['name' => 'Complete Biz']);
        $completeProject = LienProject::factory()->create(['business_id' => $completeBusiness->id]);
        LienFiling::factory()->forProject($completeProject)->complete()->create();

        $canceledBusiness = Business::factory()->create(['name' => 'Canceled Biz']);
        $canceledProject = LienProject::factory()->create(['business_id' => $canceledBusiness->id]);
        LienFiling::factory()->forProject($canceledProject)->create([
            'status' => FilingStatus::Canceled,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Biz')
            ->call('searchLiens')
            ->assertSee('Draft Biz')
            ->assertSee('Complete Biz')
            ->assertSee('Canceled Biz');
    });

    it('filters lien results by business name', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $matchBusiness = Business::factory()->create(['name' => 'Exact Match LLC']);
        $matchProject = LienProject::factory()->create(['business_id' => $matchBusiness->id]);
        LienFiling::factory()->forProject($matchProject)->paid()->create();

        $otherBusiness = Business::factory()->create(['name' => 'Other Company']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->paid()->create();

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'Exact Match')
            ->call('searchLiens')
            ->assertSee('Exact Match LLC')
            ->assertDontSee('Other Company');
    });

    it('filters lien results by creator email', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $filer = User::factory()->create(['email' => 'special-filer@test.com']);
        $business = Business::factory()->create(['name' => 'Filer Match Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->paid()->create([
            'created_by_user_id' => $filer->id,
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'No Filer Match']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->paid()->create();

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'special-filer')
            ->call('searchLiens')
            ->assertSee('Filer Match Co')
            ->assertDontSee('No Filer Match');
    });

    it('filters lien results by project address', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Address Lien Co']);
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'jobsite_address1' => '999 Unique Blvd',
        ]);
        LienFiling::factory()->forProject($project)->paid()->create();

        $otherBusiness = Business::factory()->create(['name' => 'Other Lien Co']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->paid()->create();

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', '999 Unique')
            ->call('searchLiens')
            ->assertSee('Address Lien Co')
            ->assertDontSee('Other Lien Co');
    });

    it('preserves search mode when search text changes', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchLiens')
            ->assertSet('searchMode', SearchMode::Liens)
            ->set('search', 'test updated')
            ->assertSet('searchMode', SearchMode::Liens);
    });

    it('clears search mode when search is emptied', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchBusinesses')
            ->assertSet('searchMode', SearchMode::Businesses)
            ->set('search', '')
            ->assertSet('searchMode', null);
    });

    it('clears search mode when clearSearchMode is called', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchLiens')
            ->assertSet('searchMode', SearchMode::Liens)
            ->call('clearSearchMode')
            ->assertSet('searchMode', null);
    });

    it('board mode still filters only paid active filings', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $paidBusiness = Business::factory()->create(['name' => 'Paid Active Co']);
        $paidProject = LienProject::factory()->create(['business_id' => $paidBusiness->id]);
        LienFiling::factory()->forProject($paidProject)->paid()->create();

        $draftBusiness = Business::factory()->create(['name' => 'Hidden Draft Co']);
        $draftProject = LienProject::factory()->create(['business_id' => $draftBusiness->id]);
        LienFiling::factory()->forProject($draftProject)->draft()->create();

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertSet('searchMode', null)
            ->assertSee('Paid Active Co')
            ->assertDontSee('Hidden Draft Co');
    });

    it('switches between search modes', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->set('search', 'test')
            ->call('searchBusinesses')
            ->assertSet('searchMode', SearchMode::Businesses)
            ->call('searchLiens')
            ->assertSet('searchMode', SearchMode::Liens)
            ->call('searchBusinesses')
            ->assertSet('searchMode', SearchMode::Businesses);
    });
});

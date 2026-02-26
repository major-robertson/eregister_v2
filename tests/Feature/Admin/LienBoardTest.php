<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienBoard;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
});

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

    it('places awaiting_esign filings in the Awaiting E-Signature column', function () {
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
            ->assertSee('Awaiting E-Signature')
            ->assertSee('Signing Co');
    });

    it('does not show draft or canceled filings', function () {
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

        $this->actingAs($admin);

        Livewire::test(LienBoard::class)
            ->assertDontSee('Draft Co')
            ->assertDontSee('Canceled Co');
    });
});

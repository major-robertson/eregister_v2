<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienBoardAll;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

describe('access control', function () {
    it('allows users with lien.view permission to access the all board', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin)
            ->get(route('admin.liens.board-all'))
            ->assertSuccessful()
            ->assertSee('All Filings Board');
    });

    it('denies users without lien.view permission', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.liens.board-all'))
            ->assertForbidden();
    });

    it('denies unauthenticated users', function () {
        $this->get(route('admin.liens.board-all'))
            ->assertRedirect(route('login'));
    });
});

describe('all statuses board', function () {
    it('renders all filing status columns', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->assertSee('Draft')
            ->assertSee('Awaiting Payment')
            ->assertSee('Submitted')
            ->assertSee('Awaiting Client')
            ->assertSee('Needs Review')
            ->assertSee('Ready to Send')
            ->assertSee('Waiting on Next Step')
            ->assertSee('Hold')
            ->assertSee('In Fulfillment')
            ->assertSee('Mailed')
            ->assertSee('Recorded')
            ->assertSee('Complete')
            ->assertSee('Canceled');
    });

    it('shows draft filings', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Draft Builder Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->assertSee('Draft Builder Co');
    });

    it('shows canceled filings', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Canceled Works Inc']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Canceled,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->assertSee('Canceled Works Inc');
    });

    it('filters by business name when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $matchBusiness = Business::factory()->create(['name' => 'Pinnacle Roofing']);
        $matchProject = LienProject::factory()->create(['business_id' => $matchBusiness->id]);
        LienFiling::factory()->forProject($matchProject)->create([
            'status' => FilingStatus::Draft,
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'Valley Electric']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->set('search', 'Pinnacle')
            ->assertSee('Pinnacle Roofing')
            ->assertDontSee('Valley Electric');
    });

    it('filters by filer email when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $filer = User::factory()->create(['email' => 'searchme@test.com']);

        $business = Business::factory()->create(['name' => 'Found By Email Co']);
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Draft,
            'created_by_user_id' => $filer->id,
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'Not Found Co']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->set('search', 'searchme')
            ->assertSee('Found By Email Co')
            ->assertDontSee('Not Found Co');
    });

    it('filters by property address when searching', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create(['name' => 'Maple St Co']);
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'jobsite_address1' => '55 Maple Street',
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Draft,
        ]);

        $otherBusiness = Business::factory()->create(['name' => 'Elm St Co']);
        $otherProject = LienProject::factory()->create(['business_id' => $otherBusiness->id]);
        LienFiling::factory()->forProject($otherProject)->create([
            'status' => FilingStatus::Draft,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->set('search', 'Maple')
            ->assertSee('Maple St Co')
            ->assertDontSee('Elm St Co');
    });

    it('shows filings across multiple statuses', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Draft,
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
        ]);
        LienFiling::factory()->forProject($project)->create([
            'status' => FilingStatus::Complete,
            'paid_at' => now(),
            'completed_at' => now(),
        ]);

        $this->actingAs($admin);

        Livewire::test(LienBoardAll::class)
            ->assertSee('Draft')
            ->assertSee('Submitted')
            ->assertSee('Complete');
    });
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
});

describe('access control', function () {
    it('allows users with lien.view permission to access the filing detail page', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($admin)
            ->get(route('admin.liens.show', $filing))
            ->assertSuccessful()
            ->assertSee('Filing Detail');
    });

    it('denies users without lien.view permission access', function () {
        $user = User::factory()->create();

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($user)
            ->get(route('admin.liens.show', $filing))
            ->assertForbidden();
    });

    it('denies unauthenticated users access', function () {
        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->get(route('admin.liens.show', $filing))
            ->assertRedirect(route('login'));
    });
});

describe('filing detail sections', function () {
    it('displays the filing summary section', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create([
            'description_of_work' => 'Plumbing installation and repairs',
            'amount_claimed_cents' => 500000,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Filing Summary')
            ->assertSee('$5,000.00')
            ->assertSee($filing->documentType->name);
    });

    it('displays the project information section', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create([
            'business_id' => $business->id,
            'name' => 'Downtown Office Building',
            'job_number' => 'JOB-2026-001',
            'jobsite_address1' => '123 Main Street',
            'jobsite_city' => 'Los Angeles',
            'jobsite_state' => 'CA',
            'jobsite_zip' => '90001',
            'jobsite_county' => 'Los Angeles',
        ]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Project Information')
            ->assertSee('Downtown Office Building')
            ->assertSee('JOB-2026-001')
            ->assertSee('123 Main Street');
    });

    it('displays the filing application section with parties snapshot', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        $partiesSnapshot = [
            [
                'role' => PartyRole::Claimant->value,
                'name' => 'John Smith',
                'company_name' => 'Smith Construction LLC',
                'address1' => '456 Builder Ave',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip' => '90002',
            ],
            [
                'role' => PartyRole::Owner->value,
                'name' => 'Jane Doe',
                'company_name' => null,
                'address1' => '789 Property Lane',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip' => '90003',
            ],
        ];

        $filing = LienFiling::factory()->forProject($project)->create([
            'description_of_work' => 'Complete kitchen renovation',
            'parties_snapshot_json' => $partiesSnapshot,
        ]);

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Filing Application')
            ->assertSee('Complete kitchen renovation')
            ->assertSee('Smith Construction LLC')
            ->assertSee('Jane Doe')
            ->assertSee('Claimant (You)')
            ->assertSee('Property Owner');
    });

    it('displays the all fields collapsible section', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('All Fields')
            ->assertSee('Filing Attributes')
            ->assertSee('public_id');
    });

    it('loads project parties relationship', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);

        // Create parties directly without factory
        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => PartyRole::Claimant,
            'name' => 'Test User',
            'company_name' => 'Test Claimant Inc',
            'address1' => '123 Test St',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90001',
        ]);

        LienParty::create([
            'business_id' => $business->id,
            'project_id' => $project->id,
            'role' => PartyRole::Owner,
            'name' => 'Property Owner Name',
            'address1' => '456 Owner Ave',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip' => '90002',
        ]);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Project Information');
    });
});

describe('status history', function () {
    it('displays status history section', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view');

        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Status History');
    });
});

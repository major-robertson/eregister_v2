<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\NocStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo('lien.view');

    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->calculator = app(DeadlineCalculator::class);
});

describe('lien rights summary card', function () {
    it('displays the lien rights summary card in the sidebar', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
        ]);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Lien Rights Summary');
    });

    it('displays key project factors', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'noc_status' => NocStatus::No,
            'first_furnish_date' => '2026-01-15',
            'last_furnish_date' => '2026-02-01',
        ]);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Project State')
            ->assertSee('FL')
            ->assertSee('Claimant Type')
            ->assertSee('Subcontractor')
            ->assertSee('Property Class')
            ->assertSee('Residential')
            ->assertSee('NOC Status')
            ->assertSee('First Furnish')
            ->assertSee('Jan 15, 2026')
            ->assertSee('Last Furnish')
            ->assertSee('Feb 1, 2026');
    });

    it('shows the filing document type status banner', function () {
        $noiDocType = LienDocumentType::where('slug', 'noi')->first();

        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'KY',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'first_furnish_date' => now()->subDays(10),
            'noc_status' => NocStatus::No,
        ]);

        $this->calculator->calculateForProject($project);

        $filing = LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $noiDocType->id,
        ]);

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Notice of Intent to Lien:');
    });

    it('displays required deadlines after calculation', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'first_furnish_date' => now()->subDays(10),
            'noc_status' => NocStatus::No,
        ]);

        $this->calculator->calculateForProject($project);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Required Deadlines');
    });

    it('shows days remaining for upcoming required deadlines', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'first_furnish_date' => now()->subDays(5),
            'noc_status' => NocStatus::No,
        ]);

        $this->calculator->calculateForProject($project);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('days left');
    });

    it('shows completion date when set', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-01-01',
            'completion_date' => '2026-03-15',
            'noc_status' => NocStatus::No,
        ]);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Completion')
            ->assertSee('Mar 15, 2026');
    });

    it('shows NOC recorded date when available', function () {
        $project = LienProject::factory()->create([
            'business_id' => $this->business->id,
            'jobsite_state' => 'FL',
            'claimant_type' => ClaimantType::Subcontractor,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-01-01',
            'noc_status' => NocStatus::Yes,
            'noc_recorded_at' => '2026-02-10',
        ]);

        $filing = LienFiling::factory()->forProject($project)->create();

        $this->actingAs($this->admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Feb 10, 2026');
    });
});

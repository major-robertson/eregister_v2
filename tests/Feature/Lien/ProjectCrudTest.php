<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    // Set current business in session
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('can view project list', function () {
    // Seed document types for deadlines
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    LienProject::factory()->count(3)->forBusiness($this->business)->create();

    $this->get(route('lien.projects.index'))
        ->assertSuccessful()
        ->assertSee('Lien Projects');
});

it('can create a project using wizard', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->get(route('lien.projects.create'))
        ->assertSuccessful();

    // Complete wizard via Livewire
    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        // Step 1: Project Info
        ->assertSet('step', 1)
        ->set('name', 'Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep')
        ->assertSet('step', 2)
        // Step 2: Jobsite Address
        ->set('jobsite_state', 'CA')
        ->set('jobsite_city', 'Los Angeles')
        ->set('jobsite_county', 'Los Angeles County')
        ->call('nextStep')
        ->assertSet('step', 3)
        // Step 3: Property Details
        ->set('project_type', 'commercial')
        ->call('nextStep')
        ->assertSet('step', 4)
        // Step 4: Claimant Info
        ->set('claimant_company_name', 'Test Company LLC')
        ->call('nextStep')
        ->assertSet('step', 5)
        // Step 5: Contract Details
        ->call('nextStep')
        ->assertSet('step', 6)
        // Step 6: Important Dates - save
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('lien_projects', [
        'business_id' => $this->business->id,
        'name' => 'Test Project',
        'jobsite_state' => 'CA',
        'project_type' => 'commercial',
    ]);

    // Check claimant party was created
    $project = LienProject::where('name', 'Test Project')->first();
    expect($project->claimantParty())->not->toBeNull();
    expect($project->claimantParty()->company_name)->toBe('Test Company LLC');
});

it('can view a project', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'My Test Project',
        'jobsite_state' => 'CA',
    ]);

    // Calculate deadlines
    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

    $this->get(route('lien.projects.show', $project))
        ->assertSuccessful()
        ->assertSee('My Test Project');
});

it('can edit a project using wizard', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Original Name',
    ]);

    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class, ['project' => $project])
        // Already on step 1, update name
        ->set('name', 'Updated Name')
        // Navigate through all steps to save
        ->call('nextStep') // Step 2
        ->call('nextStep') // Step 3
        ->call('nextStep') // Step 4
        ->call('nextStep') // Step 5
        ->call('nextStep') // Step 6
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('lien_projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
    ]);
});

it('calculates deadlines when project is saved', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        // Step 1
        ->set('name', 'Deadline Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep')
        // Step 2
        ->set('jobsite_state', 'CA')
        ->call('nextStep')
        // Step 3
        ->call('nextStep')
        // Step 4
        ->set('claimant_company_name', 'Test Co')
        ->call('nextStep')
        // Step 5
        ->call('nextStep')
        // Step 6
        ->set('first_furnish_date', now()->subDays(10)->format('Y-m-d'))
        ->call('save')
        ->assertRedirect();

    $project = LienProject::where('name', 'Deadline Test Project')->first();

    // Should have deadlines calculated
    expect($project->deadlines()->count())->toBeGreaterThan(0);
});

it('can save financial breakdown on project', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        // Step 1
        ->set('name', 'Financial Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep')
        // Step 2
        ->set('jobsite_state', 'CA')
        ->call('nextStep')
        // Step 3
        ->call('nextStep')
        // Step 4
        ->set('claimant_company_name', 'Test Co')
        ->call('nextStep')
        // Step 5 - Financial breakdown
        ->set('has_written_contract', true)
        ->set('base_contract_amount', '10000.00')
        ->set('change_orders', '500.00')
        ->set('payments_received', '3000.00')
        ->call('nextStep')
        // Step 6
        ->call('save')
        ->assertRedirect();

    $project = LienProject::where('name', 'Financial Test Project')->first();

    expect($project->has_written_contract)->toBeTrue();
    expect($project->base_contract_amount_cents)->toBe(1000000);
    expect($project->change_orders_cents)->toBe(50000);
    expect($project->payments_received_cents)->toBe(300000);
    expect($project->balanceDueCents())->toBe(750000); // 10000 + 500 - 3000 = 7500
});

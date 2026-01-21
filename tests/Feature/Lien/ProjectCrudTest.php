<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
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

it('can create a project', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->get(route('lien.projects.create'))
        ->assertSuccessful();

    // Submit form via Livewire
    \Livewire\Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        ->set('name', 'Test Project')
        ->set('claimant_type', 'subcontractor')
        ->set('jobsite_state', 'CA')
        ->set('jobsite_city', 'Los Angeles')
        ->set('jobsite_county', 'Los Angeles County')
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('lien_projects', [
        'business_id' => $this->business->id,
        'name' => 'Test Project',
        'jobsite_state' => 'CA',
    ]);
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

it('can edit a project', function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Original Name',
    ]);

    \Livewire\Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class, ['project' => $project])
        ->set('name', 'Updated Name')
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

    \Livewire\Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        ->set('name', 'Deadline Test Project')
        ->set('claimant_type', 'subcontractor')
        ->set('jobsite_state', 'CA')
        ->set('first_furnish_date', now()->subDays(10)->format('Y-m-d'))
        ->call('save')
        ->assertRedirect();

    $project = LienProject::where('name', 'Deadline Test Project')->first();

    // Should have deadlines calculated
    expect($project->deadlines()->count())->toBeGreaterThan(0);
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('can view project list', function () {
    LienProject::factory()->count(3)->forBusiness($this->business)->create();

    $this->get(route('lien.projects.index'))
        ->assertSuccessful();
});

it('can create a project using wizard', function () {
    $this->get(route('lien.projects.create'))
        ->assertSuccessful();

    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        ->assertSet('step', 1)
        ->set('name', 'Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep')
        ->assertSet('step', 2)
        ->set('jobsite_state', 'CA')
        ->set('jobsite_city', 'Los Angeles')
        ->set('jobsite_county', 'Los Angeles County')
        ->set('property_class', 'commercial')
        ->call('nextStep')
        ->assertSet('step', 3)
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('lien_projects', [
        'business_id' => $this->business->id,
        'name' => 'Test Project',
        'jobsite_state' => 'CA',
        'property_class' => 'commercial',
    ]);
});

it('can view a project', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'My Test Project',
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
    ]);

    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($project);

    $this->get(route('lien.projects.show', $project))
        ->assertSuccessful()
        ->assertSee('My Test Project');
});

it('can edit a project using wizard', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'name' => 'Original Name',
        'claimant_type' => 'subcontractor',
        'property_class' => 'commercial',
    ]);

    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class, ['project' => $project])
        ->set('name', 'Updated Name')
        ->call('nextStep')
        ->call('nextStep')
        ->call('save')
        ->assertRedirect();

    $this->assertDatabaseHas('lien_projects', [
        'id' => $project->id,
        'name' => 'Updated Name',
    ]);
});

it('calculates deadlines when project is saved', function () {
    Livewire::test(\App\Domains\Lien\Livewire\ProjectForm::class)
        ->set('name', 'Deadline Test Project')
        ->set('claimant_type', 'subcontractor')
        ->call('nextStep')
        ->set('jobsite_state', 'CA')
        ->set('property_class', 'commercial')
        ->call('nextStep')
        ->set('first_furnish_date', now()->subDays(10)->format('Y-m-d'))
        ->call('save')
        ->assertRedirect();

    $project = LienProject::where('name', 'Deadline Test Project')->first();

    expect($project->deadlines()->count())->toBeGreaterThan(0);
});

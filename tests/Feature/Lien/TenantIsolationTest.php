<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->businessA = Business::factory()->create([
        'name' => 'Business A',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->businessA->users()->attach($this->userA, ['role' => 'owner']);

    $this->userB = User::factory()->create();
    $this->businessB = Business::factory()->create([
        'name' => 'Business B',
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->businessB->users()->attach($this->userB, ['role' => 'owner']);

    $this->projectA = LienProject::factory()->forBusiness($this->businessA)->create([
        'name' => 'Project A',
        'claimant_type' => 'subcontractor',
    ]);
    $this->projectB = LienProject::factory()->forBusiness($this->businessB)->create([
        'name' => 'Project B',
        'claimant_type' => 'subcontractor',
    ]);
});

it('user cannot view projects from another business', function () {
    $this->actingAs($this->userA);
    session(['current_business_id' => $this->businessA->id]);

    $this->get(route('lien.projects.show', $this->projectA))
        ->assertSuccessful();

    $this->get(route('lien.projects.show', $this->projectB))
        ->assertNotFound();
});

it('user cannot edit projects from another business', function () {
    $this->actingAs($this->userA);
    session(['current_business_id' => $this->businessA->id]);

    $this->get(route('lien.projects.edit', $this->projectB))
        ->assertNotFound();
});

it('project list only shows own business projects', function () {
    $this->actingAs($this->userA);
    session(['current_business_id' => $this->businessA->id]);

    $this->get(route('lien.projects.index'))
        ->assertSuccessful()
        ->assertSee('Project A')
        ->assertDontSee('Project B');
});

it('global scope filters by current business', function () {
    $this->actingAs($this->userA);
    session(['current_business_id' => $this->businessA->id]);

    $projectsForA = LienProject::withoutGlobalScope('business')
        ->where('business_id', $this->businessA->id)
        ->get();

    expect($projectsForA)->toHaveCount(1);
    expect($projectsForA->first()->name)->toBe('Project A');
});

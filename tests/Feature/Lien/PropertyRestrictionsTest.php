<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(DeadlineCalculator::class);
});

it('blocks tenant improvement liens when not allowed', function () {
    // Set up state rule to block tenant liens
    LienStateRule::where('state', 'FL')->update([
        'tenant_project_lien_allowed' => false,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'FL',
        'property_context' => 'tenant_improvement',
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Blocked);
    expect($lienDeadline->status_reason)->toBe('tenant_project_not_allowed');
    expect($lienDeadline->status_meta)->toHaveKey('property_context');
    expect($lienDeadline->status_meta['restriction'])->toBe('not_allowed');
});

it('warns about tenant restrictions when they exist', function () {
    // Set up state rule with tenant restrictions
    LienStateRule::where('state', 'CA')->update([
        'tenant_project_lien_allowed' => true,
        'tenant_project_restrictions' => 'owner_consent_required',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'property_context' => 'tenant_improvement',
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Warning);
    expect($lienDeadline->status_reason)->toBe('tenant_project_restrictions');
    expect($lienDeadline->status_meta['restriction_type'])->toBe('owner_consent_required');
});

it('warns about owner-occupied restrictions', function () {
    // Set up state rule with owner-occupied restrictions
    LienStateRule::where('state', 'CA')->update([
        'owner_occupied_restriction_type' => 'direct_contract_only',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'property_context' => 'owner_occupied',
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Warning);
    expect($lienDeadline->status_reason)->toBe('owner_occupied_restrictions');
    expect($lienDeadline->status_meta['restriction_type'])->toBe('direct_contract_only');
});

it('warns about unknown property type when restrictions exist', function () {
    // Set up state rule with some restrictions
    LienStateRule::where('state', 'CA')->update([
        'owner_occupied_restriction_type' => 'direct_contract_only',
        'tenant_project_restrictions' => 'none',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'property_context' => 'unknown',
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Warning);
    expect($lienDeadline->status_reason)->toBe('unknown_property_type');
    expect($lienDeadline->status_meta['needs_property_context'])->toBeTrue();
});

it('allows normal projects without restrictions', function () {
    // Set up state rule with no restrictions
    LienStateRule::where('state', 'CA')->update([
        'tenant_project_lien_allowed' => true,
        'tenant_project_restrictions' => 'none',
        'owner_occupied_restriction_type' => 'none',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'property_context' => 'other',
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::Blocked);
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::Warning);
});

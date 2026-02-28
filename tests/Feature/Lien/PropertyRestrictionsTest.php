<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(DeadlineCalculator::class);
});

it('warns when property_class is null and state has tenant restrictions', function () {
    LienStateRule::where('state', 'CA')->update([
        'tenant_project_restrictions' => 'owner_consent_required',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'property_class' => null,
        'completion_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['has_property_warning'] ?? false)->toBeTrue();
    expect($lienDeadline->status_meta['property_warning_reason'])->toBe('unknown_property_type');
    expect($lienDeadline->status_meta['needs_property_class'])->toBeTrue();
});

it('warns when property_class is null and state has owner-occupied restrictions', function () {
    LienStateRule::where('state', 'CA')->update([
        'owner_occupied_restriction_type' => 'direct_contract_only',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'property_class' => null,
        'completion_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['has_property_warning'] ?? false)->toBeTrue();
    expect($lienDeadline->status_meta['property_warning_reason'])->toBe('unknown_property_type');
});

it('does not warn when property_class is set', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'property_class' => 'commercial',
        'completion_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['has_property_warning'] ?? false)->toBeFalse();
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::NotApplicable);
});

it('does not warn when state has no restrictions', function () {
    LienStateRule::where('state', 'CA')->update([
        'tenant_project_lien_allowed' => true,
        'tenant_project_restrictions' => 'none',
        'owner_occupied_restriction_type' => 'none',
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'property_class' => null,
        'completion_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['has_property_warning'] ?? false)->toBeFalse();
});

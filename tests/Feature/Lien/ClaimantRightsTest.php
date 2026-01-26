<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
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

it('marks mechanics lien as not applicable when claimant has no rights', function () {
    // Set up state rule to deny rights for supplier_to_subcontractor
    LienStateRule::where('state', 'TX')->update([
        'supplier_sub_has_lien_rights' => false,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'TX',
        'claimant_type' => ClaimantType::SupplierToSubcontractor,
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::NotApplicable);
    expect($lienDeadline->status_reason)->toBe('no_lien_rights_for_claimant');
    expect($lienDeadline->status_meta)->toHaveKey('claimant_type');
    expect($lienDeadline->status_meta)->toHaveKey('state');
});

it('allows mechanics lien when claimant has rights', function () {
    // Ensure rights exist for subcontractor
    LienStateRule::where('state', 'CA')->update([
        'sub_has_lien_rights' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => ClaimantType::Subcontractor,
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::NotApplicable);
    expect($lienDeadline->status_reason)->not->toBe('no_lien_rights_for_claimant');
});

it('does not check lien rights for prelim notice', function () {
    // Set up state rule to deny lien rights
    LienStateRule::where('state', 'TX')->update([
        'supplier_sub_has_lien_rights' => false,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'TX',
        'claimant_type' => ClaimantType::SupplierToSubcontractor,
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    // Prelim notice should still be available even if lien rights are denied
    $prelimDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($prelimDeadline)->not->toBeNull();
    expect($prelimDeadline->status_reason)->not->toBe('no_lien_rights_for_claimant');
});

it('checks rights for general contractor', function () {
    // Deny rights for GC
    LienStateRule::where('state', 'FL')->update([
        'gc_has_lien_rights' => false,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'FL',
        'claimant_type' => ClaimantType::Gc,
        'last_furnish_date' => now()->subDays(30),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::NotApplicable);
    expect($lienDeadline->status_reason)->toBe('no_lien_rights_for_claimant');
});

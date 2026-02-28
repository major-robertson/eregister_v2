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

it('shortens lien deadline when NOC is filed', function () {
    LienStateRule::where('state', 'CA')->update([
        'noc_shortens_deadline' => true,
        'lien_after_noc_days' => 30,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'completion_date' => now()->subDays(60),
        'noc_filed_date' => now()->subDays(15),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['noc_shortened'] ?? false)->toBeTrue();
    expect($lienDeadline->status_meta)->toHaveKey('original_due_date');
    expect($lienDeadline->status_meta)->toHaveKey('noc_due_date');
});

it('marks lien not applicable when NOC filed without prior prelim', function () {
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'completion_date' => now()->subDays(60),
        'noc_filed_date' => now(),
        'prelim_notice_sent_at' => null,
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::NotApplicable);
    expect($lienDeadline->status_reason)->toBe('noc_requires_prior_prelim');
    expect($lienDeadline->status_meta)->toHaveKey('noc_filed_date');
});

it('marks lien not applicable when prelim sent after NOC', function () {
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'completion_date' => now()->subDays(60),
        'noc_filed_date' => now()->subDays(5),
        'prelim_notice_sent_at' => now(),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::NotApplicable);
    expect($lienDeadline->status_reason)->toBe('noc_requires_prior_prelim');
});

it('allows lien when prelim sent before NOC', function () {
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'completion_date' => now()->subDays(60),
        'noc_filed_date' => now(),
        'prelim_notice_sent_at' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::NotApplicable);
});

it('does not shorten deadline when NOC deadline is later than base deadline', function () {
    LienStateRule::where('state', 'CA')->update([
        'noc_shortens_deadline' => true,
        'lien_after_noc_days' => 180,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'completion_date' => now()->subDays(80),
        'noc_filed_date' => now()->subDays(5),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status_meta['noc_shortened'] ?? false)->toBeFalse();
});

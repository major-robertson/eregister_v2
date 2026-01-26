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

it('shortens lien deadline when NOC is filed', function () {
    // Set up state rule with NOC shortening
    LienStateRule::where('state', 'CA')->update([
        'noc_shortens_deadline' => true,
        'lien_after_noc_days' => 30,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'last_furnish_date' => now()->subDays(60),
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

it('blocks lien when NOC filed without prior prelim', function () {
    // Set up state rule with prelim requirement
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'last_furnish_date' => now()->subDays(60),
        'noc_filed_date' => now(),
        'prelim_notice_sent_at' => null, // No prelim sent
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Blocked);
    expect($lienDeadline->status_reason)->toBe('noc_requires_prior_prelim');
    expect($lienDeadline->status_meta)->toHaveKey('noc_filed_date');
});

it('blocks lien when prelim sent after NOC', function () {
    // Set up state rule with prelim requirement
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'last_furnish_date' => now()->subDays(60),
        'noc_filed_date' => now()->subDays(5),
        'prelim_notice_sent_at' => now(), // Sent AFTER NOC
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->toBe(DeadlineStatus::Blocked);
    expect($lienDeadline->status_reason)->toBe('noc_requires_prior_prelim');
});

it('allows lien when prelim sent before NOC', function () {
    // Set up state rule with prelim requirement
    LienStateRule::where('state', 'CA')->update([
        'noc_eliminates_rights_if_no_prelim' => true,
        'noc_requires_prior_prelim' => true,
    ]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'last_furnish_date' => now()->subDays(60),
        'noc_filed_date' => now(),
        'prelim_notice_sent_at' => now()->subDays(10), // Sent BEFORE NOC
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->status)->not->toBe(DeadlineStatus::Blocked);
});

it('does not shorten deadline when NOC deadline is later than base deadline', function () {
    // Set up state rule with NOC shortening to a long period
    LienStateRule::where('state', 'CA')->update([
        'noc_shortens_deadline' => true,
        'lien_after_noc_days' => 180, // Long period
    ]);

    // NOC filed very recently, base deadline would be sooner
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'last_furnish_date' => now()->subDays(80), // Close to 90 day deadline
        'noc_filed_date' => now()->subDays(5), // NOC + 180 = 175 days from now
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Should NOT have noc_shortened because base deadline is sooner
    expect($lienDeadline->status_meta['noc_shortened'] ?? false)->toBeFalse();
});

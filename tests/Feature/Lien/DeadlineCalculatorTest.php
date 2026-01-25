<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienStateRuleSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(DeadlineCalculator::class);
});

it('calculates deadlines for a project with dates', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    expect($project->deadlines()->count())->toBeGreaterThan(0);

    // Should have a preliminary notice deadline
    $prelimDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($prelimDeadline)->not->toBeNull();
    expect($prelimDeadline->due_date)->not->toBeNull();
    expect($prelimDeadline->status)->toBe(DeadlineStatus::Pending);
});

it('marks deadline as not_applicable when anchor date is missing', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => null,
        'last_furnish_date' => null,
    ]);

    $this->calculator->calculateForProject($project);

    $deadline = $project->deadlines()->first();

    expect($deadline->status)->toBe(DeadlineStatus::NotApplicable);
    expect($deadline->missing_fields_json)->not->toBeNull();
});

it('stores computed_from_date correctly', function () {
    $firstFurnishDate = now()->subDays(15);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => $firstFurnishDate,
    ]);

    $this->calculator->calculateForProject($project);

    $prelimDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($prelimDeadline->computed_from_date->toDateString())
        ->toBe($firstFurnishDate->toDateString());
});

it('upserts deadlines without creating duplicates', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ]);

    // Calculate twice
    $this->calculator->calculateForProject($project);
    $countAfterFirst = $project->deadlines()->count();

    $this->calculator->calculateForProject($project);
    $countAfterSecond = $project->deadlines()->count();

    expect($countAfterSecond)->toBe($countAfterFirst);
});

it('recalculates due_date when anchor date changes', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);
    $originalDueDate = $project->deadlines()->first()->due_date;

    // Update the anchor date
    $project->update(['first_furnish_date' => now()->subDays(5)]);
    $this->calculator->calculateForProject($project->fresh());

    $newDueDate = $project->fresh()->deadlines()->first()->due_date;

    expect($newDueDate->toDateString())->not->toBe($originalDueDate->toDateString());
});

it('preserves completed status when recalculating', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    // Mark a deadline as completed
    $deadline = $project->deadlines()->first();
    $deadline->update(['status' => DeadlineStatus::Completed, 'completed_filing_id' => 1]);

    // Recalculate
    $this->calculator->calculateForProject($project->fresh());

    expect($project->fresh()->deadlines()->first()->status)->toBe(DeadlineStatus::Completed);
});

// Texas tests - month_day_after_month_of_date calculation
it('calculates TX residential deadline as 15th of 3rd month', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 15); // Jan 15

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'TX',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'residential',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Start of Jan (Jan 1) + 3 months = Apr 1, then set day to 15 = Apr 15
    expect($lienDeadline->due_date->toDateString())->toBe('2026-04-15');
});

it('calculates TX commercial deadline as 15th of 4th month', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 15); // Jan 15

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'TX',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'commercial',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Start of Jan (Jan 1) + 4 months = May 1, then set day to 15 = May 15
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-15');
});

it('handles TX edge case with Jan 31 anchor date', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 31); // Jan 31

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'TX',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'residential',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Start of Jan (Jan 1) + 3 months = Apr 1, then set day to 15 = Apr 15 (NOT Apr 30)
    expect($lienDeadline->due_date->toDateString())->toBe('2026-04-15');
});

// New York tests - months_after_date calculation
it('calculates NY residential deadline as 4 months', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 15); // Jan 15

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'NY',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'residential',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Jan 15 + 4 months = May 15
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-15');
});

it('calculates NY commercial deadline as 8 months', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 15); // Jan 15

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'NY',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'commercial',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Jan 15 + 8 months = Sep 15
    expect($lienDeadline->due_date->toDateString())->toBe('2026-09-15');
});

it('handles NY edge case with Jan 31 anchor date', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 31); // Jan 31

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'NY',
        'last_furnish_date' => $lastFurnishDate,
        'project_type' => 'residential',
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Jan 31 + 4 months with no overflow = May 31
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-31');
});

// Virginia tests - days_after_end_of_month_of_date calculation
it('calculates VA deadline as 90 days from end of month', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 15); // Jan 15

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'VA',
        'last_furnish_date' => $lastFurnishDate,
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Jan 15 -> end of Jan (Jan 31) + 90 days = May 1
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-01');
});

it('calculates VA deadline correctly when anchor is end of month', function () {
    $lastFurnishDate = Carbon::create(2026, 1, 31); // Jan 31

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'VA',
        'last_furnish_date' => $lastFurnishDate,
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Jan 31 -> end of Jan (Jan 31) + 90 days = May 1 (same result)
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-01');
});

it('calculates VA deadline for February correctly', function () {
    $lastFurnishDate = Carbon::create(2026, 2, 1); // Feb 1

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'VA',
        'last_furnish_date' => $lastFurnishDate,
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    // Feb 1 -> end of Feb (Feb 28, 2026 is not leap year) + 90 days = May 29
    expect($lienDeadline->due_date->toDateString())->toBe('2026-05-29');
});

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
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

<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(DeadlineCalculator::class);
});

it('calculates deadlines for a project with dates', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    expect($project->deadlines()->count())->toBeGreaterThan(0);

    $prelimDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($prelimDeadline)->not->toBeNull();
    expect($prelimDeadline->due_date)->not->toBeNull();
    expect($prelimDeadline->status)->toBe(DeadlineStatus::NotStarted);
});

it('marks deadline as not_applicable when anchor date is missing', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => null,
        'last_furnish_date' => null,
    ]);

    $this->calculator->calculateForProject($project);

    $deadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($deadline->status)->toBe(DeadlineStatus::DeadlineUnknown);
    expect($deadline->missing_fields_json)->not->toBeNull();
});

it('stores computed_from_date correctly', function () {
    $firstFurnishDate = now()->subDays(15);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
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
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);
    $countAfterFirst = $project->deadlines()->count();

    $this->calculator->calculateForProject($project);
    $countAfterSecond = $project->deadlines()->count();

    expect($countAfterSecond)->toBe($countAfterFirst);
});

it('recalculates due_date when anchor date changes', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    $originalDueDate = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first()
        ->due_date;

    $project->update(['first_furnish_date' => now()->subDays(5)]);
    $project->refresh();
    $this->calculator->calculateForProject($project);

    $project->refresh();
    $newDueDate = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first()
        ->due_date;

    expect($newDueDate->toDateString())->not->toBe($originalDueDate->toDateString());
});

it('preserves completed status when recalculating', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => now()->subDays(10),
    ]);

    $this->calculator->calculateForProject($project);

    $deadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    $filing = LienFiling::factory()->forProject($project)->create([
        'document_type_id' => $deadline->document_type_id,
    ]);
    $deadline->update(['status' => DeadlineStatus::Completed, 'completed_filing_id' => $filing->id]);

    $project->refresh();
    $this->calculator->calculateForProject($project);

    $project->refresh();
    $updatedDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
        ->first();

    expect($updatedDeadline->status)->toBe(DeadlineStatus::Completed);
});

it('calculates state-specific mechanics lien deadlines', function (string $state, string $date, array $extra, string $expectedDate) {
    $project = LienProject::factory()->forBusiness($this->business)->create(array_merge([
        'jobsite_state' => $state,
        'claimant_type' => 'subcontractor',
        'last_furnish_date' => Carbon::parse($date),
    ], $extra));

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($lienDeadline->due_date->toDateString())->toBe($expectedDate);
})->with([
    'TX residential Jan 15' => ['TX', '2026-01-15', [], '2026-04-15'],
    'TX commercial Jan 15' => ['TX', '2026-01-15', ['property_class' => 'commercial'], '2026-05-15'],
    'TX residential Jan 31 edge' => ['TX', '2026-01-31', [], '2026-04-15'],
    'NY residential Jan 15' => ['NY', '2026-01-15', [], '2026-05-15'],
    'NY commercial Jan 15' => ['NY', '2026-01-15', ['property_class' => 'commercial'], '2026-09-15'],
    'NY residential Jan 31 edge' => ['NY', '2026-01-31', [], '2026-05-31'],
    'VA Jan 15' => ['VA', '2026-01-15', [], '2026-05-01'],
    'VA Jan 31 end of month' => ['VA', '2026-01-31', [], '2026-05-01'],
    'VA Feb 1' => ['VA', '2026-02-01', [], '2026-05-29'],
]);

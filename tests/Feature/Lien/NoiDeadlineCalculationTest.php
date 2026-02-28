<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(DeadlineCalculator::class);
});

it('calculates NOI deadline based on lien deadline minus lead time', function () {
    $stateRule = LienStateRule::where('state', 'AL')->first();
    $noiLeadTime = $stateRule->noi_lead_time_days ?? 10;

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => Carbon::create(2026, 1, 15),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    expect($lienDeadline)->not->toBeNull();
    expect($noiDeadline)->not->toBeNull();

    if ($lienDeadline->due_date && $noiDeadline->due_date) {
        $expectedNoiDate = $lienDeadline->due_date->copy()->subDays($noiLeadTime);
        expect($noiDeadline->due_date->toDateString())->toBe($expectedNoiDate->toDateString());
    }
});

it('inherits missing fields from lien when lien anchor is missing', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => null,
    ]);

    $this->calculator->calculateForProject($project);

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    expect($noiDeadline)->not->toBeNull();
    expect($noiDeadline->status)->toBe(DeadlineStatus::DeadlineUnknown);
    expect($noiDeadline->missing_fields_json)->not->toBeEmpty();

    expect($noiDeadline->status_meta['derived_from'] ?? null)->toBe('mechanics_lien');
});

it('does not show lien_filing_date as missing field for NOI', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => null,
    ]);

    $this->calculator->calculateForProject($project);

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    expect($noiDeadline)->not->toBeNull();

    if (! empty($noiDeadline->missing_fields_json)) {
        expect($noiDeadline->missing_fields_json)->not->toContain('lien_filing_date');
    }
});

it('treats bad lead times as zero', function (mixed $badValue) {
    $stateRule = LienStateRule::where('state', 'AL')->first();
    $stateRule->update(['noi_lead_time_days' => $badValue]);

    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => Carbon::create(2026, 1, 15),
    ]);

    $this->calculator->calculateForProject($project);

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    if ($lienDeadline->due_date && $noiDeadline->due_date) {
        expect($noiDeadline->due_date->toDateString())->toBe($lienDeadline->due_date->toDateString());
    }
})->with([
    'zero' => [0],
    'null' => [null],
]);

it('stores lien due date in NOI status meta', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => Carbon::create(2026, 1, 15),
    ]);

    $this->calculator->calculateForProject($project);

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    $lienDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first();

    expect($noiDeadline)->not->toBeNull();

    if ($noiDeadline->due_date && $lienDeadline->due_date) {
        expect($noiDeadline->status_meta['derived_from_lien_due'] ?? null)
            ->toBe($lienDeadline->due_date->toDateString());
    }
});

it('preserves NOI completed status when recalculating', function () {
    $project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'AL',
        'claimant_type' => 'subcontractor',
        'first_furnish_date' => Carbon::create(2026, 1, 1),
        'last_furnish_date' => Carbon::create(2026, 1, 15),
    ]);

    $this->calculator->calculateForProject($project);

    $noiDeadline = $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    $noiDeadline->update([
        'status' => DeadlineStatus::Completed,
        'completed_externally_at' => now(),
        'external_filed_at' => now()->subDays(5),
    ]);

    $project->refresh();
    $this->calculator->calculateForProject($project);

    $noiDeadline = $project->refresh()->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
        ->first();

    expect($noiDeadline->status)->toBe(DeadlineStatus::Completed);
    expect($noiDeadline->completed_externally_at)->not->toBeNull();
});

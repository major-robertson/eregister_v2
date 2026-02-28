<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienStateRule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Helpers — Tier 1 (no seeders) and Tier 2 (lightweight CA-only seed)
|--------------------------------------------------------------------------
*/

/**
 * Tier 1: Create a project with a single prelim_notice deadline directly.
 * No seeders or DeadlineCalculator needed.
 */
function createProjectWithDeadline(array $projectOverrides = []): array
{
    $docType = LienDocumentType::firstOrCreate(
        ['slug' => 'prelim_notice'],
        ['name' => 'Preliminary Notice', 'is_active' => true]
    );

    $project = LienProject::factory()->forBusiness(test()->business)->create(array_merge([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ], $projectOverrides));

    $deadline = LienProjectDeadline::create([
        'business_id' => $project->business_id,
        'project_id' => $project->id,
        'deadline_rule_id' => null,
        'document_type_id' => $docType->id,
        'due_date' => now()->addDays(10),
        'status' => DeadlineStatus::NotStarted,
    ]);

    return [$project, $deadline];
}

/**
 * Tier 2: Seed only the CA rules needed for DeadlineCalculator, then create
 * a project and compute real deadlines. No CSV parsing — direct inserts only.
 */
function createSeededCAProject(array $projectOverrides = []): LienProject
{
    seedCARules();

    $project = LienProject::factory()->forBusiness(test()->business)->create(array_merge([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
    ], $projectOverrides));

    app(DeadlineCalculator::class)->calculateForProject($project);

    return $project;
}

/**
 * Insert the minimal CA rules directly — 4 doc types, 1 state rule, 3 deadline rules.
 * CA prelim = 20 days from first_furnish_date (matches production data).
 */
function seedCARules(): void
{
    $prelim = LienDocumentType::firstOrCreate(
        ['slug' => 'prelim_notice'],
        ['name' => 'Preliminary Notice', 'is_active' => true]
    );
    $noi = LienDocumentType::firstOrCreate(
        ['slug' => 'noi'],
        ['name' => 'Notice of Intent', 'is_active' => true]
    );
    $lien = LienDocumentType::firstOrCreate(
        ['slug' => 'mechanics_lien'],
        ['name' => 'Mechanics Lien', 'is_active' => true]
    );
    LienDocumentType::firstOrCreate(
        ['slug' => 'lien_release'],
        ['name' => 'Lien Release', 'is_active' => true]
    );

    LienStateRule::firstOrCreate(['state' => 'CA'], [
        'enforcement_deadline_days' => 90,
        'gc_has_lien_rights' => true,
        'sub_has_lien_rights' => true,
        'subsub_has_lien_rights' => true,
        'supplier_owner_has_lien_rights' => true,
        'supplier_gc_has_lien_rights' => true,
        'supplier_sub_has_lien_rights' => true,
    ]);

    LienDeadlineRule::firstOrCreate([
        'state' => 'CA',
        'document_type_id' => $prelim->id,
        'claimant_type' => 'any',
    ], [
        'trigger_event' => 'first_furnish_date',
        'calc_method' => 'days_after_date',
        'offset_days' => 20,
        'is_required' => true,
        'effective_scope' => 'both',
        'is_placeholder' => false,
    ]);

    // NOI uses 'days_before_date' which is not in the CalcMethod enum —
    // bypass Eloquent casts via DB::table(), same as the production seeder.
    if (! DB::table('lien_deadline_rules')->where('state', 'CA')->where('document_type_id', $noi->id)->exists()) {
        DB::table('lien_deadline_rules')->insert([
            'state' => 'CA',
            'document_type_id' => $noi->id,
            'claimant_type' => 'any',
            'trigger_event' => 'lien_filing_date',
            'calc_method' => 'days_before_date',
            'offset_days' => 20,
            'is_required' => true,
            'effective_scope' => 'both',
            'is_placeholder' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    LienDeadlineRule::firstOrCreate([
        'state' => 'CA',
        'document_type_id' => $lien->id,
        'claimant_type' => 'any',
    ], [
        'trigger_event' => 'last_furnish_date',
        'calc_method' => 'days_after_date',
        'offset_days' => 90,
        'is_required' => true,
        'effective_scope' => 'both',
        'is_placeholder' => false,
    ]);
}

/*
|--------------------------------------------------------------------------
| Setup
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['timezone' => 'America/Los_Angeles']);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->calculator = app(StepStatusCalculator::class);
});

/*
|--------------------------------------------------------------------------
| Status Precedence
|--------------------------------------------------------------------------
*/

describe('status precedence', function () {
    it('returns Completed when deadline has completed_filing_id', function () {
        [$project, $deadline] = createProjectWithDeadline();

        $filing = LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Complete,
        ]);
        $deadline->update(['completed_filing_id' => $filing->id]);

        $step = array_values($this->calculator->forProject($project->unsetRelations()))[0];
        expect($step->status)->toBe(DeadlineStatus::Completed);
    });

    it('returns Completed when completed externally', function () {
        [$project, $deadline] = createProjectWithDeadline();
        $deadline->update([
            'completed_externally_at' => now(),
            'external_filed_at' => now()->subDays(5),
            'status' => DeadlineStatus::Completed,
        ]);

        $step = array_values($this->calculator->forProject($project->unsetRelations()))[0];
        expect($step->status)->toBe(DeadlineStatus::Completed);
    });

    it('maps filing status to correct deadline status', function (FilingStatus $filing, DeadlineStatus $expected) {
        [$project, $deadline] = createProjectWithDeadline();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => $filing,
        ]);

        $step = $this->calculator->forProject($project->unsetRelations())['prelim_notice'];
        expect($step->status)->toBe($expected);
    })->with([
        'Paid -> Purchased' => [FilingStatus::Paid, DeadlineStatus::Purchased],
        'InFulfillment' => [FilingStatus::InFulfillment, DeadlineStatus::InFulfillment],
        'Mailed' => [FilingStatus::Mailed, DeadlineStatus::Mailed],
        'Recorded' => [FilingStatus::Recorded, DeadlineStatus::Recorded],
        'AwaitingClient' => [FilingStatus::AwaitingClient, DeadlineStatus::AwaitingClient],
        'AwaitingEsign' => [FilingStatus::AwaitingEsign, DeadlineStatus::AwaitingEsign],
        'AwaitingPayment' => [FilingStatus::AwaitingPayment, DeadlineStatus::AwaitingPayment],
        'Draft -> InDraft' => [FilingStatus::Draft, DeadlineStatus::InDraft],
    ]);

    it('returns DeadlineUnknown when due_date is null', function () {
        $project = createSeededCAProject([
            'first_furnish_date' => null,
            'last_furnish_date' => null,
        ]);

        $steps = $this->calculator->forProject($project->unsetRelations());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::DeadlineUnknown);
        expect($step->missingFields)->not->toBeEmpty();
    });

    it('returns correct time-based status', function (Carbon $testNow, DeadlineStatus $expected) {
        Carbon::setTestNow($testNow);

        $project = createSeededCAProject([
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $step = $this->calculator->forProject($project->unsetRelations())['prelim_notice'];
        expect($step->status)->toBe($expected);

        Carbon::setTestNow();
    })->with([
        'DueSoon (1 day before)' => [fn () => Carbon::create(2026, 1, 20), DeadlineStatus::DueSoon],
        'Missed (25 days past)' => [fn () => Carbon::create(2026, 2, 15), DeadlineStatus::Missed],
        'NotStarted (16 days out)' => [fn () => Carbon::create(2026, 1, 5), DeadlineStatus::NotStarted],
    ]);
});

/*
|--------------------------------------------------------------------------
| Purchase Conflict Locking
|--------------------------------------------------------------------------
*/

describe('purchase conflict locking', function () {
    it('locks step when lien is paid', function (string $lockedSlug) {
        $project = createSeededCAProject([
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        $lienDeadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $lienDeadline->document_type_id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Paid,
        ]);

        $step = $this->calculator->forProject($project->unsetRelations())[$lockedSlug];
        expect($step->status)->toBe(DeadlineStatus::Locked);
        expect($step->lockedReason)->toBe('Lien already purchased');
    })->with(['noi', 'prelim_notice']);

    it('filing status takes precedence over same-doc-type lock', function () {
        [$project, $deadline] = createProjectWithDeadline();

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::InFulfillment,
        ]);

        $step = $this->calculator->forProject($project->unsetRelations())['prelim_notice'];
        expect($step->status)->toBe(DeadlineStatus::InFulfillment);
    });

    it('does not lock when no paid orders exist', function () {
        $project = createSeededCAProject([
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        $steps = $this->calculator->forProject($project->unsetRelations());
        foreach ($steps as $step) {
            expect($step->lockedReason)->toBeNull();
        }
    });
});

/*
|--------------------------------------------------------------------------
| Helper Fields
|--------------------------------------------------------------------------
*/

describe('helper fields', function () {
    it('computes daysUntilDue correctly', function () {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $project = createSeededCAProject([
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $step = $this->calculator->forProject($project->unsetRelations())['prelim_notice'];
        expect($step->daysUntilDue)->toBe(11);

        Carbon::setTestNow();
    });

    it('provides missing field labels', function () {
        $project = createSeededCAProject([
            'first_furnish_date' => null,
        ]);

        $steps = $this->calculator->forProject($project->unsetRelations());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->missingFieldLabels)->not->toBeEmpty();
        expect($step->missingFieldLabels[0])->toBeString();
    });

    it('sets canMarkDoneMyself to true when no paid filing exists', function () {
        [$project] = createProjectWithDeadline();

        $step = array_values($this->calculator->forProject($project->unsetRelations()))[0];
        expect($step->canMarkDoneMyself)->toBeTrue();
    });
});

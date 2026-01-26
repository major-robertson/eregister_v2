<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
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

    $this->calculator = app(StepStatusCalculator::class);
    $this->deadlineCalculator = app(DeadlineCalculator::class);
});

describe('status precedence', function () {
    it('returns Completed status when deadline has completed_filing_id', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()->first();
        $deadline->update(['completed_filing_id' => 1]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = array_values($steps)[0];

        expect($step->status)->toBe(DeadlineStatus::Completed);
    });

    it('returns Completed status when completed externally', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()->first();
        $deadline->update([
            'completed_externally_at' => now(),
            'external_filed_at' => now()->subDays(5),
            'status' => DeadlineStatus::Completed,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = array_values($steps)[0];

        expect($step->status)->toBe(DeadlineStatus::Completed);
    });

    it('returns Purchased status when filing is Paid', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        // Create a paid filing
        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Paid,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::Purchased);
    });

    it('returns InFulfillment status when filing is InFulfillment', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::InFulfillment,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::InFulfillment);
    });

    it('returns AwaitingPayment status when filing is AwaitingPayment', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::AwaitingPayment,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::AwaitingPayment);
    });

    it('returns InDraft status when filing is Draft', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $deadline->document_type_id,
            'project_deadline_id' => $deadline->id,
            'status' => FilingStatus::Draft,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::InDraft);
    });

    it('returns DeadlineUnknown status when due_date is null', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $steps = $this->calculator->forProject($project->fresh());
        $step = array_values($steps)[0];

        expect($step->status)->toBe(DeadlineStatus::DeadlineUnknown);
        expect($step->missingFields)->not->toBeEmpty();
    });

    it('returns DueSoon status when deadline is within 7 days', function () {
        Carbon::setTestNow(Carbon::create(2026, 1, 20));

        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        // Find a deadline due within 7 days and update it
        $deadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        // CA prelim is 20 days from first furnish, so Jan 1 + 20 = Jan 21
        // With test now at Jan 20, it's 1 day away

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::DueSoon);

        Carbon::setTestNow(); // Reset
    });

    it('returns Missed status when deadline is past', function () {
        Carbon::setTestNow(Carbon::create(2026, 2, 15));

        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        // CA prelim is 20 days from first furnish = Jan 21
        // Test now at Feb 15, so it's overdue

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::Missed);

        Carbon::setTestNow(); // Reset
    });

    it('returns NotStarted status when deadline is in the future', function () {
        Carbon::setTestNow(Carbon::create(2026, 1, 5));

        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        // CA prelim is 20 days from first furnish = Jan 21
        // Test now at Jan 5, so it's 16 days away (not due soon)

        $steps = $this->calculator->forProject($project->fresh());
        $step = $steps['prelim_notice'] ?? null;

        expect($step)->not->toBeNull();
        expect($step->status)->toBe(DeadlineStatus::NotStarted);

        Carbon::setTestNow(); // Reset
    });
});

describe('purchase conflict locking', function () {
    it('locks NOI when lien is paid', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        // Get lien deadline
        $lienDeadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        // Create a paid lien filing
        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $lienDeadline->document_type_id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Paid,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $noiStep = $steps['noi'] ?? null;

        expect($noiStep)->not->toBeNull();
        expect($noiStep->status)->toBe(DeadlineStatus::Locked);
        expect($noiStep->lockedReason)->toBe('Lien already purchased');
    });

    it('locks prelim when lien is paid', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $lienDeadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $lienDeadline->document_type_id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Paid,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $prelimStep = $steps['prelim_notice'] ?? null;

        expect($prelimStep)->not->toBeNull();
        expect($prelimStep->status)->toBe(DeadlineStatus::Locked);
        expect($prelimStep->lockedReason)->toBe('Lien already purchased');
    });

    it('locks same doc type when already purchased', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $prelimDeadline = $project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        LienFiling::factory()->forBusiness($this->business)->create([
            'project_id' => $project->id,
            'document_type_id' => $prelimDeadline->document_type_id,
            'project_deadline_id' => $prelimDeadline->id,
            'status' => FilingStatus::InFulfillment,
        ]);

        $steps = $this->calculator->forProject($project->fresh());
        $prelimStep = $steps['prelim_notice'] ?? null;

        // When InFulfillment, status should be InFulfillment, not Locked
        // Locked is only for OTHER doc types blocked by this purchase
        expect($prelimStep)->not->toBeNull();
        expect($prelimStep->status)->toBe(DeadlineStatus::InFulfillment);
    });

    it('does not lock when no paid orders exist', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $steps = $this->calculator->forProject($project->fresh());

        foreach ($steps as $step) {
            expect($step->lockedReason)->toBeNull();
        }
    });
});

describe('helper fields', function () {
    it('computes daysUntilDue correctly', function () {
        Carbon::setTestNow(Carbon::create(2026, 1, 10));

        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => Carbon::create(2026, 1, 1),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $steps = $this->calculator->forProject($project->fresh());
        $prelimStep = $steps['prelim_notice'] ?? null;

        expect($prelimStep)->not->toBeNull();
        // CA prelim is 20 days from first furnish = Jan 21
        // Test now at Jan 10, so 11 days remaining
        expect($prelimStep->daysUntilDue)->toBe(11);

        Carbon::setTestNow();
    });

    it('provides missing field labels', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => null,
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $steps = $this->calculator->forProject($project->fresh());
        $step = array_values($steps)[0];

        expect($step->missingFieldLabels)->not->toBeEmpty();
        expect($step->missingFieldLabels[0])->toBeString();
    });

    it('sets canMarkDoneMyself to true when no paid filing exists', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
        ]);

        $this->deadlineCalculator->calculateForProject($project);

        $steps = $this->calculator->forProject($project->fresh());
        $step = array_values($steps)[0];

        expect($step->canMarkDoneMyself)->toBeTrue();
    });
});

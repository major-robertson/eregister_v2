<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\NocStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'timezone' => 'America/Los_Angeles',
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    // Seed document types
    $this->artisan('db:seed', ['--class' => 'LienDocumentTypeSeeder']);
    $this->artisan('db:seed', ['--class' => 'LienDeadlineRuleSeeder']);

    $this->project = LienProject::factory()->forBusiness($this->business)->create([
        'jobsite_state' => 'CA',
        'first_furnish_date' => now()->subDays(10),
        'last_furnish_date' => now()->subDays(5),
    ]);

    // Calculate deadlines for the project
    app(\App\Domains\Lien\Engine\DeadlineCalculator::class)->calculateForProject($this->project);
});

describe('LienProjectDeadline::canFile()', function () {
    it('returns true for pending preliminary notice', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline)->not->toBeNull();
        expect($deadline->canFile())->toBeTrue();
    });

    it('returns false when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        // Create a completed filing
        $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $deadline->id,
        ]);

        $deadline->update([
            'status' => DeadlineStatus::Completed,
            'completed_filing_id' => $filing->id,
        ]);

        expect($deadline->fresh()->canFile())->toBeFalse();
    });

    it('returns true for preliminary notice even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update([
            'missing_fields_json' => ['first_furnish_date'],
        ]);

        // Preliminary notice can always be filed unless already filed
        expect($deadline->fresh()->canFile())->toBeTrue();
    });

    it('returns false for mechanics lien when there are missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update([
            'missing_fields_json' => ['last_furnish_date'],
        ]);

        expect($deadline->fresh()->canFile())->toBeFalse();
    });

    it('returns true for preliminary notice even when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update([
            'status' => DeadlineStatus::NotApplicable,
        ]);

        // Preliminary notice can always be filed unless already filed
        expect($deadline->fresh()->canFile())->toBeTrue();
    });

    it('returns false for mechanics lien when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update([
            'status' => DeadlineStatus::NotApplicable,
        ]);

        expect($deadline->fresh()->canFile())->toBeFalse();
    });

    it('returns false for lien release when mechanics lien is not filed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'lien_release'))
            ->first();

        // Ensure mechanics lien is not filed
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        expect($lienDeadline->completed_filing_id)->toBeNull();
        expect($deadline->canFile())->toBeFalse();
    });

    it('returns true for lien release when mechanics lien is recorded', function () {
        // First, complete the mechanics lien filing with recorded_at set
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Recorded,
            'recorded_at' => now(), // Must be recorded, not just submitted
        ]);

        $lienDeadline->update([
            'status' => DeadlineStatus::Completed,
            'completed_filing_id' => $filing->id,
        ]);

        // Now check lien release
        $releaseDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'lien_release'))
            ->first();

        expect($releaseDeadline->canFile())->toBeTrue();
    });

    it('returns false for lien release when mechanics lien is submitted but not recorded', function () {
        // File mechanics lien but don't record it
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Submitted,
            'recorded_at' => null, // Not yet recorded
        ]);

        $lienDeadline->update([
            'status' => DeadlineStatus::Pending,
            'completed_filing_id' => null,
        ]);

        // Lien release should not be available
        $releaseDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'lien_release'))
            ->first();

        expect($releaseDeadline->canFile())->toBeFalse();
    });
});

describe('LienProjectDeadline::getFilingBlockerReason()', function () {
    it('returns "Already Filed" when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->getFilingBlockerReason())->toBe('Already Filed');
    });

    it('returns null for preliminary notice even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['missing_fields_json' => ['first_furnish_date']]);

        // Preliminary notice is always available unless already filed
        expect($deadline->fresh()->getFilingBlockerReason())->toBeNull();
    });

    it('returns "Needs Info" for mechanics lien when there are missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getFilingBlockerReason())->toBe('Needs Info');
    });

    it('returns null for preliminary notice even when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        // Preliminary notice is always available unless already filed
        expect($deadline->fresh()->getFilingBlockerReason())->toBeNull();
    });

    it('returns "Not Applicable" for mechanics lien when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        expect($deadline->fresh()->getFilingBlockerReason())->toBe('Not Applicable');
    });

    it('returns "Lien Required" for lien release when mechanics lien is not filed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'lien_release'))
            ->first();

        expect($deadline->getFilingBlockerReason())->toBe('Lien Required');
    });

    it('returns null when filing is available', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getFilingBlockerReason())->toBeNull();
    });
});

describe('LienProjectDeadline::getFilingStatusLabel()', function () {
    it('returns "Filed" when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->getFilingStatusLabel())->toBe('Filed');
    });

    it('returns "Ready" for preliminary notice even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['missing_fields_json' => ['first_furnish_date']]);

        // Preliminary notice is always ready unless already filed
        expect($deadline->fresh()->getFilingStatusLabel())->toBe('Ready');
    });

    it('returns blocker reason for mechanics lien when blocked', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getFilingStatusLabel())->toBe('Needs Info');
    });

    it('returns "Ready" when filing is available', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getFilingStatusLabel())->toBe('Ready');
    });
});

describe('LienProjectDeadline::getFilingStatusColor()', function () {
    it('returns "green" when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->getFilingStatusColor())->toBe('green');
    });

    it('returns "blue" for preliminary notice even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['missing_fields_json' => ['first_furnish_date']]);

        // Preliminary notice is always ready (blue) unless already filed
        expect($deadline->fresh()->getFilingStatusColor())->toBe('blue');
    });

    it('returns "amber" for mechanics lien with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getFilingStatusColor())->toBe('amber');
    });

    it('returns "zinc" for Not Applicable status on mechanics lien', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        expect($deadline->fresh()->getFilingStatusColor())->toBe('zinc');
    });

    it('returns "blue" when ready to file', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getFilingStatusColor())->toBe('blue');
    });
});

describe('LienProject::hasCompletedFilingForType()', function () {
    it('returns false when no filing exists', function () {
        expect($this->project->hasCompletedFilingForType('mechanics_lien'))->toBeFalse();
    });

    it('returns true when filing is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $deadline->id,
        ]);

        $deadline->update([
            'status' => DeadlineStatus::Completed,
            'completed_filing_id' => $filing->id,
        ]);

        expect($this->project->fresh()->hasCompletedFilingForType('mechanics_lien'))->toBeTrue();
    });
});

describe('LienProject::getDeadlineForType()', function () {
    it('returns the deadline for a given document type slug', function () {
        $deadline = $this->project->getDeadlineForType('prelim_notice');

        expect($deadline)->not->toBeNull();
        expect($deadline->documentType->slug)->toBe('prelim_notice');
    });

    it('returns null for non-existent document type', function () {
        $deadline = $this->project->getDeadlineForType('non_existent_type');

        expect($deadline)->toBeNull();
    });
});

describe('LienProjectDeadline::canStart()', function () {
    it('returns true for pending deadline', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->canStart())->toBeTrue();
    });

    it('returns true even when there are missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        // canStart should still be true - wizard collects missing info
        expect($deadline->fresh()->canStart())->toBeTrue();
    });

    it('returns false when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->canStart())->toBeFalse();
    });

    it('returns false when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        expect($deadline->fresh()->canStart())->toBeFalse();
    });
});

describe('LienProjectDeadline::getButtonText()', function () {
    it('returns "Start Filing" when ready', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getButtonText())->toBe('Start Filing');
    });

    it('returns "Start filing (we\'ll ask a few questions)" when missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getButtonText())->toBe("Start filing (we'll ask a few questions)");
    });

    it('returns "View Filing" when completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'prelim_notice')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $deadline->id,
        ]);

        $deadline->update([
            'status' => DeadlineStatus::Completed,
            'completed_filing_id' => $filing->id,
        ]);

        $deadline->load('completedFiling');
        expect($deadline->getButtonText())->toBe('View Filing');
    });
});

describe('LienProjectDeadline::getStatusLabel()', function () {
    it('returns "Filed" when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->getStatusLabel())->toBe('Filed');
    });

    it('returns "Not Applicable" when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        expect($deadline->fresh()->getStatusLabel())->toBe('Not Applicable');
    });

    it('returns "Needs Info" when there are missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getStatusLabel())->toBe('Needs Info');
    });

    it('returns "Ready" when ready to file', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getStatusLabel())->toBe('Ready');
    });
});

describe('LienProjectDeadline::getStatusColor()', function () {
    it('returns "green" when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        expect($deadline->fresh()->getStatusColor())->toBe('green');
    });

    it('returns "zinc" when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        expect($deadline->fresh()->getStatusColor())->toBe('zinc');
    });

    it('returns "amber" when there are missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['missing_fields_json' => ['last_furnish_date']]);

        expect($deadline->fresh()->getStatusColor())->toBe('amber');
    });

    it('returns "blue" when ready to file', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        expect($deadline->getStatusColor())->toBe('blue');
    });
});

describe('LienProject Alerts Status', function () {
    it('returns "paused" when both dates are missing', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => null,
            'last_furnish_date' => null,
        ]);

        expect($project->getAlertsStatus())->toBe('paused');
        expect($project->getAlertsStatusLabel())->toBe('Alerts Paused');
        expect($project->getAlertsStatusColor())->toBe('zinc');
    });

    it('returns "limited" when only first furnish date is set', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => null,
        ]);

        expect($project->getAlertsStatus())->toBe('limited');
        expect($project->getAlertsStatusLabel())->toBe('Alerts Limited');
        expect($project->getAlertsStatusColor())->toBe('amber');
    });

    it('returns "limited" when only last furnish date is set', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => null,
            'last_furnish_date' => now()->subDays(5),
        ]);

        expect($project->getAlertsStatus())->toBe('limited');
    });

    it('returns "active" when both dates are set', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'first_furnish_date' => now()->subDays(10),
            'last_furnish_date' => now()->subDays(5),
        ]);

        expect($project->getAlertsStatus())->toBe('active');
        expect($project->getAlertsStatusLabel())->toBe('Alerts Active');
        expect($project->getAlertsStatusColor())->toBe('green');
    });
});

describe('LienProject NOC Status', function () {
    it('defaults noc_status to unknown', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
        ]);

        expect($project->noc_status)->toBe(NocStatus::Unknown);
    });

    it('can set noc_status to yes with noc_recorded_at', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'noc_status' => NocStatus::Yes,
            'noc_recorded_at' => now()->subDays(3),
        ]);

        expect($project->noc_status)->toBe(NocStatus::Yes);
        expect($project->noc_recorded_at)->not->toBeNull();
    });

    it('can set noc_status to no without noc_recorded_at', function () {
        $project = LienProject::factory()->forBusiness($this->business)->create([
            'jobsite_state' => 'CA',
            'noc_status' => NocStatus::No,
            'noc_recorded_at' => null,
        ]);

        expect($project->noc_status)->toBe(NocStatus::No);
        expect($project->noc_recorded_at)->toBeNull();
    });
});

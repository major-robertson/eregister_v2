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

        $deadline->refresh();
        expect($deadline->canFile())->toBeFalse();
    });

    it('returns true for preliminary notice even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update([
            'missing_fields_json' => ['first_furnish_date'],
        ]);

        $deadline->refresh();
        expect($deadline->canFile())->toBeTrue();
    });

    it('returns true for mechanics lien even with missing fields', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update([
            'missing_fields_json' => ['last_furnish_date'],
        ]);

        $deadline->refresh();
        expect($deadline->canFile())->toBeTrue();
    });

    it('returns false for preliminary notice when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update([
            'status' => DeadlineStatus::NotApplicable,
        ]);

        $deadline->refresh();
        expect($deadline->canFile())->toBeFalse();
    });

    it('returns false for mechanics lien when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update([
            'status' => DeadlineStatus::NotApplicable,
        ]);

        $deadline->refresh();
        expect($deadline->canFile())->toBeFalse();
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

    it('returns false for lien release when mechanics lien is paid but not recorded', function () {
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $lienDeadline->id,
            'status' => FilingStatus::Paid,
            'recorded_at' => null,
        ]);

        $lienDeadline->update([
            'status' => DeadlineStatus::NotStarted,
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
    it('returns correct blocker reason', function (string $slug, array $updates, ?string $expected) {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->first();

        if ($updates) {
            $deadline->update($updates);
            $deadline->refresh();
        }

        expect($deadline->getFilingBlockerReason())->toBe($expected);
    })->with([
        'completed → Already Filed' => ['prelim_notice', ['status' => DeadlineStatus::Completed], 'Already Filed'],
        'missing fields on prelim → null' => ['prelim_notice', ['missing_fields_json' => ['first_furnish_date']], null],
        'missing fields on lien → null' => ['mechanics_lien', ['missing_fields_json' => ['last_furnish_date']], null],
        'not applicable on prelim → Not Applicable' => ['prelim_notice', ['status' => DeadlineStatus::NotApplicable], 'Not Applicable'],
        'not applicable on lien → Not Applicable' => ['mechanics_lien', ['status' => DeadlineStatus::NotApplicable], 'Not Applicable'],
        'lien release without lien → Lien Required' => ['lien_release', [], 'Lien Required'],
        'ready → null' => ['prelim_notice', [], null],
    ]);
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

        $this->project->refresh();
        expect($this->project->hasCompletedFilingForType('mechanics_lien'))->toBeTrue();
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

        $deadline->refresh();
        expect($deadline->canStart())->toBeTrue();
    });

    it('returns false when status is completed', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::Completed]);

        $deadline->refresh();
        expect($deadline->canStart())->toBeFalse();
    });

    it('returns false when status is not applicable', function () {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $deadline->update(['status' => DeadlineStatus::NotApplicable]);

        $deadline->refresh();
        expect($deadline->canStart())->toBeFalse();
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

        $deadline->refresh();
        expect($deadline->getButtonText())->toBe("Start filing (we'll ask a few questions)");
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
    it('returns correct status label', function (string $slug, array $updates, string $expected) {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->first();

        if ($updates) {
            $deadline->update($updates);
            $deadline->refresh();
        }

        expect($deadline->getStatusLabel())->toBe($expected);
    })->with([
        'completed → Completed' => ['prelim_notice', ['status' => DeadlineStatus::Completed], 'Completed'],
        'not applicable → N/A' => ['mechanics_lien', ['status' => DeadlineStatus::NotApplicable], 'N/A'],
        'deadline unknown → Deadline Unknown' => ['mechanics_lien', ['status' => DeadlineStatus::DeadlineUnknown], 'Deadline Unknown'],
        'not started → Not Started' => ['prelim_notice', ['status' => DeadlineStatus::NotStarted], 'Not Started'],
    ]);
});

describe('LienProjectDeadline::getStatusColor()', function () {
    it('returns correct status color', function (string $slug, array $updates, string $expected) {
        $deadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', $slug))
            ->first();

        if ($updates) {
            $deadline->update($updates);
            $deadline->refresh();
        }

        expect($deadline->getStatusColor())->toBe($expected);
    })->with([
        'completed → green' => ['prelim_notice', ['status' => DeadlineStatus::Completed], 'green'],
        'not applicable → zinc' => ['mechanics_lien', ['status' => DeadlineStatus::NotApplicable], 'zinc'],
        'deadline unknown → zinc' => ['mechanics_lien', ['status' => DeadlineStatus::DeadlineUnknown], 'zinc'],
        'not started → blue' => ['prelim_notice', ['status' => DeadlineStatus::NotStarted], 'blue'],
    ]);
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

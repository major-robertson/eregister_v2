<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\DeadlineStatus;
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

    it('returns true for lien release when mechanics lien is filed', function () {
        // First, complete the mechanics lien filing
        $lienDeadline = $this->project->deadlines()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
            ->first();

        $documentType = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $filing = LienFiling::factory()->forProject($this->project)->create([
            'document_type_id' => $documentType->id,
            'project_deadline_id' => $lienDeadline->id,
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

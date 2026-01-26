<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
use DateTimeInterface;

/**
 * Pure DTO layer for computing deadline step statuses.
 *
 * This calculator produces StepStatus objects that Blade/Livewire can consume
 * without needing to compute status logic directly.
 */
class StepStatusCalculator
{
    /**
     * Days before due date to show "Due Soon" warning.
     */
    protected const DUE_SOON_THRESHOLD_DAYS = 7;

    /**
     * Filing statuses that count as "paid" for conflict detection.
     */
    protected const PAID_STATUSES = [
        FilingStatus::Paid,
        FilingStatus::InFulfillment,
        FilingStatus::Mailed,
        FilingStatus::Recorded,
        FilingStatus::Complete,
    ];

    /**
     * Calculate status for all deadlines in a project.
     *
     * @return array<string, StepStatus> Indexed by document type slug
     */
    public function forProject(LienProject $project): array
    {
        // Ensure deadlines are loaded with required relations
        $project->loadMissing([
            'deadlines.documentType',
            'deadlines.rule',
            'deadlines.completedFiling',
            'filings.documentType',
        ]);

        // Get paid document types for conflict detection
        $paidDocTypes = $this->getPaidDocumentTypes($project);

        $steps = [];
        foreach ($project->deadlines as $deadline) {
            $slug = $deadline->documentType?->slug ?? 'unknown';
            $steps[$slug] = $this->computeStatus($deadline, $paidDocTypes);
        }

        // Determine which step is the "next step" in the workflow
        $this->markNextStep($steps);

        return $steps;
    }

    /**
     * Mark the next actionable step in the workflow.
     *
     * Workflow order: prelim_notice → noi → mechanics_lien → lien_release
     * Only REQUIRED steps that are not yet completed can be the "next step".
     *
     * @param  array<string, StepStatus>  $steps
     */
    protected function markNextStep(array $steps): void
    {
        // Statuses that count as "in progress" or "completed"
        $inProgressOrComplete = [
            DeadlineStatus::Completed,
            DeadlineStatus::Purchased,
            DeadlineStatus::InFulfillment,
        ];

        // Filing statuses that count as "paid/in-progress"
        $paidFilingStatuses = [
            FilingStatus::Paid,
            FilingStatus::InFulfillment,
            FilingStatus::Mailed,
            FilingStatus::Recorded,
            FilingStatus::Complete,
        ];

        // Workflow order for determining next step
        $workflowOrder = ['prelim_notice', 'noi', 'mechanics_lien', 'lien_release'];

        // Find the first REQUIRED, non-completed step in the workflow
        $nextStepSlug = null;
        foreach ($workflowOrder as $slug) {
            if (! isset($steps[$slug])) {
                continue;
            }

            $step = $steps[$slug];

            // Skip optional steps - they can't be the "next" step
            if ($step->isOptional()) {
                continue;
            }

            // Check if already completed or in progress
            $isComplete = in_array($step->status, $inProgressOrComplete, true);
            $hasPaidFiling = $step->filingStatus !== null && in_array($step->filingStatus, $paidFilingStatuses, true);

            if (! $isComplete && ! $hasPaidFiling) {
                $nextStepSlug = $slug;
                break;
            }
        }

        // Mark the next step
        if ($nextStepSlug !== null && isset($steps[$nextStepSlug])) {
            $steps[$nextStepSlug]->isNextStep = true;
        }
    }

    /**
     * Calculate status for a single deadline.
     */
    public function forDeadline(LienProjectDeadline $deadline): StepStatus
    {
        $deadline->loadMissing(['documentType', 'rule', 'completedFiling', 'project.filings.documentType']);

        $paidDocTypes = $this->getPaidDocumentTypes($deadline->project);

        return $this->computeStatus($deadline, $paidDocTypes);
    }

    /**
     * Compute the full StepStatus for a deadline.
     */
    protected function computeStatus(LienProjectDeadline $deadline, array $paidDocTypes): StepStatus
    {
        $docTypeSlug = $deadline->documentType?->slug ?? '';

        // Get the most relevant filing for this deadline
        $activeFiling = $this->getActiveFiling($deadline);
        $filingStatus = $activeFiling?->status;

        // Determine applicability
        $applicability = $this->determineApplicability($deadline);

        // Get missing fields info
        $missingFields = $deadline->missing_fields_json ?? [];
        $missingFieldLabels = $this->getMissingFieldLabels($missingFields);

        // Calculate deadline date info
        $deadlineDate = $deadline->due_date;
        $daysUntilDue = $this->calculateDaysUntilDue($deadlineDate);
        $isOverdue = $daysUntilDue !== null && $daysUntilDue < 0;

        // Check for purchase conflicts
        $lockedReason = $this->getLockedReason($docTypeSlug, $paidDocTypes);

        // Compute status using precedence rules
        [$status, $statusReason, $statusMeta] = $this->computeStatusByPrecedence(
            $deadline,
            $activeFiling,
            $filingStatus,
            $lockedReason,
            $applicability,
            $deadlineDate,
            $daysUntilDue,
            $isOverdue
        );

        // Determine if user can start a filing
        $canStart = $this->canStartFiling($status, $filingStatus, $lockedReason);

        // Determine if user can mark as "done myself"
        $canMarkDoneMyself = $this->canMarkDoneMyself($deadline, $paidDocTypes, $status);

        return new StepStatus(
            deadline: $deadline,
            applicability: $applicability,
            canStart: $canStart,
            canMarkDoneMyself: $canMarkDoneMyself,
            lockedReason: $lockedReason,
            missingFields: $missingFields,
            missingFieldLabels: $missingFieldLabels,
            deadlineDate: $deadlineDate,
            daysUntilDue: $daysUntilDue,
            isOverdue: $isOverdue,
            activeFiling: $activeFiling,
            filingStatus: $filingStatus,
            status: $status,
            statusReason: $statusReason,
            statusMeta: $statusMeta,
        );
    }

    /**
     * Compute status using the defined precedence rules.
     *
     * Precedence (in order):
     * 1. completed_filing_id OR completed_externally_at → Completed
     * 2. filing.status = Complete → Completed
     * 3. filing.status = Recorded → Completed
     * 4. filing.status = Mailed → Completed
     * 5. filing.status = InFulfillment → InFulfillment
     * 6. filing.status = Paid → Purchased
     * 7. filing.status = AwaitingPayment → AwaitingPayment
     * 8. locked_by_conflict → Locked
     * 9. filing.status = Draft → InDraft
     * 10. applicability = not_applicable → NotApplicable
     * 11. due_date is null → DeadlineUnknown
     * 12. due_date < today → Missed
     * 13. due_date <= today + threshold → DueSoon
     * 14. else → NotStarted
     *
     * @return array{DeadlineStatus, ?string, ?array}
     */
    protected function computeStatusByPrecedence(
        LienProjectDeadline $deadline,
        ?LienFiling $activeFiling,
        ?FilingStatus $filingStatus,
        ?string $lockedReason,
        string $applicability,
        ?DateTimeInterface $deadlineDate,
        ?int $daysUntilDue,
        bool $isOverdue
    ): array {
        $statusReason = $deadline->status_reason;
        $statusMeta = $deadline->status_meta;

        // 1. Completed via filing or external
        if ($deadline->completed_filing_id !== null || $deadline->completed_externally_at !== null) {
            return [DeadlineStatus::Completed, $statusReason, $statusMeta];
        }

        // 2-4. Filing is Complete/Recorded/Mailed → Completed
        if ($filingStatus !== null && in_array($filingStatus, [
            FilingStatus::Complete,
            FilingStatus::Recorded,
            FilingStatus::Mailed,
        ], true)) {
            return [DeadlineStatus::Completed, $statusReason, $statusMeta];
        }

        // 5. Filing is InFulfillment → InFulfillment
        if ($filingStatus === FilingStatus::InFulfillment) {
            return [DeadlineStatus::InFulfillment, $statusReason, $statusMeta];
        }

        // 6. Filing is Paid → Purchased
        if ($filingStatus === FilingStatus::Paid) {
            return [DeadlineStatus::Purchased, $statusReason, $statusMeta];
        }

        // 7. Filing is AwaitingPayment → AwaitingPayment
        if ($filingStatus === FilingStatus::AwaitingPayment) {
            return [DeadlineStatus::AwaitingPayment, $statusReason, $statusMeta];
        }

        // 8. Locked by purchase conflict
        if ($lockedReason !== null) {
            return [DeadlineStatus::Locked, 'purchase_conflict', ['reason' => $lockedReason]];
        }

        // 9. Filing is Draft → InDraft
        if ($filingStatus === FilingStatus::Draft) {
            return [DeadlineStatus::InDraft, $statusReason, $statusMeta];
        }

        // 10. Not applicable (no rights, blocked, etc.)
        if ($applicability === 'not_applicable') {
            return [DeadlineStatus::NotApplicable, $statusReason, $statusMeta];
        }

        // 11. Missing deadline date
        if ($deadlineDate === null) {
            $docSlug = $deadline->documentType?->slug ?? '';

            // For optional documents (no rule), don't show "Deadline Unknown" - just show NotStarted
            if ($applicability === 'optional') {
                return [DeadlineStatus::NotStarted, $statusReason, $statusMeta];
            }

            // Lien release deadline depends on mechanics lien being filed - show NotStarted until then
            if ($docSlug === 'lien_release') {
                return [DeadlineStatus::NotStarted, $statusReason, $statusMeta];
            }

            // For required documents with missing dates, show DeadlineUnknown
            return [DeadlineStatus::DeadlineUnknown, $statusReason, $statusMeta];
        }

        // 12. Overdue → Missed
        if ($isOverdue) {
            return [DeadlineStatus::Missed, $statusReason, $statusMeta];
        }

        // 13. Due soon → DueSoon
        if ($daysUntilDue !== null && $daysUntilDue <= self::DUE_SOON_THRESHOLD_DAYS) {
            return [DeadlineStatus::DueSoon, $statusReason, $statusMeta];
        }

        // 14. Default → NotStarted
        return [DeadlineStatus::NotStarted, $statusReason, $statusMeta];
    }

    /**
     * Get document types that have been paid for (for conflict detection).
     *
     * @return array<string>
     */
    protected function getPaidDocumentTypes(LienProject $project): array
    {
        return $project->filings
            ->filter(fn (LienFiling $f) => in_array($f->status, self::PAID_STATUSES, true))
            ->map(fn (LienFiling $f) => $f->documentType?->slug)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if a document type is locked due to purchase conflict.
     */
    protected function getLockedReason(string $docTypeSlug, array $paidDocTypes): ?string
    {
        // Paid lien locks NOI and prelim
        if (in_array('mechanics_lien', $paidDocTypes, true)) {
            if (in_array($docTypeSlug, ['noi', 'prelim_notice'], true)) {
                return 'Lien already purchased';
            }
        }

        // Can't re-purchase same doc type
        if (in_array($docTypeSlug, $paidDocTypes, true)) {
            return 'Already purchased';
        }

        return null;
    }

    /**
     * Get the most relevant active filing for a deadline.
     */
    protected function getActiveFiling(LienProjectDeadline $deadline): ?LienFiling
    {
        // First check for completed filing
        if ($deadline->completedFiling) {
            return $deadline->completedFiling;
        }

        // Then look for any active filing for this document type
        return $deadline->project->filings
            ->where('document_type_id', $deadline->document_type_id)
            ->whereNotIn('status', [FilingStatus::Canceled])
            ->sortByDesc(fn (LienFiling $f) => $this->getFilingPriority($f->status))
            ->first();
    }

    /**
     * Get priority for filing status (higher = more important).
     */
    protected function getFilingPriority(FilingStatus $status): int
    {
        return match ($status) {
            FilingStatus::Complete => 100,
            FilingStatus::Recorded => 90,
            FilingStatus::Mailed => 80,
            FilingStatus::InFulfillment => 70,
            FilingStatus::Paid => 60,
            FilingStatus::AwaitingPayment => 50,
            FilingStatus::Draft => 40,
            FilingStatus::Canceled => 0,
        };
    }

    /**
     * Determine applicability based on rule and deadline state.
     */
    protected function determineApplicability(LienProjectDeadline $deadline): string
    {
        // Check if the old status was NotApplicable or Blocked
        $oldStatus = $deadline->getRawOriginal('status') ?? $deadline->getAttributes()['status'] ?? null;
        if ($oldStatus === 'not_applicable' || $oldStatus === 'blocked') {
            return 'not_applicable';
        }

        $docSlug = $deadline->documentType?->slug ?? '';

        // Lien release is never "optional" - it's required once lien is filed
        if ($docSlug === 'lien_release') {
            return 'required';
        }

        // No rule exists (placeholder deadline) - treat as optional
        if ($deadline->rule === null) {
            return 'optional';
        }

        // Check rule's is_required flag
        if ($deadline->rule->is_required === false) {
            return 'optional';
        }

        return 'required';
    }

    /**
     * Calculate days until due date.
     */
    protected function calculateDaysUntilDue(?DateTimeInterface $dueDate): ?int
    {
        if ($dueDate === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($dueDate, false);
    }

    /**
     * Check if user can start a new filing.
     */
    protected function canStartFiling(DeadlineStatus $status, ?FilingStatus $filingStatus, ?string $lockedReason): bool
    {
        // Can't start if there's a lock
        if ($lockedReason !== null) {
            return false;
        }

        // Can't start if completed or not applicable
        if ($status->isTerminal()) {
            return false;
        }

        // Can continue if in draft
        if ($filingStatus === FilingStatus::Draft) {
            return true;
        }

        // Can't start if already has active paid filing
        if ($filingStatus !== null && in_array($filingStatus, self::PAID_STATUSES, true)) {
            return false;
        }

        // Can start in all other cases
        return true;
    }

    /**
     * Check if user can mark this step as "done myself".
     */
    protected function canMarkDoneMyself(LienProjectDeadline $deadline, array $paidDocTypes, DeadlineStatus $status): bool
    {
        // Can't mark if already completed
        if ($status === DeadlineStatus::Completed) {
            return false;
        }

        // Can't mark if not applicable
        if ($status === DeadlineStatus::NotApplicable) {
            return false;
        }

        $docTypeSlug = $deadline->documentType?->slug ?? '';

        // Can mark even if locked (this overrides the purchase)
        // But we'll show a confirmation in the UI

        // Can't mark if there's a paid filing for this exact doc type
        // (would create confusing state - show confirmation modal instead)
        if (in_array($docTypeSlug, $paidDocTypes, true)) {
            // Return true but UI should show confirmation
            return true;
        }

        return true;
    }

    /**
     * Get human-readable labels for missing fields.
     *
     * @param  array<string>  $missingFields
     * @return array<string>
     */
    protected function getMissingFieldLabels(array $missingFields): array
    {
        $labels = config('lien.missing_field_labels', []);

        return array_map(
            fn (string $field) => $labels[$field] ?? str_replace('_', ' ', $field),
            $missingFields
        );
    }
}

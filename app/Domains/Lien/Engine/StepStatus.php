<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProjectDeadline;
use DateTimeInterface;

/**
 * DTO representing the computed status of a deadline step.
 *
 * This is a pure "view model" layer - Blade/Livewire should only consume this,
 * never compute status logic directly.
 */
class StepStatus
{
    // Whether this is the next actionable step in the workflow (set after all steps computed)
    public bool $isNextStep = false;

    public function __construct(
        public readonly LienProjectDeadline $deadline,
        public readonly string $applicability,         // required | optional | not_applicable
        public readonly bool $canStart,                // true unless Completed/NotApplicable/has active filing
        public readonly bool $canMarkDoneMyself,       // true unless paid/complete filing exists
        public readonly ?string $lockedReason,         // purchase conflict reason
        public readonly array $missingFields,          // field keys for "deadline unknown"
        public readonly array $missingFieldLabels,     // human-readable field labels
        public readonly ?DateTimeInterface $deadlineDate, // null if unknown
        public readonly ?int $daysUntilDue,            // precomputed for UI
        public readonly bool $isOverdue,               // precomputed
        public readonly ?LienFiling $activeFiling,     // current filing (draft, awaiting, in progress)
        public readonly ?FilingStatus $filingStatus,   // filing status if exists
        public readonly DeadlineStatus $status,        // computed final status
        public readonly ?string $statusReason,         // the "why" (restriction type, etc.)
        public readonly ?array $statusMeta,            // additional context
    ) {}

    /**
     * Check if there's a draft filing for this deadline.
     */
    public function hasDraft(): bool
    {
        return $this->filingStatus === FilingStatus::Draft;
    }

    /**
     * Check if there's an active filing (any non-canceled, non-complete).
     */
    public function hasActiveFiling(): bool
    {
        return $this->activeFiling !== null;
    }

    /**
     * Get the document type slug for this step.
     */
    public function getDocumentTypeSlug(): string
    {
        return $this->deadline->documentType?->slug ?? '';
    }

    /**
     * Get the document type name for this step.
     */
    public function getDocumentTypeName(): string
    {
        return $this->deadline->documentType?->name ?? '';
    }

    /**
     * Check if this is a required step.
     */
    public function isRequired(): bool
    {
        return $this->applicability === 'required';
    }

    /**
     * Check if this is an optional step.
     */
    public function isOptional(): bool
    {
        return $this->applicability === 'optional';
    }

    /**
     * Get the appropriate button text for starting/continuing a filing.
     */
    public function getActionButtonText(): string
    {
        if ($this->hasDraft()) {
            return 'Continue';
        }

        if ($this->status === DeadlineStatus::AwaitingPayment) {
            return 'Complete Payment';
        }

        return 'Start';
    }

    /**
     * Check if we should show the filing action button.
     */
    public function shouldShowActionButton(): bool
    {
        // Don't show if completed or not applicable
        if ($this->status->isTerminal()) {
            return false;
        }

        // Show "View" for in-progress filings instead of action button
        if (in_array($this->status, [
            DeadlineStatus::Purchased,
            DeadlineStatus::InFulfillment,
        ], true)) {
            return false;
        }

        return $this->canStart || $this->hasDraft();
    }
}

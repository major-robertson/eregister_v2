<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use Carbon\CarbonInterface;

class DeadlineCalculator
{
    /**
     * Calculate and store deadlines for a project based on its state and claimant type.
     */
    public function calculateForProject(LienProject $project): void
    {
        if (! $project->jobsite_state) {
            return;
        }

        // Load state rule once for all deadline calculations
        $stateRule = LienStateRule::where('state', $project->jobsite_state)->first();
        if (! $stateRule) {
            return;
        }

        $effectiveScope = $this->determineEffectiveScope($project);

        $rules = LienDeadlineRule::forStateAndClaimant(
            $project->jobsite_state,
            $project->claimant_type,
            $effectiveScope
        );

        // Ensure documentType relationship is loaded
        $rules->load('documentType');

        foreach ($rules as $rule) {
            // START WITH DEFAULTS
            $status = DeadlineStatus::Pending;
            $statusReason = null;
            $statusMeta = [];
            $anchorDate = null;
            $dueDate = null;
            $missingFields = [];

            // 1. Check claimant has lien rights (NotApplicable check)
            $claimantCheck = $this->checkClaimantRights($project, $stateRule, $rule);
            if (! $claimantCheck['allowed']) {
                $status = DeadlineStatus::NotApplicable;
                $statusReason = 'no_lien_rights_for_claimant';
                $statusMeta = $claimantCheck['meta'];
                $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, null);

                continue;
            }

            // 2. Determine anchor date
            $anchorDate = $this->resolveAnchor($project, $rule, $stateRule);
            if (! $anchorDate) {
                $missingFields = [$rule->trigger_event->value];
                $status = DeadlineStatus::NotApplicable;
                $statusReason = 'missing_anchor_date';
                $statusMeta = ['missing_fields' => $missingFields];
                $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, null, $missingFields);

                continue;
            }

            // 3. Calculate base deadline
            $dueDate = $this->calculateDueDate($anchorDate, $rule);

            // 4. Apply NOC logic (BLOCKS take priority) - only for mechanics lien
            if ($rule->documentType && $rule->documentType->slug === 'mechanics_lien') {
                $nocResult = $this->applyNocLogic($project, $stateRule, $dueDate);

                if ($nocResult['blocked']) {
                    $status = DeadlineStatus::Blocked;
                    $statusReason = 'noc_requires_prior_prelim';
                    $statusMeta = $nocResult['meta'];
                    $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, $anchorDate);

                    continue;
                }

                // NOC shortened the deadline
                if ($nocResult['shortened']) {
                    $dueDate = $nocResult['deadline'];
                    $statusMeta['noc_shortened'] = true;
                    $statusMeta['original_due_date'] = $nocResult['original_due_date']->toDateString();
                    $statusMeta['noc_due_date'] = $nocResult['deadline']->toDateString();
                    $statusMeta['shorten_days'] = $stateRule->lien_after_noc_days;
                }
            }

            // 5. Check property restrictions (only if not already blocked) - only for mechanics lien
            if ($rule->documentType && $rule->documentType->slug === 'mechanics_lien') {
                $propCheck = $this->checkPropertyRestrictions($project, $stateRule);
                if ($propCheck['blocked']) {
                    $status = DeadlineStatus::Blocked;
                    $statusReason = $propCheck['reason'];
                    $statusMeta = array_merge($statusMeta, $propCheck['meta']);
                } elseif ($propCheck['warning'] && $status !== DeadlineStatus::Blocked) {
                    $status = DeadlineStatus::Warning;
                    $statusReason = $propCheck['reason'];
                    $statusMeta = array_merge($statusMeta, $propCheck['meta']);
                }
            }

            // 6. Check for existing completed filing
            $existingDeadline = $project->deadlines()->where('deadline_rule_id', $rule->id)->first();
            if ($existingDeadline?->completed_filing_id) {
                $status = DeadlineStatus::Completed;
                $statusReason = null;
                $statusMeta = [];
            }

            // 7. Upsert deadline
            $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, $dueDate, $anchorDate, $missingFields);
        }
    }

    /**
     * Upsert a deadline record with all metadata.
     */
    protected function upsertDeadline(
        LienProject $project,
        LienDeadlineRule $rule,
        DeadlineStatus $status,
        ?string $statusReason,
        array $statusMeta,
        ?CarbonInterface $dueDate,
        ?CarbonInterface $anchorDate,
        array $missingFields = []
    ): void {
        $existingDeadline = $project->deadlines()->where('deadline_rule_id', $rule->id)->first();

        $project->deadlines()->updateOrCreate(
            ['deadline_rule_id' => $rule->id],
            [
                'business_id' => $project->business_id,
                'document_type_id' => $rule->document_type_id,
                'due_date' => $dueDate,
                'computed_from_date' => $anchorDate,
                'missing_fields_json' => $missingFields ?: null,
                'status' => $existingDeadline?->completed_filing_id ? DeadlineStatus::Completed : $status,
                'status_reason' => $statusReason,
                'status_meta' => empty($statusMeta) ? null : $statusMeta,
                'completed_filing_id' => $existingDeadline?->completed_filing_id,
            ]
        );
    }

    /**
     * Check if claimant type has lien rights in this state.
     */
    protected function checkClaimantRights(LienProject $project, LienStateRule $stateRule, LienDeadlineRule $rule): array
    {
        // Only check lien rights for lien-related document types
        if (! $rule->documentType || ! in_array($rule->documentType->slug, ['mechanics_lien', 'lien_enforcement'])) {
            return ['allowed' => true, 'meta' => []];
        }

        $claimantType = $project->claimant_type?->value;

        $hasRights = match ($claimantType) {
            'gc' => $stateRule->gc_has_lien_rights,
            'subcontractor' => $stateRule->sub_has_lien_rights,
            'sub_sub_contractor' => $stateRule->subsub_has_lien_rights,
            'supplier_to_owner' => $stateRule->supplier_owner_has_lien_rights,
            'supplier_to_contractor' => $stateRule->supplier_gc_has_lien_rights,
            'supplier_to_subcontractor' => $stateRule->supplier_sub_has_lien_rights,
            default => true, // Assume rights if unknown claimant type
        };

        return [
            'allowed' => $hasRights,
            'meta' => $hasRights ? [] : [
                'claimant_type' => $claimantType,
                'rule_field' => $this->getClaimantRuleField($claimantType),
                'state' => $stateRule->state,
            ],
        ];
    }

    /**
     * Get the state rule field name for a claimant type.
     */
    protected function getClaimantRuleField(string $claimantType): string
    {
        return match ($claimantType) {
            'gc' => 'gc_has_lien_rights',
            'subcontractor' => 'sub_has_lien_rights',
            'sub_sub_contractor' => 'subsub_has_lien_rights',
            'supplier_to_owner' => 'supplier_owner_has_lien_rights',
            'supplier_to_contractor' => 'supplier_gc_has_lien_rights',
            'supplier_to_subcontractor' => 'supplier_sub_has_lien_rights',
            default => 'unknown',
        };
    }

    /**
     * Unified accessor for event dates (project fields + derived dates).
     */
    protected function getEventDate(LienProject $project, string $event): ?CarbonInterface
    {
        return match ($event) {
            // Direct project fields
            'first_furnish_date' => $project->first_furnish_date,
            'last_furnish_date' => $project->last_furnish_date,
            'completion_date' => $project->completion_date,
            'noc_filed_date' => $project->noc_filed_date,
            'noc_recorded_date' => $project->noc_recorded_at,
            'prelim_notice_sent_at' => $project->prelim_notice_sent_at,
            'contract_date' => $project->first_furnish_date, // Fallback to first furnish if no contract date

            // Derived from completed filings
            'lien_recorded_date' => $project->deadlines()
                ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
                ->whereNotNull('completed_filing_id')
                ->first()
                ?->completedFiling
                ?->recorded_at,

            'lien_filing_date' => $project->deadlines()
                ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
                ->whereNotNull('completed_filing_id')
                ->first()
                ?->completedFiling
                ?->submitted_at,

            'prelim_sent_date' => $project->deadlines()
                ->whereHas('documentType', fn ($q) => $q->where('slug', 'prelim_notice'))
                ->whereNotNull('completed_filing_id')
                ->first()
                ?->completedFiling
                ?->submitted_at,

            default => null,
        };
    }

    /**
     * Apply NOC (Notice of Completion) logic.
     * Returns blocked status if rights eliminated, or shortened deadline if applicable.
     */
    protected function applyNocLogic(
        LienProject $project,
        LienStateRule $stateRule,
        ?CarbonInterface $baseDeadline
    ): array {
        if (! $project->noc_filed_date) {
            return ['blocked' => false, 'shortened' => false, 'deadline' => $baseDeadline];
        }

        // Path 1: Rights elimination check (HARD BLOCK)
        if ($stateRule->noc_eliminates_rights_if_no_prelim && $stateRule->noc_requires_prior_prelim) {
            $prelimSentAt = $this->getEventDate($project, 'prelim_notice_sent_at')
                ?? $this->getEventDate($project, 'prelim_sent_date');

            // Check prelim sent BEFORE NOC filed
            if (! $prelimSentAt || $prelimSentAt->greaterThan($project->noc_filed_date)) {
                return [
                    'blocked' => true,
                    'shortened' => false,
                    'deadline' => null,
                    'meta' => [
                        'noc_filed_date' => $project->noc_filed_date->toDateString(),
                        'prelim_sent_date' => $prelimSentAt?->toDateString(),
                    ],
                ];
            }
        }

        // Path 2: Deadline shortening
        if ($stateRule->noc_shortens_deadline && $stateRule->lien_after_noc_days && $baseDeadline) {
            $nocDeadline = $project->noc_filed_date->copy()->addDays($stateRule->lien_after_noc_days);

            if ($nocDeadline->lt($baseDeadline)) {
                return [
                    'blocked' => false,
                    'shortened' => true,
                    'deadline' => $nocDeadline,
                    'original_due_date' => $baseDeadline,
                    'meta' => [],
                ];
            }
        }

        return ['blocked' => false, 'shortened' => false, 'deadline' => $baseDeadline];
    }

    /**
     * Check property restrictions (tenant/owner-occupied).
     */
    protected function checkPropertyRestrictions(LienProject $project, LienStateRule $stateRule): array
    {
        $propertyContext = $project->property_context ?? 'unknown';

        // Tenant restrictions (HARD BLOCK)
        if ($propertyContext === 'tenant_improvement' && ! $stateRule->tenant_project_lien_allowed) {
            return [
                'blocked' => true,
                'warning' => null,
                'reason' => 'tenant_project_not_allowed',
                'meta' => [
                    'property_context' => $propertyContext,
                    'restriction' => 'not_allowed',
                ],
            ];
        }

        // Tenant restrictions (WARNING)
        if ($propertyContext === 'tenant_improvement' && $stateRule->tenant_project_restrictions !== 'none') {
            return [
                'blocked' => false,
                'warning' => true,
                'reason' => 'tenant_project_restrictions',
                'meta' => [
                    'property_context' => $propertyContext,
                    'restriction_type' => $stateRule->tenant_project_restrictions,
                ],
            ];
        }

        // Owner-occupied restrictions (WARNING)
        if ($propertyContext === 'owner_occupied' && $stateRule->owner_occupied_restriction_type !== 'none') {
            return [
                'blocked' => false,
                'warning' => true,
                'reason' => 'owner_occupied_restrictions',
                'meta' => [
                    'property_context' => $propertyContext,
                    'restriction_type' => $stateRule->owner_occupied_restriction_type,
                ],
            ];
        }

        // Unknown property type (WARNING if restrictions exist in state)
        if ($propertyContext === 'unknown') {
            if ($stateRule->owner_occupied_restriction_type !== 'none' || $stateRule->tenant_project_restrictions !== 'none') {
                return [
                    'blocked' => false,
                    'warning' => true,
                    'reason' => 'unknown_property_type',
                    'meta' => [
                        'property_context' => $propertyContext,
                        'needs_property_context' => true,
                    ],
                ];
            }
        }

        return ['blocked' => false, 'warning' => null, 'reason' => null, 'meta' => []];
    }

    /**
     * Resolve the anchor date for the rule, handling later_of/earlier_of conditions.
     */
    protected function resolveAnchor(LienProject $project, LienDeadlineRule $rule, LienStateRule $stateRule): ?CarbonInterface
    {
        $conditions = $rule->conditions_json;
        $anchorLogic = $conditions['anchor'] ?? $stateRule->lien_anchor_logic ?? 'single';

        if ($anchorLogic === 'later_of') {
            $dates = collect($conditions['dates'] ?? [])
                ->map(fn ($field) => $this->getEventDate($project, $field))
                ->filter()
                ->values();

            if ($dates->isEmpty()) {
                return null;
            }

            return $dates->reduce(fn ($carry, $date) => $carry === null || $date->greaterThan($carry) ? $date : $carry);
        }

        if ($anchorLogic === 'earlier_of') {
            $dates = collect($conditions['dates'] ?? [])
                ->map(fn ($field) => $this->getEventDate($project, $field))
                ->filter()
                ->values();

            if ($dates->isEmpty()) {
                return null;
            }

            return $dates->reduce(fn ($carry, $date) => $carry === null || $date->lessThan($carry) ? $date : $carry);
        }

        // Default 'single': use the trigger_event field
        return $this->getEventDate($project, $rule->trigger_event->value);
    }

    /**
     * Calculate the due date based on the anchor date and rule's calc_method.
     */
    protected function calculateDueDate(CarbonInterface $anchorDate, LienDeadlineRule $rule): CarbonInterface
    {
        $calcMethod = $rule->calc_method ?? CalcMethod::DaysAfterDate;

        return match ($calcMethod) {
            CalcMethod::DaysAfterDate => $anchorDate->copy()->addDays($rule->offset_days),

            // Use addMonthsNoOverflow to prevent Jan 31 + 1 month = Mar 2 issues
            CalcMethod::MonthsAfterDate => $anchorDate->copy()
                ->addMonthsNoOverflow($rule->offset_months),

            // "15th of the Nth month after the month of X"
            // Normalize to start of month first for deterministic results
            CalcMethod::MonthDayAfterMonthOfDate => $anchorDate->copy()
                ->startOfMonth()
                ->addMonthsNoOverflow($rule->offset_months)
                ->setDay($rule->day_of_month),

            // VA: "90 days from end of month of last work"
            // First go to end of anchor month, then add days
            CalcMethod::DaysAfterEndOfMonthOfDate => $anchorDate->copy()
                ->endOfMonth()
                ->addDays($rule->offset_days),
        };
    }

    /**
     * Determine the effective scope (residential/commercial) for rule selection.
     * Defaults to 'residential' (conservative - shorter deadlines) if unknown.
     */
    protected function determineEffectiveScope(LienProject $project): string
    {
        // Check for explicit project_type if set
        $projectType = $project->project_type ?? null;

        if ($projectType) {
            // Map common project type values to scope
            $residentialTypes = ['residential', 'single_family', 'multi_family', 'home', 'house'];
            $commercialTypes = ['commercial', 'industrial', 'retail', 'office'];

            $normalized = strtolower($projectType);
            if (in_array($normalized, $residentialTypes, true)) {
                return 'residential';
            }
            if (in_array($normalized, $commercialTypes, true)) {
                return 'commercial';
            }
        }

        // Default to residential (conservative - shorter deadlines in TX/NY)
        return 'residential';
    }

    /**
     * Recalculate deadlines for all projects in a given state.
     * Useful when deadline rules are updated.
     */
    public function recalculateForState(string $state): int
    {
        $count = 0;

        LienProject::where('jobsite_state', $state)
            ->chunk(100, function ($projects) use (&$count) {
                foreach ($projects as $project) {
                    $this->calculateForProject($project);
                    $count++;
                }
            });

        return $count;
    }
}

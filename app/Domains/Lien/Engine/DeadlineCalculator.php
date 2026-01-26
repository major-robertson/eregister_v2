<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienProjectDeadline;
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

        // Pre-calculate lien deadline for NOI derivation
        $lienDeadlineData = null;

        foreach ($rules as $rule) {
            // START WITH DEFAULTS
            $status = DeadlineStatus::NotStarted;
            $statusReason = null;
            $statusMeta = [];
            $anchorDate = null;
            $dueDate = null;
            $missingFields = [];

            $docSlug = $rule->documentType?->slug;

            // 1. Check claimant has lien rights (NotApplicable check)
            $claimantCheck = $this->checkClaimantRights($project, $stateRule, $rule);
            if (! $claimantCheck['allowed']) {
                $status = DeadlineStatus::NotApplicable;
                $statusReason = 'no_lien_rights_for_claimant';
                $statusMeta = $claimantCheck['meta'];
                $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, null);

                continue;
            }

            // 2. Special handling for NOI - derive from lien deadline
            if ($docSlug === 'noi') {
                // Calculate lien deadline if not already done
                if ($lienDeadlineData === null) {
                    $lienDeadlineData = $this->calculateLienDeadline($project, $stateRule, $rules);
                }

                $noiResult = $this->calculateNoiDeadline($project, $stateRule, $lienDeadlineData);
                $dueDate = $noiResult['due_date'];
                $missingFields = $noiResult['missing_fields'];
                $statusMeta = $noiResult['status_meta'];

                if ($dueDate === null && ! empty($missingFields)) {
                    $status = DeadlineStatus::DeadlineUnknown;
                    $statusReason = 'missing_anchor_date';
                    $statusMeta['missing_fields'] = $missingFields;
                    $statusMeta['derived_from'] = 'mechanics_lien';
                    $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, null, $missingFields);

                    continue;
                }

                // Check for existing completed filing or external completion
                $existingDeadline = $project->deadlines()->where('deadline_rule_id', $rule->id)->first();
                if ($existingDeadline?->completed_filing_id || $existingDeadline?->completed_externally_at) {
                    $status = DeadlineStatus::Completed;
                    $statusReason = null;
                    $statusMeta = [];
                }

                $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, $dueDate, null, $missingFields);

                continue;
            }

            // 3. Determine anchor date for non-NOI documents
            $anchorDate = $this->resolveAnchor($project, $rule, $stateRule);
            if (! $anchorDate) {
                $missingFields = [$rule->trigger_event->value];
                $status = DeadlineStatus::DeadlineUnknown;
                $statusReason = 'missing_anchor_date';
                $statusMeta = ['missing_fields' => $missingFields];
                $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, null, null, $missingFields);

                continue;
            }

            // 4. Calculate base deadline
            $dueDate = $this->calculateDueDate($anchorDate, $rule);

            // 5. Apply NOC logic (BLOCKS take priority) - only for mechanics lien
            if ($docSlug === 'mechanics_lien') {
                $nocResult = $this->applyNocLogic($project, $stateRule, $dueDate);

                if ($nocResult['blocked']) {
                    $status = DeadlineStatus::NotApplicable;
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

                // Store lien deadline data for NOI calculation
                $lienDeadlineData = [
                    'due_date' => $dueDate,
                    'missing_fields' => [],
                    'is_missed' => $dueDate?->isPast() ?? false,
                ];
            }

            // 6. Check property restrictions (only if not already blocked) - only for mechanics lien
            if ($docSlug === 'mechanics_lien') {
                $propCheck = $this->checkPropertyRestrictions($project, $stateRule);
                if ($propCheck['blocked']) {
                    $status = DeadlineStatus::NotApplicable;
                    $statusReason = $propCheck['reason'];
                    $statusMeta = array_merge($statusMeta, $propCheck['meta']);
                } elseif ($propCheck['warning']) {
                    // Store warning info in status_meta, but don't change status
                    // The StepStatusCalculator will handle displaying warnings
                    $statusMeta['has_property_warning'] = true;
                    $statusMeta['property_warning_reason'] = $propCheck['reason'];
                    $statusMeta = array_merge($statusMeta, $propCheck['meta']);
                }
            }

            // 7. Check for existing completed filing or external completion
            $existingDeadline = $project->deadlines()->where('deadline_rule_id', $rule->id)->first();
            if ($existingDeadline?->completed_filing_id || $existingDeadline?->completed_externally_at) {
                $status = DeadlineStatus::Completed;
                $statusReason = null;
                $statusMeta = [];
            }

            // 8. Upsert deadline
            $this->upsertDeadline($project, $rule, $status, $statusReason, $statusMeta, $dueDate, $anchorDate, $missingFields);
        }

        // 9. Ensure all document types have deadline records (even if optional/no rule)
        $this->ensureAllDocumentTypesExist($project);
    }

    /**
     * Ensure all document types have deadline records for the project.
     * Creates optional placeholder deadlines for any missing document types.
     */
    protected function ensureAllDocumentTypesExist(LienProject $project): void
    {
        $allDocTypes = LienDocumentType::orderBy('id')->get();
        $existingDocTypeIds = $project->deadlines()->pluck('document_type_id')->toArray();

        foreach ($allDocTypes as $docType) {
            if (! in_array($docType->id, $existingDocTypeIds)) {
                // Create optional placeholder deadline (no rule exists for this doc type)
                LienProjectDeadline::create([
                    'project_id' => $project->id,
                    'document_type_id' => $docType->id,
                    'deadline_rule_id' => null,
                    'status' => DeadlineStatus::NotStarted,
                    'status_reason' => 'optional_no_rule',
                    'status_meta' => null,
                    'due_date' => null,
                    'anchor_date' => null,
                    'missing_fields_json' => null,
                ]);
            }
        }
    }

    /**
     * Calculate the lien deadline for NOI derivation.
     *
     * @return array{due_date: ?CarbonInterface, missing_fields: array, is_missed: bool}
     */
    protected function calculateLienDeadline(LienProject $project, LienStateRule $stateRule, $rules): array
    {
        $lienRule = $rules->first(fn ($r) => $r->documentType?->slug === 'mechanics_lien');

        if (! $lienRule) {
            return ['due_date' => null, 'missing_fields' => [], 'is_missed' => false];
        }

        $anchorDate = $this->resolveAnchor($project, $lienRule, $stateRule);

        if (! $anchorDate) {
            return [
                'due_date' => null,
                'missing_fields' => [$lienRule->trigger_event->value],
                'is_missed' => false,
            ];
        }

        $dueDate = $this->calculateDueDate($anchorDate, $lienRule);

        // Apply NOC shortening if applicable
        $nocResult = $this->applyNocLogic($project, $stateRule, $dueDate);
        if ($nocResult['shortened']) {
            $dueDate = $nocResult['deadline'];
        }

        return [
            'due_date' => $dueDate,
            'missing_fields' => [],
            'is_missed' => $dueDate->isPast(),
        ];
    }

    /**
     * Calculate NOI deadline derived from lien deadline with guardrails.
     *
     * NOI due date = lien_due - noi_lead_time_days
     *
     * @return array{due_date: ?CarbonInterface, missing_fields: array, status_meta: array}
     */
    protected function calculateNoiDeadline(LienProject $project, LienStateRule $stateRule, ?array $lienDeadlineData): array
    {
        $statusMeta = [];

        // If lien deadline couldn't be calculated, inherit its missing fields
        if ($lienDeadlineData === null || $lienDeadlineData['due_date'] === null) {
            return [
                'due_date' => null,
                'missing_fields' => $lienDeadlineData['missing_fields'] ?? ['last_furnish_date'],
                'status_meta' => ['derived_from' => 'mechanics_lien'],
            ];
        }

        $lienDueDate = $lienDeadlineData['due_date'];

        // Get lead time with guardrails - clamp negative/null to 0
        $leadTimeDays = max(0, $stateRule->noi_lead_time_days ?? 0);

        // Calculate NOI due date
        $noiDueDate = $lienDueDate->copy()->subDays($leadTimeDays);

        // Guardrail: If NOI due ends up after lien due (bad data), clamp to lien due
        if ($noiDueDate->greaterThan($lienDueDate)) {
            $noiDueDate = $lienDueDate->copy();
            $statusMeta['noi_clamped'] = true;
            $statusMeta['original_lead_time'] = $stateRule->noi_lead_time_days;
        }

        // If lien deadline is missed, flag it in meta
        if ($lienDeadlineData['is_missed']) {
            $statusMeta['lien_is_missed'] = true;
        }

        $statusMeta['derived_from_lien_due'] = $lienDueDate->toDateString();
        $statusMeta['lead_time_days'] = $leadTimeDays;

        return [
            'due_date' => $noiDueDate,
            'missing_fields' => [],
            'status_meta' => $statusMeta,
        ];
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

        // Preserve completion state
        $finalStatus = $status;
        if ($existingDeadline?->completed_filing_id || $existingDeadline?->completed_externally_at) {
            $finalStatus = DeadlineStatus::Completed;
        }

        $project->deadlines()->updateOrCreate(
            ['deadline_rule_id' => $rule->id],
            [
                'business_id' => $project->business_id,
                'document_type_id' => $rule->document_type_id,
                'due_date' => $dueDate,
                'computed_from_date' => $anchorDate,
                'missing_fields_json' => $missingFields ?: null,
                'status' => $finalStatus,
                'status_reason' => $statusReason,
                'status_meta' => empty($statusMeta) ? null : $statusMeta,
                'completed_filing_id' => $existingDeadline?->completed_filing_id,
                // Preserve external completion fields
                'completed_externally_at' => $existingDeadline?->completed_externally_at,
                'external_filed_at' => $existingDeadline?->external_filed_at,
                'external_completion_note' => $existingDeadline?->external_completion_note,
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
        // If property_class is set (residential/commercial/government), no warning needed
        // The old property_context field is deprecated in favor of property_class
        $propertyClass = $project->property_class;

        if ($propertyClass !== null) {
            // Property type is known, no warning needed
            return ['blocked' => false, 'warning' => false, 'reason' => null, 'meta' => []];
        }

        // Property class not set - show warning if state has restrictions
        if ($stateRule->owner_occupied_restriction_type !== 'none' || $stateRule->tenant_project_restrictions !== 'none') {
            return [
                'blocked' => false,
                'warning' => true,
                'reason' => 'unknown_property_type',
                'meta' => [
                    'needs_property_class' => true,
                ],
            ];
        }

        return ['blocked' => false, 'warning' => false, 'reason' => null, 'meta' => []];
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
            CalcMethod::DaysAfterDate => $anchorDate->copy()->addDays($rule->offset_days ?? 0),

            // Use addMonthsNoOverflow to prevent Jan 31 + 1 month = Mar 2 issues
            CalcMethod::MonthsAfterDate => $anchorDate->copy()
                ->addMonthsNoOverflow($rule->offset_months ?? 0),

            // "15th of the Nth month after the month of X"
            // Normalize to start of month first for deterministic results
            CalcMethod::MonthDayAfterMonthOfDate => $anchorDate->copy()
                ->startOfMonth()
                ->addMonthsNoOverflow($rule->offset_months ?? 0)
                ->setDay($rule->day_of_month ?? 1),

            // VA: "90 days from end of month of last work"
            // First go to end of anchor month, then add days
            CalcMethod::DaysAfterEndOfMonthOfDate => $anchorDate->copy()
                ->endOfMonth()
                ->addDays($rule->offset_days ?? 0),
        };
    }

    /**
     * Determine the effective scope (residential/commercial) for rule selection.
     * Defaults to 'residential' (conservative - shorter deadlines) if unknown.
     */
    protected function determineEffectiveScope(LienProject $project): string
    {
        // Check for explicit property_class if set
        $propertyClass = $project->property_class ?? null;

        if ($propertyClass) {
            // Map common property class values to scope
            $residentialTypes = ['residential', 'single_family', 'multi_family', 'home', 'house'];
            $commercialTypes = ['commercial', 'industrial', 'retail', 'office'];

            $normalized = strtolower($propertyClass);
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

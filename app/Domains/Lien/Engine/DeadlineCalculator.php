<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienProject;
use Carbon\Carbon;
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

        // Determine effective scope based on project's project_type field
        // Default to 'residential' (conservative - shorter deadlines) if unknown
        $effectiveScope = $this->determineEffectiveScope($project);

        $rules = LienDeadlineRule::forStateAndClaimant(
            $project->jobsite_state,
            $project->claimant_type,
            $effectiveScope
        );

        foreach ($rules as $rule) {
            $anchorDate = $this->resolveAnchor($project, $rule);
            $missingFields = [];

            if (! $anchorDate) {
                $missingFields[] = $rule->trigger_event->value;
            }

            $dueDate = $anchorDate ? $this->calculateDueDate($anchorDate, $rule) : null;

            // Determine status
            $status = DeadlineStatus::Pending;
            if (! $anchorDate) {
                $status = DeadlineStatus::NotApplicable;
            }

            // Check if there's an existing completed filing
            $existingDeadline = $project->deadlines()
                ->where('deadline_rule_id', $rule->id)
                ->first();

            $completedFilingId = $existingDeadline?->completed_filing_id;
            if ($completedFilingId) {
                $status = DeadlineStatus::Completed;
            }

            // Upsert the deadline
            $project->deadlines()->updateOrCreate(
                ['deadline_rule_id' => $rule->id],
                [
                    'business_id' => $project->business_id,
                    'document_type_id' => $rule->document_type_id,
                    'due_date' => $dueDate,
                    'computed_from_date' => $anchorDate,
                    'missing_fields_json' => $missingFields ?: null,
                    'status' => $completedFilingId ? DeadlineStatus::Completed : $status,
                    'completed_filing_id' => $completedFilingId,
                ]
            );
        }
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
     * Resolve the anchor date for the rule, handling later_of conditions.
     */
    protected function resolveAnchor(LienProject $project, LienDeadlineRule $rule): ?CarbonInterface
    {
        $conditions = $rule->conditions_json;

        // Handle later_of anchor (e.g., TX uses later of last_furnish_date and special_fab_delivery_date)
        if (($conditions['anchor'] ?? null) === 'later_of') {
            $dates = collect($conditions['dates'] ?? [])
                ->map(fn ($field) => $project->{$field})
                ->filter()
                ->values();

            if ($dates->isEmpty()) {
                return null;
            }

            // Compare Carbon instances by timestamp, return the latest
            return $dates->reduce(fn ($carry, $date) => $carry === null || $date->greaterThan($carry) ? $date : $carry
            );
        }

        // Default: use the trigger_event field
        return $project->{$rule->trigger_event->value};
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

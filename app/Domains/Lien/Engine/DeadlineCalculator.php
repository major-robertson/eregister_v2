<?php

namespace App\Domains\Lien\Engine;

use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienProject;

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

        $rules = LienDeadlineRule::forStateAndClaimant(
            $project->jobsite_state,
            $project->claimant_type
        );

        foreach ($rules as $rule) {
            $triggerField = $rule->trigger_event->value;
            $anchorDate = $project->{$triggerField};
            $missingFields = [];

            if (! $anchorDate) {
                $missingFields[] = $triggerField;
            }

            $dueDate = $anchorDate?->copy()->addDays($rule->offset_days);

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

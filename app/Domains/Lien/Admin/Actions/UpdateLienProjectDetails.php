<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Admin\Actions\Concerns\TracksFieldChanges;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;

/**
 * Admin edit of the shared LienProject behind a filing. Re-derives the canonical
 * claimant_type from the role facts, recalculates deadlines (exactly as the
 * customer wizard does), logs a field-level `application_project_updated` audit
 * event on the edited filing, and re-syncs every eligible fulfillment snapshot.
 */
class UpdateLienProjectDetails
{
    use NormalizesLienInput, TracksFieldChanges;

    /**
     * Project columns whose changes are tracked in the audit diff. claimant_type
     * is derived, but tracked so the timeline shows when it shifts.
     *
     * @var list<string>
     */
    private const TRACKED_FIELDS = [
        'name', 'job_number', 'provided_type', 'hired_by', 'claimant_type', 'property_context',
        'property_class', 'jobsite_address1', 'jobsite_address2', 'jobsite_city', 'jobsite_state',
        'jobsite_zip', 'jobsite_county', 'legal_description', 'apn',
        'first_furnish_date', 'last_furnish_date', 'completion_date', 'noc_status', 'noc_recorded_at',
        'base_contract_amount_cents', 'change_orders_cents', 'credits_deductions_cents',
        'payments_received_cents', 'uncompleted_work_cents', 'owner_is_tenant', 'has_written_contract',
    ];

    /**
     * @param  array<string, mixed>  $input  raw project form values (money already in cents)
     */
    public function execute(LienFiling $filing, array $input): SyncResult
    {
        return DB::transaction(function () use ($filing, $input): SyncResult {
            $project = $filing->project;

            $providedType = (string) ($input['provided_type'] ?? $project->provided_type);
            $hiredBy = (string) ($input['hired_by'] ?? $project->hired_by);

            $before = $this->fieldSnapshot($project, self::TRACKED_FIELDS);

            $project->update([
                'name' => $this->nullIfBlank($input['name'] ?? null),
                'job_number' => $this->nullIfBlank($input['job_number'] ?? null),
                'provided_type' => $providedType,
                'hired_by' => $hiredBy,
                // Canonical value — always re-derived so it never drifts from the facts.
                'claimant_type' => ClaimantType::derive($providedType, $hiredBy)->value,
                'property_context' => $this->nullIfBlank($input['property_context'] ?? null),
                'property_class' => $this->nullIfBlank($input['property_class'] ?? null),
                'jobsite_address1' => $this->nullIfBlank($input['jobsite_address1'] ?? null),
                'jobsite_address2' => $this->nullIfBlank($input['jobsite_address2'] ?? null),
                'jobsite_city' => $this->nullIfBlank($input['jobsite_city'] ?? null),
                'jobsite_state' => $this->normalizeState($input['jobsite_state'] ?? null),
                'jobsite_zip' => $this->nullIfBlank($input['jobsite_zip'] ?? null),
                'jobsite_county' => $this->nullIfBlank($input['jobsite_county'] ?? null),
                'legal_description' => $this->nullIfBlank($input['legal_description'] ?? null),
                'apn' => $this->nullIfBlank($input['apn'] ?? null),
                'first_furnish_date' => $this->nullableDate($input['first_furnish_date'] ?? null),
                'last_furnish_date' => $this->nullableDate($input['last_furnish_date'] ?? null),
                'completion_date' => $this->nullableDate($input['completion_date'] ?? null),
                'noc_status' => $this->nullIfBlank($input['noc_status'] ?? null),
                'noc_recorded_at' => $this->nullableDate($input['noc_recorded_at'] ?? null),
                'base_contract_amount_cents' => $this->asCents($input['base_contract_amount_cents'] ?? null),
                'change_orders_cents' => $this->asCents($input['change_orders_cents'] ?? null),
                'credits_deductions_cents' => $this->asCents($input['credits_deductions_cents'] ?? null),
                'payments_received_cents' => $this->asCents($input['payments_received_cents'] ?? null),
                'uncompleted_work_cents' => $this->asCents($input['uncompleted_work_cents'] ?? null),
                'owner_is_tenant' => (bool) ($input['owner_is_tenant'] ?? false),
                'has_written_contract' => (bool) ($input['has_written_contract'] ?? false),
            ]);

            // Dates / claimant type / state may have moved — recompute deadlines
            // (the calculator preserves completed deadlines).
            app(DeadlineCalculator::class)->calculateForProject($project->fresh());

            $changes = $this->diffSnapshots($before, $this->fieldSnapshot($project->refresh(), self::TRACKED_FIELDS));

            if ($changes === []) {
                return new SyncResult;
            }

            $sync = app(SyncFilingSnapshot::class)->syncFromCurrentApplicationState($filing);

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'application_project_updated',
                'payload_json' => ['changes' => $changes, 'meta' => $sync->toAuditMeta()],
                'created_by' => auth()->id(),
            ]);

            return $sync;
        });
    }
}

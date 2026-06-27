<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Admin\Actions\Concerns\TracksFieldChanges;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;

/**
 * Admin edit of a filing's own columns (claim amount, description of work,
 * jurisdiction, service level). Logs a field-level `application_filing_updated`
 * audit event and re-syncs the fulfillment snapshot.
 */
class UpdateLienFilingDetails
{
    use NormalizesLienInput, TracksFieldChanges;

    /**
     * @var list<string>
     */
    private const TRACKED_FIELDS = [
        'amount_claimed_cents', 'description_of_work', 'jurisdiction_state', 'jurisdiction_county', 'service_level',
    ];

    /**
     * @param  array<string, mixed>  $input  raw filing form values (amount already in cents)
     */
    public function execute(LienFiling $filing, array $input): SyncResult
    {
        return DB::transaction(function () use ($filing, $input): SyncResult {
            $before = $this->fieldSnapshot($filing, self::TRACKED_FIELDS);

            $filing->update([
                'amount_claimed_cents' => $this->asCents($input['amount_claimed_cents'] ?? null),
                'description_of_work' => $this->nullIfBlank($input['description_of_work'] ?? null),
                'jurisdiction_state' => $this->normalizeState($input['jurisdiction_state'] ?? null),
                'jurisdiction_county' => $this->nullIfBlank($input['jurisdiction_county'] ?? null),
                'service_level' => $this->nullIfBlank($input['service_level'] ?? null),
            ]);

            $changes = $this->diffSnapshots($before, $this->fieldSnapshot($filing->refresh(), self::TRACKED_FIELDS));

            if ($changes === []) {
                return new SyncResult;
            }

            $sync = app(SyncFilingSnapshot::class)->syncFromCurrentApplicationState($filing);

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'application_filing_updated',
                'payload_json' => ['changes' => $changes, 'meta' => $sync->toAuditMeta()],
                'created_by' => auth()->id(),
            ]);

            return $sync;
        });
    }
}

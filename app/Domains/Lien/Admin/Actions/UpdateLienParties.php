<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Admin\Actions\Concerns\TracksFieldChanges;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use Illuminate\Support\Facades\DB;

/**
 * Admin add / edit / remove of the parties on a lien application. Each operation
 * logs an `application_parties_updated` audit event (identifying the party by ID,
 * with role + name only as a display label) and re-syncs the fulfillment snapshot
 * — which rebuilds parties_snapshot_json and reconciles recipients centrally.
 */
class UpdateLienParties
{
    use NormalizesLienInput, TracksFieldChanges;

    /**
     * @var list<string>
     */
    private const TRACKED_FIELDS = [
        'role', 'name', 'company_name', 'address1', 'address2', 'city', 'state', 'zip', 'email', 'phone',
    ];

    /**
     * Create a new party (when $partyId is null) or update an existing one.
     *
     * @param  array<string, mixed>  $input
     */
    public function saveParty(LienFiling $filing, ?int $partyId, array $input): SyncResult
    {
        return DB::transaction(function () use ($filing, $partyId, $input): SyncResult {
            $project = $filing->project;

            $attributes = [
                'role' => $this->nullIfBlank($input['role'] ?? null),
                'name' => $this->nullIfBlank($input['name'] ?? null),
                'company_name' => $this->nullIfBlank($input['company_name'] ?? null),
                'address1' => $this->nullIfBlank($input['address1'] ?? null),
                'address2' => $this->nullIfBlank($input['address2'] ?? null),
                'city' => $this->nullIfBlank($input['city'] ?? null),
                'state' => $this->normalizeState($input['state'] ?? null),
                'zip' => $this->nullIfBlank($input['zip'] ?? null),
                'email' => $this->normalizeEmail($input['email'] ?? null),
                'phone' => $this->nullIfBlank($input['phone'] ?? null),
            ];

            if ($partyId !== null) {
                $party = $project->parties()->findOrFail($partyId);
                $before = $this->fieldSnapshot($party, self::TRACKED_FIELDS);
                $party->update($attributes);
                $changes = $this->diffSnapshots($before, $this->fieldSnapshot($party->refresh(), self::TRACKED_FIELDS));
                $action = 'updated';

                // Nothing actually changed — don't write a noise event or resync.
                if ($changes === []) {
                    return new SyncResult;
                }
            } else {
                $party = $project->parties()->create($attributes + ['business_id' => $filing->business_id]);
                $changes = $this->diffSnapshots(
                    array_fill_keys(self::TRACKED_FIELDS, null),
                    $this->fieldSnapshot($party, self::TRACKED_FIELDS),
                );
                $action = 'added';
            }

            return $this->recordAndSync($filing, $this->partyDescriptor($party, $action), $changes);
        });
    }

    public function removeParty(LienFiling $filing, int $partyId): SyncResult
    {
        return DB::transaction(function () use ($filing, $partyId): SyncResult {
            $party = $filing->project->parties()->findOrFail($partyId);

            // Capture identity before the row is gone.
            $descriptor = $this->partyDescriptor($party, 'removed');

            $party->delete();

            return $this->recordAndSync($filing, $descriptor, []);
        });
    }

    /**
     * @param  array{party_id: int, role: ?string, label: string, action: string}  $descriptor
     * @param  array<string, array{from: scalar|null, to: scalar|null}>  $changes
     */
    private function recordAndSync(LienFiling $filing, array $descriptor, array $changes): SyncResult
    {
        $sync = app(SyncFilingSnapshot::class)->syncFromCurrentApplicationState($filing);

        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'application_parties_updated',
            'payload_json' => [
                'party' => $descriptor,
                'changes' => $changes,
                'meta' => $sync->toAuditMeta(),
            ],
            'created_by' => auth()->id(),
        ]);

        return $sync;
    }

    /**
     * @return array{party_id: int, role: ?string, label: string, action: string}
     */
    private function partyDescriptor(LienParty $party, string $action): array
    {
        $role = $party->role;

        return [
            'party_id' => $party->id,
            'role' => $role?->value,
            'label' => ($role?->label() ?? 'Party').': '.$party->displayName(),
            'action' => $action,
        ];
    }
}

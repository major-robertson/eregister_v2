<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for everything the fulfillment team sees: a filing's
 * immutable `payload_json` / `parties_snapshot_json` and its recipients'
 * `address_snapshot_json`. No other Action writes those columns directly.
 *
 * Given a filing the admin just edited, this rebuilds the fulfillment snapshot
 * from the *current* project + filing + parties for that filing and every
 * snapshot-eligible sibling filing on the same project (they all derive from the
 * same shared application data). Finalized filings — already mailed/recorded —
 * are left frozen and reported as skipped.
 */
class SyncFilingSnapshot
{
    /**
     * payload_json keys that have no backing column and therefore cannot be
     * reconstructed from the live records. They are carried forward verbatim
     * from the existing snapshot. Everything else in the payload is rebuilt
     * authoritatively from the model (so column-backed values — e.g.
     * service_level, property_class — can never go stale).
     *
     * @var list<string>
     */
    private const PRESERVED_PAYLOAD_KEYS = [
        'property_details.multiple_parcels',
    ];

    public function syncFromCurrentApplicationState(LienFiling $editedFiling): SyncResult
    {
        // Fresh project + parties, bypassing the business scope (admins have no
        // current business, and we may be syncing siblings regardless).
        $project = LienProject::withoutGlobalScope('business')
            ->with('parties')
            ->findOrFail($editedFiling->project_id);

        $filings = LienFiling::withoutGlobalScope('business')
            ->with(['recipients', 'documentType'])
            ->where('project_id', $editedFiling->project_id)
            ->get();

        $result = new SyncResult;

        DB::transaction(function () use ($filings, $project, $result): void {
            foreach ($filings as $filing) {
                if (! $filing->canApplicationSnapshotBeResynced()) {
                    $result->skippedFilingIds[] = $filing->id;
                    $result->skippedReasons[$filing->id] = $filing->payload_json === null
                        ? 'no_snapshot'
                        : 'finalized';

                    continue;
                }

                $this->resyncFiling($filing, $project, $result);
                $result->syncedFilingIds[] = $filing->id;
            }
        });

        return $result;
    }

    protected function resyncFiling(LienFiling $filing, LienProject $project, SyncResult $result): void
    {
        $filing->update([
            'parties_snapshot_json' => $project->parties->map->toSnapshot()->values()->all(),
            'payload_json' => $this->rebuildPayload($filing, $project),
        ]);

        $this->reconcileRecipients($filing, $project, $result);
    }

    /**
     * Rebuild payload_json from live records, then carry forward only the keys
     * that cannot be reconstructed plus any unrecognized keys we did not author.
     *
     * @return array<string, mixed>
     */
    protected function rebuildPayload(LienFiling $filing, LienProject $project): array
    {
        $existing = $filing->payload_json ?? [];

        $payload = [
            'project' => [
                'name' => $project->name,
                'jobsite_address' => $project->jobsiteAddressLine(),
                'legal_description' => $project->legal_description,
                'apn' => $project->apn,
            ],
            'property_details' => [
                'is_public_project' => $project->property_class === 'government' ? 'yes' : 'no',
                'project_type_category' => $project->property_class,
                'has_legal_description' => $project->legal_description ? 'yes' : 'no',
                'has_apn' => $project->apn ? 'yes' : 'no',
                'multiple_parcels' => null, // no column; restored from PRESERVED_PAYLOAD_KEYS below
                'owner_is_tenant' => (bool) $project->owner_is_tenant,
            ],
            'filing' => [
                'document_type' => $filing->documentType?->name,
                'amount_claimed' => $filing->amount_claimed_cents !== null
                    ? $filing->amount_claimed_cents / 100
                    : null,
                'description_of_work' => $filing->description_of_work,
                'service_level' => $filing->service_level?->value,
            ],
            'amount_breakdown' => [
                'has_written_contract' => $project->has_written_contract === null
                    ? null
                    : ($project->has_written_contract ? '1' : '0'),
                'base_contract_amount' => $this->centsToDecimalString($project->base_contract_amount_cents),
                'change_orders' => $this->centsToDecimalString($project->change_orders_cents),
                'credits_deductions' => $this->centsToDecimalString($project->credits_deductions_cents),
                'payments_received' => $this->centsToDecimalString($project->payments_received_cents),
                'uncompleted_work' => $this->centsToDecimalString($project->uncompleted_work_cents),
            ],
            'dates' => [
                'first_furnish' => $project->first_furnish_date?->toDateString(),
                'last_furnish' => $project->last_furnish_date?->toDateString(),
                'completion' => $project->completion_date?->toDateString(),
            ],
        ];

        // Carry forward the column-less keys from the existing snapshot.
        foreach (self::PRESERVED_PAYLOAD_KEYS as $path) {
            $value = data_get($existing, $path);
            if ($value !== null) {
                data_set($payload, $path, $value);
            }
        }

        // Defensive: preserve any unrecognized top-level keys we did not author.
        foreach ($existing as $key => $value) {
            if (! array_key_exists($key, $payload)) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    /**
     * Reconcile this filing's recipients against the live parties:
     *  - unsent recipient, party still exists  -> refresh address snapshot
     *  - unsent recipient, party removed        -> delete (no dangling mail target)
     *  - already-sent recipient                 -> leave; warn if its address drifted
     */
    protected function reconcileRecipients(LienFiling $filing, LienProject $project, SyncResult $result): void
    {
        $liveParties = $project->parties->keyBy('id');

        foreach ($filing->recipients as $recipient) {
            $party = $recipient->party_id ? $liveParties->get($recipient->party_id) : null;

            if ($recipient->sent_at !== null) {
                if ($party && $this->recipientAddressDiffers($recipient, $party)) {
                    $result->staleSentRecipientWarnings[] =
                        "Recipient \"{$recipient->snapshotName()}\" was already mailed; its address may now be out of date.";
                }

                continue;
            }

            if (! $party) {
                $recipient->delete();

                continue;
            }

            $recipient->update([
                'address_snapshot_json' => LienFilingRecipient::fromParty($party)['address_snapshot_json'],
            ]);
        }
    }

    protected function recipientAddressDiffers(LienFilingRecipient $recipient, LienParty $party): bool
    {
        $current = LienFilingRecipient::fromParty($party)['address_snapshot_json'];

        return ($recipient->address_snapshot_json ?? []) !== $current;
    }

    protected function centsToDecimalString(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return number_format($cents / 100, 2, '.', '');
    }
}

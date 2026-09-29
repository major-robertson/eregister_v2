<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Admin edits to who a filing is served on and how it went out.
 *
 * Adding a recipient snapshots the party's address at that moment, so the
 * proof of service and the labels print the address actually mailed even
 * if the party is corrected later. Editing records the delivery method,
 * tracking number and sent / delivered times. Both log audit events.
 * Nothing here is automatic: an admin adds each recipient by hand, and
 * nothing touches payload_json.
 */
class UpdateLienFilingRecipient
{
    use NormalizesLienInput;

    /** @var list<string> */
    public const DELIVERY_METHODS = UpdateLienDocumentDetails::NOTICE_METHODS;

    public function add(LienFiling $filing, LienParty $party, string $deliveryMethod = 'certified_mail'): LienFilingRecipient
    {
        if ($party->project_id !== $filing->project_id) {
            throw new InvalidArgumentException("That party is not on this filing's project.");
        }

        return DB::transaction(function () use ($filing, $party, $deliveryMethod): LienFilingRecipient {
            $exists = $filing->recipients()
                ->withoutGlobalScope('business')
                ->where('party_id', $party->id)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException(($party->displayName() ?: 'That party').' is already a recipient.');
            }

            $recipient = $filing->recipients()->create(LienFilingRecipient::fromParty($party, $deliveryMethod));

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'recipient_added',
                'payload_json' => ['recipient' => $this->descriptor($recipient, $party)],
                'created_by' => auth()->id(),
            ]);

            return $recipient;
        });
    }

    /**
     * @param  array{delivery_method?: string|null, tracking_number?: string|null, sent_at?: Carbon|null, delivered_at?: Carbon|null}  $input  timestamps already in UTC
     * @return array<string, array{from: mixed, to: mixed}> the fields that changed
     */
    public function update(LienFiling $filing, LienFilingRecipient $recipient, array $input): array
    {
        if ($recipient->filing_id !== $filing->id) {
            throw new InvalidArgumentException('That recipient belongs to another filing.');
        }

        return DB::transaction(function () use ($filing, $recipient, $input): array {
            $before = $this->snapshot($recipient);

            $recipient->update([
                'delivery_method' => $this->nullIfBlank($input['delivery_method'] ?? null),
                'tracking_number' => $this->nullIfBlank($input['tracking_number'] ?? null),
                'sent_at' => $input['sent_at'] ?? null,
                'delivered_at' => $input['delivered_at'] ?? null,
            ]);

            $after = $this->snapshot($recipient->refresh());
            $changes = [];

            foreach ($after as $field => $value) {
                if (($before[$field] ?? null) !== $value) {
                    $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $value];
                }
            }

            if ($changes === []) {
                return [];
            }

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'recipient_updated',
                'payload_json' => [
                    'recipient' => $this->descriptor($recipient, $recipient->party),
                    'changes' => $changes,
                ],
                'created_by' => auth()->id(),
            ]);

            return $changes;
        });
    }

    /**
     * @return array<string, string|null>
     */
    private function snapshot(LienFilingRecipient $recipient): array
    {
        return [
            'delivery_method' => $recipient->delivery_method,
            'tracking_number' => $recipient->tracking_number,
            'sent_at' => $recipient->sent_at?->toIso8601String(),
            'delivered_at' => $recipient->delivered_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function descriptor(LienFilingRecipient $recipient, ?LienParty $party): array
    {
        return [
            'id' => $recipient->id,
            'party_id' => $recipient->party_id,
            'role' => $party?->role?->value,
            'name' => $recipient->snapshotName(),
            'address' => $recipient->snapshotAddressLine(),
        ];
    }
}

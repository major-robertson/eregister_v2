<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Admin\Actions\Concerns\NormalizesLienInput;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\DB;

/**
 * Admin edit of the per-document facts the generated lien documents need
 * but the application never asked for: signer and title, license number,
 * contract date and type, prior-notice service, months of work, owner
 * interest and block/lot, and the recorded lien a release refers to.
 *
 * Stored as lien_filings.document_details_json (only the keys that are
 * set) and logged as a field-level `document_details_updated` event.
 * Nothing in payload_json changes, so there is no snapshot re-sync.
 */
class UpdateLienDocumentDetails
{
    use NormalizesLienInput;

    /** @var list<string> */
    public const CONTRACT_TYPES = ['written', 'oral'];

    /** @var list<string> */
    public const NOTICE_METHODS = [
        'certified_mail', 'registered_mail', 'first_class_mail', 'personal_delivery', 'overnight_delivery', 'email',
    ];

    /**
     * @param  array<string, mixed>  $input  form values (money already in cents; original_lien nested)
     * @return array<string, array{from: mixed, to: mixed}> the fields that changed
     */
    public function execute(LienFiling $filing, array $input): array
    {
        return DB::transaction(function () use ($filing, $input): array {
            $before = self::flatten($filing->document_details_json ?? []);
            $details = $this->normalize($input);

            $filing->update(['document_details_json' => $details === [] ? null : $details]);

            $after = self::flatten($filing->refresh()->document_details_json ?? []);
            $changes = [];

            foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $field) {
                if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                    $changes[$field] = ['from' => $before[$field] ?? null, 'to' => $after[$field] ?? null];
                }
            }

            if ($changes === []) {
                return [];
            }

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'document_details_updated',
                'payload_json' => ['changes' => $changes],
                'created_by' => auth()->id(),
            ]);

            return $changes;
        });
    }

    /**
     * Only set values are stored, so a blank form leaves the column null and
     * the documents keep falling back to the business and project.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function normalize(array $input): array
    {
        $set = fn (array $values) => array_filter($values, fn ($value) => $value !== null);

        $details = $set([
            'signer_name' => $this->nullIfBlank($input['signer_name'] ?? null),
            'signer_title' => $this->nullIfBlank($input['signer_title'] ?? null),
            'license_number' => $this->nullIfBlank($input['license_number'] ?? null),
            'contract_date' => $this->nullableDate($input['contract_date'] ?? null),
            'contract_type' => $this->nullIfBlank($input['contract_type'] ?? null),
            'estimated_price_cents' => $this->asCents($input['estimated_price_cents'] ?? null),
            'owner_interest' => $this->nullIfBlank($input['owner_interest'] ?? null),
            'block' => $this->nullIfBlank($input['block'] ?? null),
            'lot' => $this->nullIfBlank($input['lot'] ?? null),
            'notice_served_at' => $this->nullableDate($input['notice_served_at'] ?? null),
            'notice_served_method' => $this->nullIfBlank($input['notice_served_method'] ?? null),
            'months_of_work' => $this->nullIfBlank($input['months_of_work'] ?? null),
            'attachments_note' => $this->nullIfBlank($input['attachments_note'] ?? null),
        ]);

        $original = (array) ($input['original_lien'] ?? []);
        $originalLien = $set([
            'recording_reference' => $this->nullIfBlank($original['recording_reference'] ?? null),
            'book' => $this->nullIfBlank($original['book'] ?? null),
            'page' => $this->nullIfBlank($original['page'] ?? null),
            'county' => $this->nullIfBlank($original['county'] ?? null),
            'recorded_at' => $this->nullableDate($original['recorded_at'] ?? null),
            'amount_received_cents' => $this->asCents($original['amount_received_cents'] ?? null),
        ]);

        if ($originalLien !== []) {
            $details['original_lien'] = $originalLien;
        }

        return $details;
    }

    /**
     * One level of keys for the audit diff: "original_lien.book" => "…".
     *
     * @param  array<string, mixed>  $details
     * @return array<string, scalar|null>
     */
    public static function flatten(array $details): array
    {
        $flat = [];

        foreach ($details as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    $flat["{$key}.{$subKey}"] = is_scalar($subValue) ? $subValue : null;
                }
            } else {
                $flat[$key] = is_scalar($value) ? $value : null;
            }
        }

        return $flat;
    }
}

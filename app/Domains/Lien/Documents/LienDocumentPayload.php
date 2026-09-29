<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\PartyRole;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Illuminate\Support\Carbon;

/**
 * The plain, JSON-safe array the lien document views render from.
 *
 * Built from live filing and project data: payload_json is a fulfillment
 * summary without roles, addresses, recipients or recording facts, and the
 * post-recording documents need the current recording reference. A
 * regenerated draft therefore always carries the admin's latest corrections.
 * E-sign can freeze this array later exactly as SendDemandLetterForSignature
 * freezes the demand letter payload.
 *
 * Every value is null-guarded so a thin application still renders; the views
 * print a ruled blank for anything missing and LienDocumentPackage warns.
 */
final class LienDocumentPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function fromFiling(LienFiling $filing, ResolvedLienDocument $form): array
    {
        $project = $filing->project;
        $business = $project?->business;
        $details = self::details($filing);
        $parties = self::parties($project, $business);
        $amountCents = $filing->amount_claimed_cents ?? $project?->balanceDueCents();
        $now = now();
        $stateRules = LienDocumentRegistry::for($form->state);

        return [
            'form' => $form->toArray(),
            // What the state calls each document, for cross-references such as
            // "served its Notice to Owner on …".
            'titles' => array_map(fn (array $entry) => $entry['title'], $stateRules['kinds']),
            'date' => $now->eastern()->format('F j, Y'),
            'generated_at' => $now->eastern()->format('M j, Y g:i A T'),
            'filing' => [
                'public_id' => $filing->public_id,
                'kind' => $form->kind,
                'title' => $form->title,
                'state' => $form->state,
                'state_name' => $form->stateName,
                'county' => $form->countyName,
                'amount_cents' => $amountCents,
                'amount' => self::money($amountCents),
                'amount_words' => $amountCents === null ? null : MoneyWords::dollars($amountCents),
                // Preliminary notices state an estimate of the total price: Document
                // details first, then the contract with its change orders, then the claim.
                'estimate' => $details['estimated_price']
                    ?? self::money($project?->base_contract_amount_cents === null ? null : $project->base_contract_amount_cents + ($project->change_orders_cents ?? 0))
                    ?? self::money($amountCents),
                'description_of_work' => self::text($filing->description_of_work),
                'recording' => [
                    'method' => $filing->recording_method?->label(),
                    'provider' => self::text($filing->recording_provider),
                    'reference' => self::text($filing->recording_reference),
                    'submitted_at' => self::date($filing->recording_submitted_at, true),
                    'recorded_at' => self::date($filing->recorded_at, true),
                ],
                'mailed_at' => self::date($filing->mailed_at, true),
                'tracking_number' => self::text($filing->mailing_tracking_number),
            ],
            'project' => self::project($project, $form, $details),
            'parties' => $parties,
            'signer' => self::signer($details, $business, $parties['claimant']),
            'details' => $details,
            'original_lien' => self::originalLien($filing, $form, $details),
            'preparer' => self::preparer(),
            'server' => [
                'state' => $serverState = strtoupper((string) config('lien.documents.server_state', 'KY')),
                'state_name' => WaiverStateRegistry::STATE_NAMES[$serverState] ?? $serverState,
            ],
            'recipients' => self::recipients($filing),
        ];
    }

    /**
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private static function project(?LienProject $project, ResolvedLienDocument $form, array $details): array
    {
        $type = $project?->claimant_type;
        $hiredBy = self::text($project?->hired_by);
        $inPrivity = match (true) {
            $hiredBy === 'owner' => true,
            $hiredBy !== null => false,
            $type instanceof ClaimantType => in_array($type, [ClaimantType::Gc, ClaimantType::SupplierToOwner], true),
            default => null,
        };

        $cityStateZip = trim(implode(' ', array_filter([
            implode(', ', array_filter([$project?->jobsite_city, $project?->jobsite_state])),
            $project?->jobsite_zip,
        ])));
        // "9025 Baywood Park Dr, Seminole, FL 33777": street, unit, then city-state-zip as one part.
        $addressLines = array_values(array_filter([$project?->jobsite_address1, $project?->jobsite_address2, $cityStateZip]));

        $writtenContract = $project?->has_written_contract;

        return [
            'name' => self::text($project?->name),
            'job_number' => self::text($project?->job_number),
            'address' => [
                'line1' => self::text($project?->jobsite_address1),
                'line2' => self::text($project?->jobsite_address2),
                'city' => self::text($project?->jobsite_city),
                'state' => self::text($project?->jobsite_state),
                'zip' => self::text($project?->jobsite_zip),
                'lines' => $addressLines,
                'single_line' => self::text(implode(', ', $addressLines)),
            ],
            'county' => $form->countyName,
            'legal_description' => self::text($project?->legal_description),
            'apn' => self::text($project?->apn),
            'parcel_label' => (string) ($form->recording['parcel_label'] ?? 'Parcel ID'),
            'property_class' => self::text($project?->property_class),
            'claimant_type' => $type?->value,
            'claimant_type_label' => $type?->label(),
            // How the claimant describes its tier in a sentence ("furnished … as a subcontractor").
            'claimant_type_phrase' => match ($type) {
                ClaimantType::Gc => 'direct contractor in contract with the owner',
                ClaimantType::Subcontractor => 'subcontractor',
                ClaimantType::SubSubContractor => 'sub-subcontractor',
                ClaimantType::SupplierToOwner => 'supplier of materials to the owner',
                ClaimantType::SupplierToContractor => 'supplier of materials to the direct contractor',
                ClaimantType::SupplierToSubcontractor => 'supplier of materials to a subcontractor',
                default => null,
            },
            'hired_by' => $hiredBy,
            'in_privity' => $inPrivity,
            'has_written_contract' => $writtenContract === null ? null : (bool) $writtenContract,
            'contract_type' => $details['contract_type']
                ?? ($writtenContract === null ? null : ($writtenContract ? 'written' : 'oral')),
            'dates' => [
                'first_furnish' => self::date($project?->first_furnish_date),
                'last_furnish' => self::date($project?->last_furnish_date),
                'completion' => self::date($project?->completion_date),
                'noc_recorded' => self::date($project?->noc_recorded_at),
                'noc_filed' => self::date($project?->noc_filed_date, true),
                'prelim_sent' => self::date($project?->prelim_notice_sent_at, true),
            ],
            'amounts' => [
                'contract' => self::amount($project?->base_contract_amount_cents),
                'change_orders' => self::amount($project?->change_orders_cents),
                'credits' => self::amount($project?->credits_deductions_cents),
                'payments' => self::amount($project?->payments_received_cents),
                'uncompleted' => self::amount($project?->uncompleted_work_cents),
                'balance_due' => self::amount($project?->balanceDueCents()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function parties(?LienProject $project, ?Business $business): array
    {
        $parties = $project?->parties ?? collect();
        $byRole = fn (PartyRole $role) => $parties->first(fn (LienParty $party) => $party->role === $role);

        $claimant = self::party($byRole(PartyRole::Claimant), $business);
        $owner = self::party($byRole(PartyRole::Owner));
        $customer = self::party($byRole(PartyRole::Customer));
        $gc = self::party($byRole(PartyRole::Gc));
        $subcontractor = self::party($byRole(PartyRole::Subcontractor));

        // The hiring party is whoever the claimant contracted with: the
        // customer party when one exists, else the party the wizard answer
        // points at.
        $hiring = $customer ?? match ($project?->hired_by) {
            'owner' => $owner,
            'direct_contractor' => $gc,
            'subcontractor' => $subcontractor,
            default => null,
        };

        return [
            'claimant' => $claimant,
            'owner' => $owner,
            'customer' => $customer,
            'hiring' => $hiring,
            'gc' => $gc,
            'subcontractor' => $subcontractor,
            'lender' => self::party($byRole(PartyRole::Lender)),
            'others' => $parties
                ->filter(fn (LienParty $party) => $party->role === PartyRole::Other)
                ->map(fn (LienParty $party) => self::party($party))
                ->values()
                ->all(),
        ];
    }

    /**
     * One party as the views see it. The claimant falls back to the business
     * for its company name and mailing address.
     *
     * @return array<string, mixed>|null
     */
    private static function party(?LienParty $party, ?Business $business = null): ?array
    {
        if ($party === null && $business === null) {
            return null;
        }

        $company = self::text($party?->company_name) ?? self::text($business?->name);
        $name = self::text($party?->name);
        $addressLines = $party?->addressLines() ?: [];

        if ($addressLines === [] && $business !== null) {
            $addressLines = $business->addressLines();
        }

        $state = self::text($party?->state) ?? self::text($business?->business_address['state'] ?? null);

        return [
            'id' => $party?->id,
            'role' => $party?->role?->value ?? PartyRole::Claimant->value,
            'role_label' => $party?->role?->label() ?? PartyRole::Claimant->label(),
            'name' => $name,
            'company' => $company,
            'display_name' => $company ?? $name,
            'address_lines' => $addressLines,
            'address_line' => implode(', ', $addressLines) ?: null,
            'city' => self::text($party?->city),
            'state' => $state,
            'zip' => self::text($party?->zip),
            'county' => self::text($party?->county),
            'phone' => self::text($party?->phone) ?? self::text($business?->phone),
            'email' => self::text($party?->email),
        ];
    }

    /**
     * Who signs: Document details first, else the business's responsible
     * person who can sign liens.
     *
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>|null  $claimant
     * @return array<string, mixed>
     */
    private static function signer(array $details, ?Business $business, ?array $claimant): array
    {
        $name = $details['signer_name'];
        $title = $details['signer_title'];

        if ($name === null) {
            foreach ($business?->responsible_people ?? [] as $person) {
                if (! empty($person['can_sign_liens']) && ! empty($person['name'])) {
                    $name = $person['name'];
                    $title ??= $person['title'] ?? null;
                    break;
                }
            }
        }

        return [
            'name' => self::text($name),
            'title' => self::text($title),
            'company' => $claimant['display_name'] ?? null,
            'license_number' => $details['license_number'] ?? self::text($business?->contractor_license_number),
        ];
    }

    /**
     * The per-document facts that live in lien_filings.document_details_json
     * (written by the Document details card). Missing keys read as null so
     * the views can be written once.
     *
     * @return array<string, mixed>
     */
    private static function details(LienFiling $filing): array
    {
        $raw = $filing->getAttribute('document_details_json');
        $raw = is_array($raw) ? $raw : [];
        $original = is_array($raw['original_lien'] ?? null) ? $raw['original_lien'] : [];

        $noticeMethod = self::text($raw['notice_served_method'] ?? null);
        $estimate = isset($raw['estimated_price_cents']) ? (int) $raw['estimated_price_cents'] : null;
        $received = isset($original['amount_received_cents']) ? (int) $original['amount_received_cents'] : null;

        return [
            'signer_name' => self::text($raw['signer_name'] ?? null),
            'signer_title' => self::text($raw['signer_title'] ?? null),
            'license_number' => self::text($raw['license_number'] ?? null),
            'contract_date' => self::date($raw['contract_date'] ?? null),
            'contract_type' => self::text($raw['contract_type'] ?? null),
            'estimated_price_cents' => $estimate,
            'estimated_price' => self::money($estimate),
            'owner_interest' => self::text($raw['owner_interest'] ?? null),
            'block' => self::text($raw['block'] ?? null),
            'lot' => self::text($raw['lot'] ?? null),
            'notice_served_at' => self::date($raw['notice_served_at'] ?? null),
            'notice_served_method' => $noticeMethod,
            'notice_served_method_label' => self::deliveryLabel($noticeMethod),
            'months_of_work' => self::text($raw['months_of_work'] ?? null),
            'attachments_note' => self::text($raw['attachments_note'] ?? null),
            'original_lien' => [
                'recording_reference' => self::text($original['recording_reference'] ?? null),
                'book' => self::text($original['book'] ?? null),
                'page' => self::text($original['page'] ?? null),
                'county' => self::text($original['county'] ?? null),
                'recorded_at' => self::date($original['recorded_at'] ?? null),
                'amount_received_cents' => $received,
                'amount_received' => self::money($received),
            ],
        ];
    }

    /**
     * The lien a release refers to: Document details first, else the
     * project's most recently recorded mechanics lien filing.
     *
     * @param  array<string, mixed>  $details
     * @return array<string, mixed>
     */
    private static function originalLien(LienFiling $filing, ResolvedLienDocument $form, array $details): array
    {
        $title = LienDocumentRegistry::for($form->state)['kinds']['mechanics_lien']['title'];
        $fromDetails = $details['original_lien'];

        if ($fromDetails['recording_reference'] !== null || $fromDetails['recorded_at'] !== null) {
            return $fromDetails + ['title' => $title, 'source' => 'details', 'amount' => null];
        }

        $recorded = $filing->project?->latestRecordedLien($filing);

        return [
            'title' => $title,
            'source' => $recorded === null ? null : 'filing',
            'recording_reference' => self::text($recorded?->recording_reference),
            'book' => null,
            'page' => null,
            'county' => $recorded === null ? null : LienCountyKey::displayName($recorded->jurisdiction_county),
            'recorded_at' => self::date($recorded?->recorded_at, true),
            'amount' => self::money($recorded?->amount_claimed_cents),
            'amount_received_cents' => null,
            'amount_received' => null,
        ];
    }

    /**
     * The recipients on the filing, as mailed (the snapshot address, not the
     * live party).
     *
     * @return list<array<string, mixed>>
     */
    private static function recipients(LienFiling $filing): array
    {
        $recipients = $filing->relationLoaded('recipients')
            ? $filing->recipients
            : $filing->recipients()->withoutGlobalScope('business')->with('party')->get();

        return $recipients->map(function (LienFilingRecipient $recipient) {
            $snapshot = $recipient->address_snapshot_json ?? [];
            $cityStateZip = trim(implode(' ', array_filter([
                implode(', ', array_filter([$snapshot['city'] ?? null, $snapshot['state'] ?? null])),
                $snapshot['zip'] ?? null,
            ])));
            $lines = array_values(array_filter([$snapshot['address1'] ?? null, $snapshot['address2'] ?? null, $cityStateZip]));
            $company = self::text($snapshot['company_name'] ?? null);
            $name = self::text($snapshot['name'] ?? null);

            return [
                'id' => $recipient->id,
                'party_id' => $recipient->party_id,
                'role' => $recipient->party?->role?->value,
                'role_label' => $recipient->party?->role?->label(),
                'name' => $name,
                'company' => $company,
                'display_name' => $company ?? $name,
                'address_lines' => $lines,
                'address_line' => implode(', ', $lines) ?: null,
                'state' => self::text($snapshot['state'] ?? null),
                'delivery_method' => self::text($recipient->delivery_method),
                'delivery_method_label' => self::deliveryLabel($recipient->delivery_method),
                'tracking_number' => self::text($recipient->tracking_number),
                'sent_at' => self::date($recipient->sent_at, true),
                'delivered_at' => self::date($recipient->delivered_at, true),
            ];
        })->values()->all();
    }

    /**
     * The "prepared by and return to" block. config('lien.documents.preparer')
     * wins; an empty address falls back to the CAN-SPAM postal address so a
     * fresh environment still prints something.
     *
     * @return array<string, mixed>
     */
    private static function preparer(): array
    {
        $config = (array) config('lien.documents.preparer', []);
        $lines = array_values(array_filter(array_map('trim', (array) ($config['address_lines'] ?? []))));

        if ($lines === [] && filled(config('mail.postal_address'))) {
            $postal = trim((string) config('mail.postal_address'));
            $lines = array_values(array_filter(array_map('trim', explode(',', $postal, 2))));
        }

        return [
            'name' => self::text($config['name'] ?? null) ?? 'eRegister',
            'attention' => self::text($config['attention'] ?? null),
            'address_lines' => $lines,
            'phone' => self::text($config['phone'] ?? null),
            'email' => self::text($config['email'] ?? null),
        ];
    }

    public static function deliveryLabel(?string $method): ?string
    {
        return match ($method) {
            null, '' => null,
            'certified_mail' => 'certified mail, return receipt requested',
            'certified_mail_no_receipt' => 'certified mail',
            'registered_mail' => 'registered mail',
            'first_class_mail' => 'first-class mail',
            'personal_delivery', 'hand_delivery' => 'personal delivery',
            'overnight', 'overnight_delivery' => 'overnight delivery',
            'email' => 'email',
            default => str_replace('_', ' ', $method),
        };
    }

    /**
     * @return array{cents: int|null, formatted: string|null}
     */
    private static function amount(?int $cents): array
    {
        return ['cents' => $cents, 'formatted' => self::money($cents)];
    }

    /** Number only ("4,213.75"); the "$" is fixed text in the views. */
    private static function money(?int $cents): ?string
    {
        return $cents === null ? null : number_format($cents / 100, 2);
    }

    /**
     * Date-only columns format directly; timestamps shift to Eastern first.
     */
    private static function date(mixed $value, bool $timestamp = false): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = $value instanceof Carbon ? $value : Carbon::parse((string) $value);

        return ($timestamp ? $date->eastern() : $date)->format('F j, Y');
    }

    private static function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}

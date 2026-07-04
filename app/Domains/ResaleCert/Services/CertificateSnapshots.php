<?php

namespace App\Domains\ResaleCert\Services;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Models\User;

/**
 * Builds the frozen business/vendor snapshots stored on every certificate.
 * PDF rendering reads exclusively from these, so a certificate re-render
 * always matches what was originally issued.
 */
class CertificateSnapshots
{
    /**
     * Resolve the tax id to print for a state: the state's own registration
     * when the business has one, otherwise the home-state registration.
     *
     * @return array{tax_id: string|null, source_state: string|null}
     */
    public function taxIdForState(Business $business, string $stateCode): array
    {
        $registrations = $business->resaleTaxRegistrations;

        $match = $registrations->firstWhere('state_code', $stateCode);

        if ($match && $match->tax_id) {
            return ['tax_id' => $match->tax_id, 'source_state' => $stateCode];
        }

        $home = $registrations->firstWhere('is_home_state', true);

        if ($home && $home->tax_id) {
            return ['tax_id' => $home->tax_id, 'source_state' => $home->state_code];
        }

        return ['tax_id' => null, 'source_state' => null];
    }

    /**
     * Business snapshot for one certificate. Individual state forms carry
     * that state's tax id (with home-state fallback); uniform MTC/SST forms
     * carry a per-covered-state tax id map instead.
     *
     * @param  list<string>  $coversStates
     * @return array<string, mixed>
     */
    public function businessSnapshot(Business $business, User $creator, string $stateCode, array $coversStates): array
    {
        $profile = $business->resaleProfile;
        $address = $business->business_address ?? [];
        $signer = $business->getResponsiblePersonForUser($creator->id);

        $snapshot = [
            'legal_name' => $business->legal_name ?? $business->name,
            'dba' => $business->dba_name,
            'ein' => $business->fein,
            'products_description' => $profile?->products_description,
            'email' => $profile?->contact_email,
            'phone' => $profile?->formattedPhone(),
            'signer_title' => $signer['title'] ?? 'Authorized Representative',
            'address' => [
                'line1' => $address['line1'] ?? '',
                'line2' => $address['line2'] ?? null,
                'city' => $address['city'] ?? '',
                'state' => $address['state'] ?? '',
                'postal_code' => $address['zip'] ?? ($address['postal_code'] ?? ''),
                'country' => 'US',
            ],
        ];

        if (in_array($stateCode, ['MTC', 'SST'], true)) {
            $taxIds = [];

            foreach ($coversStates as $coveredState) {
                $taxIds[$coveredState] = $this->taxIdForState($business, $coveredState);
            }

            $snapshot['selected_states_tax_ids'] = $taxIds;
            $snapshot['tax_id'] = null;
            $snapshot['tax_id_source_state'] = null;
        } else {
            $taxIdInfo = $this->taxIdForState($business, $stateCode);
            $snapshot['tax_id'] = $taxIdInfo['tax_id'];
            $snapshot['tax_id_source_state'] = $taxIdInfo['source_state'];
        }

        return $snapshot;
    }

    /**
     * @return array<string, mixed>
     */
    public function vendorSnapshot(ResaleVendor $vendor): array
    {
        return [
            'legal_name' => $vendor->legal_name,
            'address' => [
                'line1' => $vendor->address_line1,
                'line2' => $vendor->address_line2,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'postal_code' => $vendor->postal_code,
                'country' => $vendor->country,
            ],
            'contact' => [
                'name' => $vendor->contact_name,
                'email' => $vendor->contact_email,
                'phone' => $vendor->contact_phone,
            ],
        ];
    }
}

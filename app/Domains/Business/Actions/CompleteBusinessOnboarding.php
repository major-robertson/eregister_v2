<?php

namespace App\Domains\Business\Actions;

use App\Domains\Business\Models\Business;
use App\Services\GooglePlacesService;

/**
 * Save the business address and mark onboarding complete. The address is
 * geocoded when it wasn't picked from the autocomplete, so the geo fields
 * are filled either way. Shared by the address screen (OnboardingWizard)
 * and the waiver sign-up's one-screen business setup (BusinessSwitcher).
 */
class CompleteBusinessOnboarding
{
    public function __construct(private GooglePlacesService $googlePlaces) {}

    /**
     * @param  array<string, mixed>  $address  line1, line2, city, state, zip and the
     *                                         optional geo fields (place_id, formatted_address,
     *                                         lat, lng, county, country)
     */
    public function execute(Business $business, array $address): void
    {
        // No place_id means the address was typed, not picked: geocode it.
        if (empty($address['place_id'])) {
            $address = [...$address, ...$this->geocode($address)];
        }

        // Filter out empty values to keep JSON clean
        $addressData = array_filter($address, function ($value, $key) {
            // Always remove empty line2
            if ($key === 'line2' && $value === '') {
                return false;
            }
            // Remove null geo fields
            if (in_array($key, ['place_id', 'formatted_address', 'lat', 'lng', 'county', 'country']) && $value === null) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);

        $business->update([
            'business_address' => $addressData,
        ]);

        $business->completeOnboarding();
    }

    /**
     * @param  array<string, mixed>  $address
     * @return array<string, mixed>
     */
    private function geocode(array $address): array
    {
        $query = implode(', ', array_filter([
            $address['line1'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['zip'] ?? null,
        ]));

        if ($query === '') {
            return [];
        }

        $geo = $this->googlePlaces->geocodeAddress($query);

        if (! $geo) {
            return [];
        }

        return [
            'place_id' => $geo['place_id'],
            'formatted_address' => $geo['formatted_address'],
            'lat' => $geo['lat'],
            'lng' => $geo['lng'],
            'county' => $geo['county'],
            'country' => $geo['country'],
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google.maps_api_key', '');
    }

    /**
     * Get place details by place ID.
     *
     * @return array{place_id: string, formatted_address: string, lat: float, lng: float, county: string|null, country: string}|null
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('GooglePlacesService: API key not configured');

            return null;
        }

        $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'place_id,formatted_address,geometry,address_components',
            'key' => $this->apiKey,
        ]);

        if (! $response->successful()) {
            Log::error('GooglePlacesService: Place Details API call failed', [
                'place_id' => $placeId,
                'status' => $response->status(),
            ]);

            return null;
        }

        $data = $response->json();

        if ($data['status'] !== 'OK') {
            Log::warning('GooglePlacesService: Place Details API returned non-OK status', [
                'place_id' => $placeId,
                'status' => $data['status'],
                'error' => $data['error_message'] ?? null,
            ]);

            return null;
        }

        return $this->extractPlaceData($data['result']);
    }

    /**
     * Geocode an address string to get place details.
     * Used as fallback when user manually enters address without using autocomplete.
     *
     * @return array{place_id: string|null, formatted_address: string, lat: float, lng: float, county: string|null, country: string}|null
     */
    public function geocodeAddress(string $address): ?array
    {
        if (empty($this->apiKey)) {
            Log::warning('GooglePlacesService: API key not configured');

            return null;
        }

        // First, use Place Autocomplete to find the best match
        $autocompleteResponse = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $address,
            'types' => 'address',
            'components' => 'country:us',
            'key' => $this->apiKey,
        ]);

        if (! $autocompleteResponse->successful()) {
            Log::error('GooglePlacesService: Autocomplete API call failed', [
                'address' => $address,
                'status' => $autocompleteResponse->status(),
            ]);

            return null;
        }

        $autocompleteData = $autocompleteResponse->json();

        if ($autocompleteData['status'] !== 'OK' || empty($autocompleteData['predictions'])) {
            Log::warning('GooglePlacesService: No autocomplete predictions found', [
                'address' => $address,
                'status' => $autocompleteData['status'],
            ]);

            return null;
        }

        // Get the first (best) match and fetch its details
        $placeId = $autocompleteData['predictions'][0]['place_id'];

        return $this->getPlaceDetails($placeId);
    }

    /**
     * Extract relevant fields from a place result.
     *
     * @return array{place_id: string, formatted_address: string, lat: float, lng: float, county: string|null, country: string}
     */
    protected function extractPlaceData(array $place): array
    {
        $data = [
            'place_id' => $place['place_id'] ?? null,
            'formatted_address' => $place['formatted_address'] ?? null,
            'lat' => $place['geometry']['location']['lat'] ?? null,
            'lng' => $place['geometry']['location']['lng'] ?? null,
            'county' => null,
            'country' => null,
        ];

        foreach ($place['address_components'] ?? [] as $component) {
            $types = $component['types'];

            if (in_array('administrative_area_level_2', $types)) {
                $data['county'] = $component['long_name'];
            }

            if (in_array('country', $types)) {
                $data['country'] = $component['short_name'];
            }
        }

        return $data;
    }
}

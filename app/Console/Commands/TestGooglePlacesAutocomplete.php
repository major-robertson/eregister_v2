<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestGooglePlacesAutocomplete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:test-places {address? : The address to search for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Google Places API to verify available fields for autocomplete';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apiKey = config('services.google.maps_api_key');

        if (! $apiKey) {
            $this->error('GOOGLE_API_KEY is not set in your .env file');

            return self::FAILURE;
        }

        $address = $this->argument('address') ?? '1600 Amphitheatre Parkway, Mountain View, CA';

        $this->info("Testing Google Places API with address: {$address}");
        $this->newLine();

        // Step 1: Call Place Autocomplete API
        $this->info('Step 1: Calling Place Autocomplete API...');
        $autocompleteResponse = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $address,
            'types' => 'address',
            'components' => 'country:us',
            'key' => $apiKey,
        ]);

        if (! $autocompleteResponse->successful()) {
            $this->error('Autocomplete API call failed');
            $this->error($autocompleteResponse->body());

            return self::FAILURE;
        }

        $autocompleteData = $autocompleteResponse->json();

        if ($autocompleteData['status'] !== 'OK') {
            $this->error("Autocomplete API returned status: {$autocompleteData['status']}");
            if (isset($autocompleteData['error_message'])) {
                $this->error($autocompleteData['error_message']);
            }

            return self::FAILURE;
        }

        $this->info('Autocomplete predictions found: '.count($autocompleteData['predictions']));
        $this->newLine();

        // Show first prediction
        $prediction = $autocompleteData['predictions'][0] ?? null;
        if (! $prediction) {
            $this->error('No predictions found');

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['description', $prediction['description'] ?? 'N/A'],
                ['place_id', $prediction['place_id'] ?? 'N/A'],
            ]
        );

        $this->newLine();
        $this->info('Autocomplete response fields available:');
        $this->line(json_encode(array_keys($prediction), JSON_PRETTY_PRINT));

        // Step 2: Call Place Details API to get full details
        $placeId = $prediction['place_id'];
        $this->newLine();
        $this->info("Step 2: Calling Place Details API for place_id: {$placeId}");

        $detailsResponse = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'place_id,formatted_address,geometry,address_components',
            'key' => $apiKey,
        ]);

        if (! $detailsResponse->successful()) {
            $this->error('Place Details API call failed');
            $this->error($detailsResponse->body());

            return self::FAILURE;
        }

        $detailsData = $detailsResponse->json();

        if ($detailsData['status'] !== 'OK') {
            $this->error("Place Details API returned status: {$detailsData['status']}");
            if (isset($detailsData['error_message'])) {
                $this->error($detailsData['error_message']);
            }

            return self::FAILURE;
        }

        $place = $detailsData['result'];

        $this->newLine();
        $this->info('Place Details Response:');
        $this->newLine();

        // Show basic info
        $this->table(
            ['Field', 'Value'],
            [
                ['place_id', $place['place_id'] ?? 'N/A'],
                ['formatted_address', $place['formatted_address'] ?? 'N/A'],
                ['latitude', $place['geometry']['location']['lat'] ?? 'N/A'],
                ['longitude', $place['geometry']['location']['lng'] ?? 'N/A'],
            ]
        );

        // Parse and display address components
        $this->newLine();
        $this->info('Address Components:');

        $components = [];
        foreach ($place['address_components'] ?? [] as $component) {
            $components[] = [
                implode(', ', $component['types']),
                $component['long_name'],
                $component['short_name'],
            ];
        }

        $this->table(['Types', 'Long Name', 'Short Name'], $components);

        // Extract specific fields we care about
        $this->newLine();
        $this->info('Extracted Fields for Our Use:');

        $extracted = [
            'place_id' => $place['place_id'] ?? null,
            'formatted_address' => $place['formatted_address'] ?? null,
            'lat' => $place['geometry']['location']['lat'] ?? null,
            'lng' => $place['geometry']['location']['lng'] ?? null,
            'street_number' => null,
            'route' => null,
            'city' => null,
            'county' => null,
            'state' => null,
            'state_full' => null,
            'zip' => null,
            'country' => null,
        ];

        foreach ($place['address_components'] ?? [] as $component) {
            $types = $component['types'];

            if (in_array('street_number', $types)) {
                $extracted['street_number'] = $component['long_name'];
            }
            if (in_array('route', $types)) {
                $extracted['route'] = $component['long_name'];
            }
            if (in_array('locality', $types)) {
                $extracted['city'] = $component['long_name'];
            }
            if (in_array('administrative_area_level_2', $types)) {
                $extracted['county'] = $component['long_name'];
            }
            if (in_array('administrative_area_level_1', $types)) {
                $extracted['state'] = $component['short_name'];
                $extracted['state_full'] = $component['long_name'];
            }
            if (in_array('postal_code', $types)) {
                $extracted['zip'] = $component['long_name'];
            }
            if (in_array('country', $types)) {
                $extracted['country'] = $component['short_name'];
            }
        }

        $extractedTable = array_map(fn ($k, $v) => [$k, $v ?? '(not available)'], array_keys($extracted), array_values($extracted));
        $this->table(['Field', 'Value'], $extractedTable);

        $this->newLine();
        $this->info('Conclusion:');
        $this->line('- Autocomplete API returns: place_id and description only');
        $this->line('- Place Details API returns: geometry (lat/lng), address_components, formatted_address');
        $this->line('- We NEED to call Place Details API after autocomplete to get lat/lng and county');

        return self::SUCCESS;
    }
}

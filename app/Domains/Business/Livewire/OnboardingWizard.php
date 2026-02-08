<?php

namespace App\Domains\Business\Livewire;

use App\Concerns\ResolvesMarketingLead;
use App\Domains\Business\Models\Business;
use App\Services\GooglePlacesService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class OnboardingWizard extends Component
{
    use ResolvesMarketingLead;

    public Business $business;

    // Address (JSON structure for Google Maps compatibility)
    public array $businessAddress = [
        'line1' => '',
        'line2' => '',
        'city' => '',
        'state' => '',
        'zip' => '',
        // Geo fields from Google Places
        'place_id' => null,
        'formatted_address' => null,
        'lat' => null,
        'lng' => null,
        'county' => null,
        'country' => null,
    ];

    public function mount(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('update', $business);

        $this->business = $business;

        // Ensure legal_name is set from the business name
        if (! $business->legal_name && $business->name) {
            $business->update(['legal_name' => $business->name]);
        }

        // Load existing address from JSON or initialize empty
        $existingAddress = $business->business_address ?? [];
        $this->businessAddress = [
            'line1' => $existingAddress['line1'] ?? '',
            'line2' => $existingAddress['line2'] ?? '',
            'city' => $existingAddress['city'] ?? '',
            'state' => $existingAddress['state'] ?? '',
            'zip' => $existingAddress['zip'] ?? '',
            // Geo fields
            'place_id' => $existingAddress['place_id'] ?? null,
            'formatted_address' => $existingAddress['formatted_address'] ?? null,
            'lat' => $existingAddress['lat'] ?? null,
            'lng' => $existingAddress['lng'] ?? null,
            'county' => $existingAddress['county'] ?? null,
            'country' => $existingAddress['country'] ?? null,
        ];

        // Pre-fill from marketing lead if address is empty
        $lead = $this->resolveLeadForPrefill();
        if ($lead && empty($this->businessAddress['line1'])) {
            $this->businessAddress['line1'] = $lead->mailing_address ?? '';
            $this->businessAddress['line2'] = $lead->mailing_address_2 ?? '';
            $this->businessAddress['city'] = $lead->mailing_city ?? '';
            $this->businessAddress['state'] = $lead->mailing_state ?? '';
            $this->businessAddress['zip'] = $lead->mailing_zip ?? '';
        }
    }

    public function updateAddressFromAutocomplete(array $addressComponents): void
    {
        $this->businessAddress = [
            'line1' => $addressComponents['line1'] ?? '',
            'line2' => $addressComponents['line2'] ?? '',
            'city' => $addressComponents['city'] ?? '',
            'state' => $addressComponents['state'] ?? '',
            'zip' => $addressComponents['zip'] ?? '',
            // Geo fields from Google Places
            'place_id' => $addressComponents['place_id'] ?? null,
            'formatted_address' => $addressComponents['formatted_address'] ?? null,
            'lat' => $addressComponents['lat'] ?? null,
            'lng' => $addressComponents['lng'] ?? null,
            'county' => $addressComponents['county'] ?? null,
            'country' => $addressComponents['country'] ?? null,
        ];
    }

    public function complete(): mixed
    {
        $this->validate([
            'businessAddress.line1' => ['required', 'string', 'max:100'],
            'businessAddress.line2' => ['nullable', 'string', 'max:100'],
            'businessAddress.city' => ['required', 'string', 'max:50'],
            'businessAddress.state' => ['required', 'string', 'size:2'],
            'businessAddress.zip' => ['required', 'string', 'max:10'],
        ], [], [
            'businessAddress.line1' => 'street address',
            'businessAddress.city' => 'city',
            'businessAddress.state' => 'state',
            'businessAddress.zip' => 'ZIP code',
        ]);

        // If no place_id (user didn't use autocomplete), try to geocode the address
        if (empty($this->businessAddress['place_id'])) {
            $this->geocodeAddress();
        }

        // Filter out empty values to keep JSON clean
        $addressData = array_filter($this->businessAddress, function ($value, $key) {
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

        $this->business->update([
            'business_address' => $addressData,
        ]);

        $this->business->completeOnboarding();

        // Redirect based on signup landing path for first business
        $user = Auth::user();
        $isFirstBusiness = $user->businesses()->count() === 1;
        $landingPath = $user->signup_landing_path;

        if ($isFirstBusiness && $landingPath === '/liens') {
            $redirectRoute = route('lien.onboarding');
        } elseif ($isFirstBusiness && $landingPath === '/llc') {
            // TODO: Add LLC onboarding route when available
            // For now, redirect to dashboard where they can start LLC application
            $redirectRoute = route('dashboard');
        } else {
            $redirectRoute = route('dashboard');
        }

        return $this->redirect($redirectRoute, navigate: true);
    }

    /**
     * Geocode the address using Google Places API.
     * Used as fallback when user manually enters address without autocomplete.
     */
    protected function geocodeAddress(): void
    {
        $address = implode(', ', array_filter([
            $this->businessAddress['line1'],
            $this->businessAddress['city'],
            $this->businessAddress['state'],
            $this->businessAddress['zip'],
        ]));

        if (empty($address)) {
            return;
        }

        $googlePlaces = app(GooglePlacesService::class);
        $geoData = $googlePlaces->geocodeAddress($address);

        if ($geoData) {
            $this->businessAddress['place_id'] = $geoData['place_id'];
            $this->businessAddress['formatted_address'] = $geoData['formatted_address'];
            $this->businessAddress['lat'] = $geoData['lat'];
            $this->businessAddress['lng'] = $geoData['lng'];
            $this->businessAddress['county'] = $geoData['county'];
            $this->businessAddress['country'] = $geoData['country'];
        }
    }

    public function render(): View
    {
        return view('livewire.business.onboarding-wizard', [
            'states' => config('states'),
        ])->layout('layouts.minimal', ['title' => 'Business Setup']);
    }
}

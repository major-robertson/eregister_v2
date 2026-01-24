<div class="w-full max-w-lg">
    {{-- Progress dots: 2/4 if from liens + first business (continuous flow), 2/2 otherwise --}}
    @php
        $user = auth()->user();
        $isFromLiens = $user->signup_landing_path === '/liens';
        $isFirstBusiness = $user->businesses()->count() === 1;
        $isContinuousFlow = $isFromLiens && $isFirstBusiness;
    @endphp
    <div class="mb-16 flex justify-center gap-2">
        <div class="h-2 w-2 rounded-full bg-primary"></div>
        <div class="h-2 w-2 rounded-full bg-primary"></div>
        @if ($isContinuousFlow)
            <div class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
            <div class="h-2 w-2 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
        @endif
    </div>

    {{-- Business Address (typeform style) --}}
    <div class="text-center">
        <h1 class="text-3xl font-bold tracking-tight text-text-primary sm:text-4xl">
            Where is your business located?
        </h1>
        <p class="mt-4 text-lg text-text-secondary">
            Your primary business address.
        </p>
    </div>

    <form wire:submit="complete" class="mt-12 space-y-6">
        {{-- Street Address with Google Places Autocomplete --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-text-secondary">Street Address</label>
            <input
                id="autocomplete-address"
                type="text"
                wire:model="businessAddress.line1"
                placeholder="123 Main Street"
                autofocus
                autocomplete="off"
                class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
            />
            @error('businessAddress.line1')
                <p class="mt-2 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        {{-- Address Line 2 --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-text-secondary">Suite / Unit <span class="text-text-tertiary">(optional)</span></label>
            <input
                type="text"
                wire:model="businessAddress.line2"
                placeholder="Suite 100"
                class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
            />
        </div>

        {{-- City, State, ZIP in a row --}}
        <div class="grid grid-cols-6 gap-6">
            <div class="col-span-3">
                <label class="mb-2 block text-sm font-medium text-text-secondary">City</label>
                <input
                    type="text"
                    wire:model="businessAddress.city"
                    placeholder="City"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('businessAddress.city')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-1">
                <label class="mb-2 block text-sm font-medium text-text-secondary">State</label>
                <select
                    wire:model="businessAddress.state"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary focus:border-primary focus:outline-none focus:ring-0"
                >
                    <option value="">--</option>
                    @foreach ($states as $code => $name)
                        <option value="{{ $code }}">{{ $code }}</option>
                    @endforeach
                </select>
                @error('businessAddress.state')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label class="mb-2 block text-sm font-medium text-text-secondary">ZIP Code</label>
                <input
                    type="text"
                    wire:model="businessAddress.zip"
                    placeholder="12345"
                    class="w-full border-0 border-b-2 border-border bg-transparent px-0 py-3 text-xl font-medium text-text-primary placeholder:text-text-secondary/50 focus:border-primary focus:outline-none focus:ring-0"
                />
                @error('businessAddress.zip')
                    <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end pt-6">
            @if ($isContinuousFlow)
                <flux:button type="submit" variant="primary" icon-trailing="arrow-right">
                    Continue
                </flux:button>
            @else
                <flux:button type="submit" variant="primary" icon-trailing="check">
                    Complete Setup
                </flux:button>
            @endif
        </div>
    </form>

    @script
    <script>
        (function() {
            var livewireComponent = $wire;

            // Define the callback function globally for Google Maps
            window.initAutocomplete = function() {
                var input = document.getElementById('autocomplete-address');
                if (!input) {
                    return;
                }

                var autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address'],
                    componentRestrictions: { country: 'us' },
                    fields: ['place_id', 'geometry', 'address_components', 'formatted_address']
                });

                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();

                    if (!place.address_components) {
                        return;
                    }

                    // Parse address components
                    var streetNumber = '';
                    var route = '';
                    var city = '';
                    var state = '';
                    var zip = '';
                    var county = '';
                    var country = '';

                    place.address_components.forEach(function(component) {
                        var types = component.types;

                        if (types.includes('street_number')) {
                            streetNumber = component.long_name;
                        }
                        if (types.includes('route')) {
                            route = component.long_name;
                        }
                        if (types.includes('locality')) {
                            city = component.long_name;
                        }
                        if (types.includes('administrative_area_level_1')) {
                            state = component.short_name;
                        }
                        if (types.includes('postal_code')) {
                            zip = component.long_name;
                        }
                        if (types.includes('administrative_area_level_2')) {
                            county = component.long_name;
                        }
                        if (types.includes('country')) {
                            country = component.short_name;
                        }
                    });

                    // Build address line 1
                    var line1 = [streetNumber, route].filter(Boolean).join(' ');

                    // Extract geo data
                    var lat = place.geometry && place.geometry.location ? place.geometry.location.lat() : null;
                    var lng = place.geometry && place.geometry.location ? place.geometry.location.lng() : null;

                    // Update Livewire component with all fields
                    livewireComponent.updateAddressFromAutocomplete({
                        line1: line1,
                        line2: '',
                        city: city,
                        state: state,
                        zip: zip,
                        place_id: place.place_id || null,
                        formatted_address: place.formatted_address || null,
                        lat: lat,
                        lng: lng,
                        county: county || null,
                        country: country || null
                    });
                });
            };

            // Load Google Maps API with callback
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initAutocomplete';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            } else {
                window.initAutocomplete();
            }
        })();
    </script>
    @endscript
</div>

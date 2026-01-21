<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header :title="$isEditing ? 'Edit Project' : 'Create Project'">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $isEditing ? 'Edit' : 'Create'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        {{-- Project Info --}}
        <x-ui.card>
            <x-slot:header>Project Information</x-slot:header>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Project Name *</flux:label>
                    <flux:input wire:model="name" placeholder="e.g., 123 Main St Commercial" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Job Number</flux:label>
                    <flux:input wire:model="job_number" placeholder="Optional" />
                    <flux:error name="job_number" />
                </flux:field>

                <flux:field>
                    <flux:label>Your Role on This Project *</flux:label>
                    <flux:select wire:model="claimant_type">
                        @foreach($claimantTypes as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="claimant_type" />
                </flux:field>
            </div>
        </x-ui.card>

        {{-- Jobsite Address --}}
        <x-ui.card>
            <x-slot:header>Jobsite Address</x-slot:header>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Street Address</flux:label>
                    <flux:input id="jobsite-autocomplete" wire:model="jobsite_address1" placeholder="Start typing to search..." autocomplete="off" />
                    <flux:error name="jobsite_address1" />
                </flux:field>

                <flux:field>
                    <flux:label>Address Line 2</flux:label>
                    <flux:input wire:model="jobsite_address2" placeholder="Suite, Unit, etc." />
                    <flux:error name="jobsite_address2" />
                </flux:field>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <flux:field class="col-span-2 md:col-span-1">
                        <flux:label>City</flux:label>
                        <flux:input wire:model="jobsite_city" />
                        <flux:error name="jobsite_city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>State *</flux:label>
                        <flux:select wire:model="jobsite_state">
                            <option value="">Select...</option>
                            @foreach($states as $code => $name)
                                <option value="{{ $code }}">{{ $code }} - {{ $name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="jobsite_state" />
                    </flux:field>

                    <flux:field>
                        <flux:label>ZIP Code</flux:label>
                        <flux:input wire:model="jobsite_zip" />
                        <flux:error name="jobsite_zip" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>County</flux:label>
                    <flux:input wire:model="jobsite_county" placeholder="e.g., Los Angeles County" />
                    @if($jobsite_county_google && $jobsite_county !== $jobsite_county_google)
                        <flux:description class="text-warning">
                            Modified from Google Maps: {{ $jobsite_county_google }}
                        </flux:description>
                    @else
                        <flux:description>Important for recording liens in the correct jurisdiction.</flux:description>
                    @endif
                    <flux:error name="jobsite_county" />
                </flux:field>
            </div>
        </x-ui.card>

        {{-- Legal Description --}}
        <x-ui.card>
            <x-slot:header>Property Details (Optional)</x-slot:header>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Legal Description</flux:label>
                    <flux:textarea wire:model="legal_description" rows="3" placeholder="As it appears on the deed..." />
                    <flux:error name="legal_description" />
                </flux:field>

                <flux:field>
                    <flux:label>Assessor Parcel Number (APN)</flux:label>
                    <flux:input wire:model="apn" placeholder="e.g., 123-456-789" />
                    <flux:error name="apn" />
                </flux:field>
            </div>
        </x-ui.card>

        {{-- Important Dates --}}
        <x-ui.card>
            <x-slot:header>Important Dates</x-slot:header>
            <x-slot:description>
                These dates are used to calculate your lien deadlines. Enter dates as they become known.
            </x-slot:description>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Contract Date</flux:label>
                    <flux:input type="date" wire:model="contract_date" />
                    <flux:error name="contract_date" />
                </flux:field>

                <flux:field>
                    <flux:label>First Furnish Date</flux:label>
                    <flux:input type="date" wire:model="first_furnish_date" />
                    <flux:description>When you first provided labor/materials</flux:description>
                    <flux:error name="first_furnish_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Last Furnish Date</flux:label>
                    <flux:input type="date" wire:model="last_furnish_date" />
                    <flux:description>When you last provided labor/materials</flux:description>
                    <flux:error name="last_furnish_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Completion Date</flux:label>
                    <flux:input type="date" wire:model="completion_date" />
                    <flux:error name="completion_date" />
                </flux:field>

                <flux:field>
                    <flux:label>Notice of Completion Recorded</flux:label>
                    <flux:input type="date" wire:model="noc_recorded_date" />
                    <flux:description>Can shorten lien deadlines</flux:description>
                    <flux:error name="noc_recorded_date" />
                </flux:field>
            </div>
        </x-ui.card>

        {{-- Actions --}}
        <div class="flex justify-end gap-3">
            <flux:button href="{{ $isEditing ? route('lien.projects.show', $project) : route('lien.projects.index') }}" variant="ghost">
                Cancel
            </flux:button>
            <flux:button type="submit" variant="primary">
                {{ $isEditing ? 'Save Changes' : 'Create Project' }}
            </flux:button>
        </div>
    </form>

    @script
    <script>
        (function() {
            var livewireComponent = $wire;

            // Define the callback function globally for Google Maps
            window.initJobsiteAutocomplete = function() {
                var input = document.getElementById('jobsite-autocomplete');
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
                    });

                    // Build address line 1
                    var line1 = [streetNumber, route].filter(Boolean).join(' ');

                    // Extract geo data
                    var lat = place.geometry && place.geometry.location ? place.geometry.location.lat() : null;
                    var lng = place.geometry && place.geometry.location ? place.geometry.location.lng() : null;

                    // Update Livewire component with all fields
                    livewireComponent.updateAddressFromAutocomplete({
                        line1: line1,
                        city: city,
                        state: state,
                        zip: zip,
                        county: county || null,
                        place_id: place.place_id || null,
                        formatted_address: place.formatted_address || null,
                        lat: lat,
                        lng: lng
                    });
                });
            };

            // Load Google Maps API with callback
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                var script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initJobsiteAutocomplete';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            } else {
                window.initJobsiteAutocomplete();
            }
        })();
    </script>
    @endscript
</div>

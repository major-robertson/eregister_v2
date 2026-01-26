<div class="max-w-3xl mx-auto space-y-6">
    <x-ui.page-header :title="$isEditing ? 'Edit Project' : 'Create Project'">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $isEditing ? 'Edit' : 'Create'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    {{-- Progress Steps --}}
    <div class="flex items-center justify-between mb-8">
        @foreach($stepTitles as $stepNum => $label)
        @php
        $isActive = $step === $stepNum;
        $isComplete = $step > $stepNum;
        @endphp
        <div class="flex items-center {{ $stepNum < $totalSteps ? 'flex-1' : '' }}">
            <button wire:click="goToStep({{ $stepNum }})"
                @class([ 'flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition'
                , 'bg-blue-600 text-white'=> $isActive,
                'bg-green-500 text-white' => $isComplete,
                'bg-zinc-200 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400' => !$isActive && !$isComplete,
                'cursor-pointer' => $isComplete,
                'cursor-default' => !$isComplete,
                ])
                @if(!$isComplete) disabled @endif
                >
                @if($isComplete)
                <flux:icon name="check" class="w-4 h-4" />
                @else
                {{ $stepNum }}
                @endif
            </button>
            <span class="ml-2 text-sm {{ $isActive ? 'font-medium' : 'text-zinc-500' }} hidden md:inline">{{ $label
                }}</span>
            @if($stepNum < $totalSteps) <div class="flex-1 h-px bg-zinc-200 dark:bg-zinc-700 mx-4">
        </div>
        @endif
    </div>
    @endforeach
</div>

{{-- Step Content --}}
<x-ui.card>
    @if($step === 1)
    {{-- Step 1: Project Info --}}
    <x-slot:header>Project Information</x-slot:header>

    <div class="space-y-4">
        <flux:field>
            <flux:label>Project Name *</flux:label>
            <flux:input wire:model="name" placeholder="e.g., 123 Main St Commercial" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Job Number</flux:label>
            <flux:input wire:model="job_number" placeholder="Optional internal reference" />
            <flux:error name="job_number" />
        </flux:field>

        <flux:field>
            <flux:label>Your Role on This Project *</flux:label>
            <flux:select wire:model="claimant_type">
                @foreach($claimantTypes as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </flux:select>
            <flux:description>This determines which parties you'll need to provide and affects your lien rights.
            </flux:description>
            <flux:error name="claimant_type" />
        </flux:field>
    </div>

    @elseif($step === 2)
    {{-- Step 2: Jobsite Address --}}
    <x-slot:header>Jobsite Address</x-slot:header>

    <div class="space-y-4">
        <flux:field>
            <flux:label>Street Address</flux:label>
            <input type="text" id="jobsite-autocomplete" wire:model.blur="jobsite_address1"
                placeholder="Start typing to search..." autocomplete="off"
                class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-800 shadow-sm placeholder:text-zinc-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:placeholder:text-zinc-500" />
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
                <flux:select wire:model.live="jobsite_state">
                    <option value="">Select...</option>
                    @foreach($states as $code => $name)
                    <option value="{{ $code }}">{{ $code }}</option>
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

        <flux:field>
            <flux:label>Property Type *</flux:label>
            <flux:select wire:model="property_class">
                <option value="">Select...</option>
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="government">Government / Public</option>
            </flux:select>
            <flux:description>Helps determine applicable lien rules.</flux:description>
            <flux:error name="property_class" />
        </flux:field>
    </div>

    @elseif($step === 3)
    {{-- Step 3: Important Dates (Optional) --}}
    <x-slot:header>
        <div class="flex items-center gap-2">
            Important Dates
            <flux:badge color="blue" size="sm">Optional</flux:badge>
        </div>
    </x-slot:header>

    <div class="space-y-6">
        {{-- Info Banner --}}
        <flux:callout color="blue" icon="information-circle">
            These dates are optional, but without them we can't calculate your filing deadlines or send you alerts.
        </flux:callout>

        <div class="space-y-4">
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
        </div>

        {{-- NOC Question - only for CA, NV, AZ, OR --}}
        @if($this->showNocQuestion())
        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-6 space-y-4">
            <flux:field>
                <flux:label>Do you know if a Notice of Completion was recorded?</flux:label>
                <div class="flex gap-4 mt-2">
                    @foreach($nocStatuses as $status)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="noc_status" value="{{ $status->value }}"
                            class="h-4 w-4 border-zinc-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $status->label() }}</span>
                    </label>
                    @endforeach
                </div>
                <flux:description>If you're unsure, choose Not sure — we'll use the safest deadline.</flux:description>
                <flux:error name="noc_status" />
            </flux:field>

            @if($noc_status === 'yes')
            <flux:field>
                <flux:label>NOC Recorded Date *</flux:label>
                <flux:input type="date" wire:model="noc_recorded_at" />
                <flux:error name="noc_recorded_at" />
            </flux:field>
            @endif
        </div>
        @endif
    </div>
    @endif
</x-ui.card>

{{-- Navigation --}}
<div class="flex justify-between">
    <div>
        @if($step > 1)
        <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left">
            Back
        </flux:button>
        @else
        <flux:button href="{{ route('lien.projects.index') }}" variant="ghost">
            Cancel
        </flux:button>
        @endif
    </div>

    <div class="flex gap-3">
        @if($step < $totalSteps) <flux:button wire:click="nextStep" wire:loading.attr="disabled" variant="primary"
            icon-trailing="arrow-right">
            <span wire:loading.remove wire:target="nextStep">Continue</span>
            <span wire:loading wire:target="nextStep">Saving...</span>
            </flux:button>
            @elseif($step === 3)
            {{-- Step 3: Show Skip and Save buttons --}}
            <flux:button wire:click="skipStep" wire:loading.attr="disabled" variant="ghost">
                <span wire:loading.remove wire:target="skipStep">Skip for now</span>
                <span wire:loading wire:target="skipStep">Saving...</span>
            </flux:button>
            <flux:button wire:click="save" wire:loading.attr="disabled" variant="primary" icon-trailing="check">
                <span wire:loading.remove wire:target="save">{{ $isEditing ? 'Save Changes' : 'Create Project' }}</span>
                <span wire:loading wire:target="save">Saving...</span>
            </flux:button>
            @endif
    </div>
</div>

@if($step === 2)
@script
<script>
    (function() {
                var livewireComponent = $wire;
                var autocompleteInstance = null;

                // Define the callback function globally for Google Maps
                window.initJobsiteAutocomplete = function() {
                    // Wait for the input element to exist
                    var attempts = 0;
                    var maxAttempts = 20;

                    function tryInit() {
                        var input = document.getElementById('jobsite-autocomplete');

                        if (!input) {
                            attempts++;
                            if (attempts < maxAttempts) {
                                setTimeout(tryInit, 100);
                            }
                            return;
                        }

                        // Avoid duplicate initialization
                        if (input.dataset.autocompleteInitialized) {
                            return;
                        }
                        input.dataset.autocompleteInitialized = 'true';

                        autocompleteInstance = new google.maps.places.Autocomplete(input, {
                            types: ['address'],
                            componentRestrictions: { country: 'us' },
                            fields: ['place_id', 'geometry', 'address_components', 'formatted_address']
                        });

                        autocompleteInstance.addListener('place_changed', function() {
                            var place = autocompleteInstance.getPlace();

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

                            // Update input value immediately for visual feedback
                            input.value = line1;

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

                        // Prevent form submission on Enter key in autocomplete
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                            }
                        });
                    }

                    tryInit();
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
@endif
</div>
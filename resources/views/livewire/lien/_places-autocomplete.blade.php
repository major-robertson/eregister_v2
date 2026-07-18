{{--
    Delegated Google Places autocomplete for address inputs, shared by the
    lien address forms (waiver wizard contact/owner modals, party manager,
    contact form). Include once per page; any

        <input data-places-autocomplete data-places-method="methodName">

    initializes on first focus — focus-time init is what makes this work for
    inputs inside modals, which don't exist at page load. The picked address
    is parsed like the project form's jobsite autocomplete (street, city,
    state, ZIP, county from administrative_area_level_2) and handed to the
    input's Livewire component via the named method as
    {line1, city, state, zip, county}.

    The document-level listener survives Livewire navigation and modal
    re-renders; the window guard keeps it single.
--}}
<script>
    (function () {
        if (window.lienPlacesDelegated) {
            return;
        }
        window.lienPlacesDelegated = true;

        var mapsRequested = false;

        function ensureMaps(callback) {
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                callback();
                return;
            }

            window.lienPlacesQueue = window.lienPlacesQueue || [];
            window.lienPlacesQueue.push(callback);

            if (mapsRequested) {
                return;
            }
            mapsRequested = true;

            window.initLienPlacesAutocomplete = function () {
                (window.lienPlacesQueue || []).forEach(function (cb) { cb(); });
                window.lienPlacesQueue = [];
            };

            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places&callback=initLienPlacesAutocomplete';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }

        document.addEventListener('focusin', function (event) {
            var input = event.target;

            if (!(input instanceof HTMLInputElement) || input.dataset.placesAutocomplete === undefined) {
                return;
            }
            if (input.dataset.placesInitialized) {
                return;
            }
            input.dataset.placesInitialized = 'true';

            ensureMaps(function () {
                var autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address'],
                    componentRestrictions: { country: 'us' },
                    fields: ['address_components'],
                });

                autocomplete.addListener('place_changed', function () {
                    var place = autocomplete.getPlace();

                    if (!place.address_components) {
                        return;
                    }

                    var parts = {
                        street_number: '',
                        route: '',
                        locality: '',
                        administrative_area_level_1: '',
                        administrative_area_level_2: '',
                        postal_code: '',
                    };

                    place.address_components.forEach(function (component) {
                        component.types.forEach(function (type) {
                            if (type in parts) {
                                parts[type] = type === 'administrative_area_level_1'
                                    ? component.short_name
                                    : component.long_name;
                            }
                        });
                    });

                    var line1 = [parts.street_number, parts.route].filter(Boolean).join(' ');
                    input.value = line1;

                    var root = input.closest('[wire\\:id]');
                    var component = root && window.Livewire
                        ? window.Livewire.find(root.getAttribute('wire:id'))
                        : null;

                    if (!component) {
                        return;
                    }

                    component.call(input.dataset.placesMethod || 'updateAddressFromAutocomplete', {
                        line1: line1,
                        city: parts.locality,
                        state: parts.administrative_area_level_1,
                        zip: parts.postal_code,
                        county: parts.administrative_area_level_2 || null,
                    });
                });

                // Enter selects a suggestion; it must not submit the form.
                input.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            });
        });
    })();
</script>

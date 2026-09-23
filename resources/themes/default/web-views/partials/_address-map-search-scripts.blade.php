{{--
    Shared JS for the address-form map search widget (see
    _address-map-search.blade.php). Used by:
      - web-views/users-profile/account-address.blade.php
      - web-views/users-profile/account-address-edit.blade.php
      - web-views/checkout/shipping.blade.php

    Requires $countriesName to be available in the including view (all three
    controllers already pass it).

    Fixed element IDs referenced (#location, #map, #latitude, #longitude,
    #address, #system-country-restrict-status) are shared across all three
    pages intentionally - see _address-map-search.blade.php for why they are
    not prefixed.
--}}
<script>
    "use strict";
    const deliveryRestrictedCountries = @json($countriesName);

    function deliveryRestrictedCountriesCheck(countryOrCode, elementSelector, inputElement) {
        const foundIndex = deliveryRestrictedCountries.findIndex(country => country.toLowerCase() === countryOrCode
            .toLowerCase());
        if (foundIndex !== -1) {
            $(elementSelector).removeClass('map-area-alert-danger');
            $(inputElement).parent().find('.map-address-alert').removeClass('opacity-100').addClass('opacity-0')
        } else {
            $(elementSelector).addClass('map-area-alert-danger');
            $(inputElement).val('')
            $(inputElement).parent().find('.map-address-alert').removeClass('opacity-0').addClass('opacity-100')
        }
    }
</script>
<script>
    function searchLocation() {
        var location = document.getElementById('location').value;
        if (location.trim() !== "") {
            var mapFrame = document.getElementById('map');
            mapFrame.src = "https://www.google.com/maps?q=" + encodeURIComponent(location) + "&output=embed";

            // Fetch lat/lng and address using Nominatim
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        document.getElementById('latitude').value = data[0].lat;
                        document.getElementById('longitude').value = data[0].lon;
                        document.getElementById('address').value = data[0].display_name; // auto-fill address textarea
                    }
                })
                .catch(err => console.error('Location fetch error:', err));
        }
    }
</script>

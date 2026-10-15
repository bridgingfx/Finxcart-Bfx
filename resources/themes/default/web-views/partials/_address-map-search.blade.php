{{--
    Shared "search a location on the map" widget used by:
      - web-views/users-profile/account-address.blade.php (add-address modal)
      - web-views/users-profile/account-address-edit.blade.php (edit-address page)
      - web-views/checkout/shipping.blade.php (checkout inline address form)

    All three pages already rely on the fixed element IDs #location, #map,
    #latitude, #longitude and #address via shared JS (searchLocation(),
    the Google Places autocomplete block, and shipping.js), and the three
    forms never appear on the same rendered page (different routes), so the
    IDs are intentionally NOT prefixed here — prefixing would break the
    existing per-page JS that hard-codes these selectors.

    Params:
      - mapQuery (string)  required. Value used for the initial iframe
        `?q=` embed URL (already url-encoded/formatted by the caller).
      - locationValue (string, optional) prefill for the #location search
        input. Defaults to empty.
--}}
@php
    $locationValue = $locationValue ?? '';
@endphp
<div class="form-group map-area-alert-border location-map-address-canvas-area">
    <div id="search-box" style="margin-bottom: 10px;">
        <input type="text" id="location" value="{{ $locationValue }}"
            placeholder="{{ translate('search_here') }}"
            style="max-width: 300px; padding: 5px; margin-right: 5px;">
        <button type="button" onclick="searchLocation()" class="btn btn-primary">
            {{ translate('Search') }}
        </button>
    </div>

    <iframe id="map"
        src="https://www.google.com/maps?q={{ $mapQuery }}&output=embed"
        style="width: 100%; height: 300px; border: none;" allowfullscreen
        loading="lazy">
    </iframe>
</div>

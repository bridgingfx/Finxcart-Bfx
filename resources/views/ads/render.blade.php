<?php
// resources/views/ads/render.blade.php
?>

<div class="ad-container" data-ad-id="{{ $ad->id }}">
    @if($ad->type === 'image')
        {{-- Image Ad: Wrapped in a link --}}
        <a href="{{ $clickUrl }}" target="_blank" rel="noopener sponsored">
            <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->name }}" style="width: 100%; height: auto;">
        </a>

    @elseif($ad->type === 'code')
        {{--
          Code Ad (e.g., AdSense or your complex HTML)
          This type is NOT wrapped in a link. It is rendered exactly as-is.
        --}}
        {!! $ad->content !!}

    @elseif($ad->type === 'text')
        {{-- Text Ad: Wrapped in a link --}}
        <a href="{{ $clickUrl }}" target="_blank" rel="noopener sponsored" style="text-decoration: none;">
            {!! $ad->content !!}
        </a>
    @endif

    {{--
      Impression Tracking Pixel.
      This fires when the ad is rendered by the browser.
    --}}
    <img src="{{ $impressionUrl }}" width="1" height="1" style="display:none; visibility:hidden;" alt="" />
</div>

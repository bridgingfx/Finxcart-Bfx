

<div class="ad-container" data-ad-id="{{ $ad->id }}">
    @if($ad->type === 'image')
        <a href="{{ $clickUrl }}" target="_blank" rel="noopener sponsored">
            <img src="{{ asset('storage/' . $ad->image_path) }}" alt="{{ $ad->name }}" style="width: 100%; height: auto;">
        </a>

    @elseif($ad->type === 'code')
        {{-- Embed code is admin-authored HTML/JS. Only admins can create ads. --}}
        {!! $ad->content !!}

    @elseif($ad->type === 'text')
        <a href="{{ $clickUrl }}" target="_blank" rel="noopener sponsored" style="text-decoration: none;">
            {{ $ad->content }}
        </a>
    @endif

    {{-- Impression Tracking Pixel --}}
    <img src="{{ $impressionUrl }}" width="1" height="1" style="display:none; visibility:hidden;" alt="" />
</div>

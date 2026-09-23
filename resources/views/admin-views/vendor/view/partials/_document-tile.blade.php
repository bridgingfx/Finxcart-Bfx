@php
    $isPdf = !empty($rawFilename) && str_ends_with(strtolower($rawFilename), '.pdf');
@endphp
<div>
    <div class="text-muted mb-2 fw-semibold">
        {{ $label }}
        @if($required ?? false)
            <span class="text-danger">*</span>
        @endif
    </div>
    @if(!empty($url))
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="document-tile">
            <div class="document-tile-preview">
                @if($isPdf)
                    <i class="fi fi-rr-file-pdf"></i>
                @else
                    <img src="{{ $url }}" alt="{{ $label }}">
                @endif
            </div>
            <div class="document-tile-label">
                <span>{{ $isPdf ? translate('download_pdf') : translate('view_full_size') }}</span>
                <i class="fi fi-rr-eye"></i>
            </div>
        </a>
    @else
        <div class="document-tile-empty">
            <i class="fi fi-rr-picture"></i>
            <span>{{ translate('no_data_found') }}</span>
        </div>
    @endif
</div>

<div class="event-about-panel">
    <h3 class="event-about-heading">{{ translate('about_the_event') }}</h3>
    @if($product->video_url != null && (str_contains($product->video_url, "youtube.com/embed/")))
        <div class="mb-4">
            <iframe class="w-100" height="315" src="{{$product->video_url}}"></iframe>
        </div>
    @endif
    @if ($product['details'])
        <div class="event-about-text rich-editor-html-content">
            {!! clean_html($product['details']) !!}
        </div>
    @else
        <div class="text-center text-capitalize py-5">
            <img class="mw-90" src="{{theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg')}}" alt="">
            <p class="text-capitalize mt-2"><small>{{ translate('product_details_not_found') }}!</small></p>
        </div>
    @endif

    @if($product->event_days || $product->event_industry_brands || $product->event_speakers_count || $product->event_audience)
        <div class="row g-2 mt-2">
            @if($product->event_days)
                <div class="col-6 col-md-3">
                    <div class="event-stat-card border p-2 text-center h-100">
                        <div class="fs-20 font-bold event-stat-value">{{ $product->event_days }}</div>
                        <div class="fs-12 text-muted">{{ translate('event_days') }}</div>
                    </div>
                </div>
            @endif
            @if($product->event_industry_brands)
                <div class="col-6 col-md-3">
                    <div class="event-stat-card border p-2 text-center h-100">
                        <div class="fs-20 font-bold event-stat-value">{{ $product->event_industry_brands }}</div>
                        <div class="fs-12 text-muted">{{ translate('industry_brands') }}</div>
                    </div>
                </div>
            @endif
            @if($product->event_speakers_count)
                <div class="col-6 col-md-3">
                    <div class="event-stat-card border p-2 text-center h-100">
                        <div class="fs-20 font-bold event-stat-value">{{ $product->event_speakers_count }}</div>
                        <div class="fs-12 text-muted">{{ translate('speakers') }}</div>
                    </div>
                </div>
            @endif
            @if($product->event_audience)
                <div class="col-6 col-md-3">
                    <div class="event-stat-card border p-2 text-center h-100">
                        <div class="fs-20 font-bold event-stat-value">{{ $product->event_audience }}</div>
                        <div class="fs-12 text-muted">{{ translate('audience') }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>

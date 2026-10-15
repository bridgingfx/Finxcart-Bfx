@extends('layouts.front-end.app')

@section('title', $businessPage?->title)

@push('css_or_js')
    <style>
        .business-page-content {
            line-height: 1.7;
        }

        .business-page-content p,
        .business-page-content ul,
        .business-page-content ol,
        .business-page-content blockquote {
            margin-bottom: 1rem;
        }

        .business-page-content h1,
        .business-page-content h2,
        .business-page-content h3,
        .business-page-content h4,
        .business-page-content h5,
        .business-page-content h6 {
            margin: 1.25rem 0 .75rem;
            line-height: 1.3;
        }

        .business-page-content li + li {
            margin-top: .4rem;
        }

        .business-page-content > :last-child {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')

@if($businessPage?->slug == 'about-us')
    @if(view()->exists('web-views.pages.about_us_content'))
        @include('web-views.pages.about_us_content')
    @else
        <div class="container for-container">
            <div class="business-pages-banner-section mt-4 {{ empty($businessPage?->description) ? 'mb-4' : '' }}"
                data-bg-img="{{ getStorageImages(path: $businessPage?->banner_full_url, type: 'business-page') }}">
                <div class="container">
                    <h1 class="text-center text-capitalize font-semi-bold fs-24">{{ $businessPage?->title }}</h1>
                </div>
            </div>

            @if(!empty($businessPage?->description))
                <div class="card my-4">
                    <div class="card-body">
                        <div class="for-padding text-justify business-page-content">
                            {!! $businessPage?->description !!}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
@else
    {{-- Original code for all other business pages --}}
    <div class="container for-container">
        <div class="business-pages-banner-section mt-4 {{ empty($businessPage?->description) ? 'mb-4' : '' }}"
            data-bg-img="{{ getStorageImages(path: $businessPage?->banner_full_url, type: 'business-page') }}">
            <div class="container">
                <h1 class="text-center text-capitalize font-semi-bold fs-24">{{ $businessPage?->title }}</h1>
            </div>
        </div>

        @if(!empty($businessPage?->description))
            <div class="card my-4">
                <div class="card-body">
                    <div class="for-padding text-justify business-page-content">
                        {!! $businessPage?->description !!}
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

@endsection

@extends('theme-views.layouts.app')

@section('title', translate('FAQ'))

@push('css_or_js')
    <style>
        .faq-list {
            display: grid;
            gap: 12px;
        }

        .faq-list .accordion-item {
            border: 1px solid #e7edf5;
            border-radius: 8px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(20, 36, 62, .05);
        }

        .faq-list .accordion-button {
            gap: 14px;
            padding: 16px 18px;
            box-shadow: none;
        }

        .faq-list .accordion-button::after {
            display: none;
        }

        .faq-question-text {
            flex: 1;
            line-height: 1.45;
            text-align: start;
        }

        .faq-plus {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 1px solid #d8e2ef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            line-height: 1;
            color: var(--bs-primary);
            transition: transform .2s ease, background .2s ease, color .2s ease;
        }

        .faq-list .accordion-button:not(.collapsed) .faq-plus {
            transform: rotate(45deg);
            background: var(--bs-primary);
            color: #fff;
            border-color: var(--bs-primary);
        }
    </style>
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 pt-3 mb-sm-5">
        <div class="page-title overlay py-5 __opacity-half background-custom-fit"
             data-bg-img = {{getStorageImages(path: imagePathProcessing(imageData: (isset($pageTitleBanner['value']) ?json_decode($pageTitleBanner['value'])?->image : null),path: 'banner'),source: theme_asset('assets/img/media/page-title-bg.png'))}}>
        <div class="container">
                <h1 class="absolute-white text-center">{{ translate('FAQ') }}</h1>
            </div>
        </div>
        <?php
            $length=count($helps);
                if($length%2!=0){
                    $first=($length+1)/2;
                }else{
                    $first=$length/2;
                }
        ?>
        <div class="container">
            <div class="my-4">
                <div class="accordion accordion-flush faq-list" id="accordionFlushExample">
                    @php($index = 0)
                    @foreach($helps as $index => $help)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="flush-heading{{ $help['id'] }}">
                                <button class="accordion-button text-dark fw-semibold" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#flush-collapse{{ $help['id'] }}"
                                        aria-expanded="false" aria-controls="flush-collapse{{ $help['id'] }}">
                                    <span class="faq-question-text">{{ $help['question'] }}</span>
                                    <span class="faq-plus" aria-hidden="true">+</span>
                                </button>
                            </h2>
                            <div id="flush-collapse{{ $help['id'] }}"
                                 class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                                 aria-labelledby="flush-heading{{ $help['id'] }}"
                                 data-bs-parent="#accordionFlushExample">
                                <div class="accordion-body">
                                    {{ $help['answer'] }}
                                </div>
                            </div>
                        </div>
                        @php($index++)
                    @endforeach
                </div>
            </div>
        </div>
    </main>
@endsection

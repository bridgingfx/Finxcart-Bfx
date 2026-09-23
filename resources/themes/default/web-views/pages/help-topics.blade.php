@extends('layouts.front-end.app')

@section('title',translate('FAQ'))

@push('css_or_js')
    <style>
        .faq-shell {
            max-width: 980px;
            margin-inline: auto;
        }

        .faq-list {
            display: grid;
            gap: 12px;
        }

        .faq-item {
            border: 1px solid #e7edf5;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(20, 36, 62, .05);
            overflow: hidden;
        }

        .faq-question {
            width: 100%;
            min-height: 58px;
            border: 0;
            background: transparent;
            color: #1f2937;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 18px;
            font-weight: 600;
            text-align: start;
            cursor: pointer;
        }

        .faq-question:hover,
        .faq-question[aria-expanded="true"] {
            background: #f7faff;
            color: var(--web-primary, #1455ac);
        }

        .faq-question-text {
            flex: 1;
            line-height: 1.45;
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
            color: var(--web-primary, #1455ac);
            transition: transform .2s ease, background .2s ease, color .2s ease;
        }

        .faq-question[aria-expanded="true"] .faq-plus {
            transform: rotate(45deg);
            background: var(--web-primary, #1455ac);
            color: #fff;
            border-color: var(--web-primary, #1455ac);
        }

        .faq-answer {
            padding: 0 18px 18px 18px;
            color: #4b5563;
            line-height: 1.65;
        }

        @media (max-width: 575px) {
            .faq-question {
                padding: 14px;
                align-items: flex-start;
            }
        }
    </style>
@endpush

@section('content')
    <div class="__inline-60">
        <div class="container rtl">
            <div class="row">
                <div class="col-md-12 sidebar_heading text-center mb-2">
                    <h1 class="text-center pt-4 fs-24 font-semi-bold text-capitalize">{{ translate('frequently_asked_question') }}</h1>
                </div>
            </div>
            <hr>
        </div>

        <div class="container pb-5 mb-2 mb-md-4 mt-3 rtl">
            <div class="row">

                @php $length=count($helps); @endphp
                @if(count($helps) > 0)
                    <section class="col-12 mt-3">
                        <section class="container pt-4 pb-5">
                            <div class="faq-shell faq-list" id="faqAccordion">
                                @foreach($helps as $key => $help)
                                    <article class="faq-item">
                                        <button class="faq-question" type="button"
                                                data-toggle="collapse"
                                                data-target="#faqAnswer{{ $help['id'] }}"
                                                aria-expanded="{{ $key == 0 ? 'true' : 'false' }}"
                                                aria-controls="faqAnswer{{ $help['id'] }}">
                                            <span class="faq-question-text">{{ $help['question'] }}</span>
                                            <span class="faq-plus" aria-hidden="true">+</span>
                                        </button>
                                        <div id="faqAnswer{{ $help['id'] }}"
                                             class="collapse {{ $key == 0 ? 'show' : '' }}"
                                             data-parent="#faqAccordion">
                                            <div class="faq-answer">
                                                {{ $help['answer'] }}
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    </section>
                @else
                    <div class="col-12">
                        <div class="d-flex justify-content-center align-items-center pt-4">
                            <div class="d-flex flex-column justify-content-center align-items-center gap-3">
                                <img src="{{ dynamicStorage(path: 'public/assets/front-end/img/empty-icons/empty-faqs.svg') }}"
                                     alt="{{ translate('brand') }}" class="img-fluid" width="100">
                                <h5 class="text-muted fs-14 font-semi-bold text-center">{{ translate('there_is_no_FAQs') }}</h5>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';

        $(document).on('shown.bs.collapse hidden.bs.collapse', '#faqAccordion .collapse', function () {
            const isOpen = $(this).hasClass('show');
            $('[data-target="#' + this.id + '"]').attr('aria-expanded', isOpen ? 'true' : 'false');
        });
    </script>
@endpush

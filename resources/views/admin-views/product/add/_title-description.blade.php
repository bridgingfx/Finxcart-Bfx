<div class="card">
    <div class="card-body">
        <div class="row gy-4">
            {{-- All products are Digital-only now; Product Type is a fixed hidden field
                 (see _general-setup.blade.php). --}}
            <div class="col-md-12" id="listing-type-section">
                <div class="form-group">
                    <label class="form-label d-block">
                        {{ translate('listing_type') }}
                        <span class="input-required-icon">*</span>
                    </label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                   id="listing_type_product" value="product" required
                                   {{ old('listing_type', 'product') == 'product' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_product">{{ translate('product') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                   id="listing_type_event" value="event"
                                   {{ old('listing_type') == 'event' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_event">{{ translate('event') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                   id="listing_type_broker" value="broker"
                                   {{ old('listing_type') == 'broker' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_broker">{{ translate('broker') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="position-relative nav--tab-wrapper">
                    <ul class="nav nav-pills nav--tab text-capitalize lang_tab" id="pills-tab"
                        role="tablist">
                        @foreach ($languages as $lang)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $lang == $defaultLanguage ? 'active' : '' }}"
                                   id="{{ $lang }}-link" data-bs-toggle="pill" href="#{{ $lang }}-form"
                                   role="tab" aria-controls="{{ $lang }}-form" aria-selected="true">
                                    {{ getLanguageName($lang) . '(' . strtoupper($lang) . ')' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="nav--tab__prev">
                        <button class="btn btn-circle border-0 bg-white text-primary">
                            <i class="fi fi-sr-angle-left"></i>
                        </button>
                    </div>
                    <div class="nav--tab__next">
                        <button class="btn btn-circle border-0 bg-white text-primary">
                            <i class="fi fi-sr-angle-right"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex justify-content-end">
                    <a class="btn btn-primary btn-sm p-2 text-capitalize"
                       href="{{ route('admin.products.product-gallery') }}">
                        <i class="fi fi-rr-plus-small"></i>
                        {{ translate('add_info_from_gallery') }}
                    </a>
                </div>
            </div>
            <div class="col-12">
                <div class="tab-content" id="pills-tabContent">
                    @foreach ($languages as $lang)
                        <div class="tab-pane fade {{ $lang == $defaultLanguage ? 'show active' : '' }}"
                             id="{{ $lang }}-form" role="tabpanel">
                            <div class="form-group">
                                <label class="form-label" for="{{ $lang }}_name">
                                    {{ translate('product_name') }}
                                    ({{ strtoupper($lang) }})
                                    @if($lang == $defaultLanguage)
                                        <span class="input-required-icon text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" {{ $lang == $defaultLanguage ? 'required' : '' }} name="name[]"
                                       id="{{ $lang }}_name"
                                       class="form-control {{ $lang == $defaultLanguage ? 'product-title-default-language' : '' }}"
                                       placeholder="{{ translate('ex') }}: {{ translate('new_Product') }}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $lang }}">
                            <div class="form-group pt-2">
                                <label class="form-label" for="{{ $lang }}_description">
                                    {{ translate('description') }} ({{ strtoupper($lang) }})
                                    @if($lang == $defaultLanguage)
                                        <span class="input-required-icon text-danger">*</span>
                                    @endif
                                </label>

                                <div id="description-{{$lang }}-editor" class="quill-editor {{ $lang == $defaultLanguage ? 'product-description-default-language-editor' : '' }}"></div>
                                <textarea name="description[]" id="description-{{$lang }}"
                                          class="{{ $lang == $defaultLanguage ? 'product-description-default-language' : '' }}"
                                          style="display:none;"></textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

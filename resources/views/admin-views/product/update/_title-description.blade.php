<div class="card">
    <div class="card-body">
        <div class="row gy-4">
            {{-- Product Type is fixed (Digital-only for new selections); legacy physical
                 products keep their stored value in the hidden field (_general-setup.blade.php). --}}
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
                                   {{ $currentListingType == 'product' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_product">{{ translate('product') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                   id="listing_type_event" value="event"
                                   {{ $currentListingType == 'event' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_event">{{ translate('event') }}</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input listing-type-input" type="radio" name="listing_type"
                                   id="listing_type_broker" value="broker"
                                   {{ $currentListingType == 'broker' ? 'checked' : '' }}>
                            <label class="form-check-label" for="listing_type_broker">{{ translate('broker') }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
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
            <div class="col-12">
                <div class="tab-content" id="pills-tabContent">
                    @foreach ($languages as $language)
                            <?php
                            if (count($product['translations'])) {
                                $translate = [];
                                foreach ($product['translations'] as $translation) {
                                    if ($translation->locale == $language && $translation->key == "name") {
                                        $translate[$language]['name'] = $translation->value;
                                    }
                                    if ($translation->locale == $language && $translation->key == "description") {
                                        $translate[$language]['description'] = $translation->value;
                                    }
                                }
                            }
                            ?>
                        <div class="tab-pane fade {{ $language == $defaultLanguage ? 'show active' : '' }}"
                             id="{{ $language }}-form" role="tabpanel">
                            <div class="form-group">
                                <label class="form-label" for="{{ $language }}_name">
                                    {{ translate('product_name') }}
                                    ({{ strtoupper($language) }})
                                    @if($language == $defaultLanguage)
                                        <span class="input-required-icon text-danger">*</span>
                                    @endif
                                </label>
                                <input type="text" {{ $language == $defaultLanguage ? 'required' : '' }} name="name[]"
                                       id="{{ $language }}_name"
                                       value="{{ $translate[$language]['name'] ?? $product['name'] }}"
                                       class="form-control {{ $language == $defaultLanguage ? 'product-title-default-language' : '' }}"
                                       placeholder="{{ translate('ex') }}: {{ translate('new_Product') }}">
                            </div>
                            <input type="hidden" name="lang[]" value="{{ $language }}">
                            <div class="form-group pt-2">
                                <label class="form-label" for="{{ $language }}_description">
                                    {{ translate('description') }} ({{ strtoupper($language) }})
                                    @if($language == $defaultLanguage)
                                        <span class="input-required-icon text-danger">*</span>
                                    @endif
                                </label>

                                <div id="description-{{ $language }}-editor" class="quill-editor {{ $language == $defaultLanguage ? 'product-description-default-language-editor' : '' }}">{!! $translate[$language]['description']??$product['details'] !!}</div>
                                <textarea name="description[]" id="description-{{$language}}"
                                          class="{{ $language == $defaultLanguage ? 'product-description-default-language' : '' }}"
                                          style="display:none;">{!! $translate[$language]['description']??$product['details'] !!}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

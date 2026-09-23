<div class="card mt-3 rest-part show-for-digital-product">
    <div class="card-header">
        <div class="d-flex gap-2">
            <i class="fi fi-sr-user"></i>
            <h3 class="mb-0">{{ translate('product_variation_setup') }}</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-2" id="digital-product-type-choice-section">
            <div class="col-sm-6 col-md-4 col-xxl-3">
                <div class="multi--select">
                    <label class="form-label">{{ translate('File_Type') }}</label>
                    <select class="custom-select" name="file-type" multiple
                            id="digital-product-type-select">
                        @foreach($digitalProductFileTypes as $FileType)
                            <option value="{{ $FileType }}">{{ translate($FileType) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3 rest-part" id="digital-product-variation-section"></div>

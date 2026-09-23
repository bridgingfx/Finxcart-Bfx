@foreach ($fullCategoryStructure as $headCategory)
    @foreach ($headCategory['categories'] as $main_category)
        <div class="js-slide">
            <div class="product-item mx-1 remove-divider">
                <div class="product-item__outer h-100 w-100">
                    <div class="product-item__inner bg-white px-wd-3 p-2 p-md-3 text-center">
                        <div class="product-item__body pb-xl-2">
                            <h5 class="mb-1 product-item__title">
                                <a href="{{route('products',['category_id'=> $main_category['cat_id'],'data_from'=>'category','page'=>1])}}"
                                   class="text-blue font-weight-bold"
                                   style="display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                                    {{ $main_category['category_name'] }}
                                </a>
                            </h5>
                            <div class="mb-2 cat-img-container d-flex align-items-center justify-content-center"
                                style="width: 100%; height: 100%; background: #fff; display: flex;">
                                <a href="{{route('products',['category_id'=> $main_category['cat_id'],'data_from'=>'category','page'=>1])}}" class="d-block text-center w-100 category_slider_image_section_container" style="display: block;

                                 ">
                                    <img class="img-fluid category_slider_image_section"
                                        src="{{ getStorageImages(path: ($main_category['image_full_url'] ?? $headCategory['image_full_url']), type: 'category') }}"
                                        alt="{{ $main_category['category_name'] }}"
                                        style="width: auto;
                                         height: 100px;
                                          max-width: 100%; object-fit: contain; margin: auto;">
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endforeach

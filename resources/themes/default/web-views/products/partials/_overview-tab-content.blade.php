<div class="product-tab-panel">

    <!-- Heading -->
    <div class="product-tab-heading">
        <span class="product-tab-heading-icon">
            <i class="tio-dashboard-vs-outlined"></i>
        </span>

        <div>
            <h3>{{ translate('product_overview') }}</h3>
            <p>{{ translate('everything_you_need_to_know_about_this_product') }}</p>
        </div>
    </div>


    <!-- Product Overview -->
    <div style="width:100%;">

        <!-- Overview Content -->
        <div class="product-overview-copy"
             style="width:100%;">

            <span class="product-overview-kicker">
                <i class="tio-checkmark-circle"></i>
                {{ translate('product_highlights') }}
            </span>

            @if($product->video_url != null && (str_contains($product->video_url, "youtube.com/embed/")))
                <div class="mb-4"
                     style="width:100%;">

                    <iframe
                        class="w-100"
                        height="315"
                        src="{{$product->video_url}}"
                        style="
                            display:block;
                            width:100%;
                            border:0;
                            border-radius:14px;
                        ">
                    </iframe>

                </div>
            @endif


            @if ($product['details'])
                <div class="product-tab-content-card text-body text-justify details-text-justify rich-editor-html-content"
                     style="
                        width:100%;
                        margin-bottom:20px;
                    ">
                    {!! clean_html($product['details']) !!}
                </div>
            @endif

        </div>


        <!-- SINGLE BENEFITS CARD -->
        <div class="product-overview-benefits"
             style="
                display:flex;
                align-items:stretch;
                width:100%;
                min-height:100px;
                padding:0;
                margin:20px 0 0 0;
                background:#ffffff;
                border:1px solid #e7ebf0;
                border-radius:16px;
                box-shadow:0 4px 18px rgba(20,30,50,0.06);
                overflow:hidden;
             ">


            <!-- BENEFIT 1 -->
            <div class="product-overview-benefit"
                 style="
                    flex:1;
                    min-width:0;
                    display:flex;
                    align-items:center;
                    padding:20px 24px;
                    gap:14px;
                 ">

                <div style="
                    width:48px;
                    height:48px;
                    min-width:48px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    background:#f0efff;
                    color:#6366f1;
                    font-size:19px;
                ">
                    <i class="fas fa-gem"></i>
                </div>


                <div style="
                    min-width:0;
                    flex:1;
                ">

                    <strong style="
                        display:block;
                        margin:0 0 4px 0;
                        color:#202938;
                        font-size:14px;
                        font-weight:700;
                        line-height:20px;
                    ">
                        {{ translate($product['product_type']) }} {{ translate('product') }}
                    </strong>

                    <small style="
                        display:block;
                        margin:0;
                        color:#8a94a6;
                        font-size:12px;
                        font-weight:400;
                        line-height:18px;
                    ">
                        {{ translate('clearly_listed_product_format') }}
                    </small>

                </div>

            </div>


            <!-- DIVIDER -->
            <div style="
                width:1px;
                min-width:1px;
                height:55px;
                align-self:center;
                background:#e8ecf1;
            "></div>


            <!-- BENEFIT 2 -->
            <div class="product-overview-benefit"
                 style="
                    flex:1;
                    min-width:0;
                    display:flex;
                    align-items:center;
                    padding:20px 24px;
                    gap:14px;
                 ">

                <div style="
                    width:48px;
                    height:48px;
                    min-width:48px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    background:#fff6df;
                    color:#f59e0b;
                    font-size:19px;
                ">
                    <i class="fas fa-bolt"></i>
                </div>


                <div style="
                    min-width:0;
                    flex:1;
                ">

                    <strong style="
                        display:block;
                        margin:0 0 4px 0;
                        color:#202938;
                        font-size:14px;
                        font-weight:700;
                        line-height:20px;
                    ">
                        {{ $product['product_type'] === 'digital' ? translate('instant_access') : translate('ready_to_order') }}
                    </strong>

                    <small style="
                        display:block;
                        margin:0;
                        color:#8a94a6;
                        font-size:12px;
                        font-weight:400;
                        line-height:18px;
                    ">
                        {{ translate('simple_and_fast_purchase_experience') }}
                    </small>

                </div>

            </div>


            <!-- DIVIDER -->
            <div style="
                width:1px;
                min-width:1px;
                height:55px;
                align-self:center;
                background:#e8ecf1;
            "></div>


            <!-- BENEFIT 3 -->
            <div class="product-overview-benefit"
                 style="
                    flex:1;
                    min-width:0;
                    display:flex;
                    align-items:center;
                    padding:20px 24px;
                    gap:14px;
                 ">

                <div style="
                    width:48px;
                    height:48px;
                    min-width:48px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    background:#e9f9f2;
                    color:#10b981;
                    font-size:19px;
                ">
                    <i class="fas fa-shield-alt"></i>
                </div>


                <div style="
                    min-width:0;
                    flex:1;
                ">

                    <strong style="
                        display:block;
                        margin:0 0 4px 0;
                        color:#202938;
                        font-size:14px;
                        font-weight:700;
                        line-height:20px;
                    ">
                        {{ translate('secure_purchase') }}
                    </strong>

                    <small style="
                        display:block;
                        margin:0;
                        color:#8a94a6;
                        font-size:12px;
                        font-weight:400;
                        line-height:18px;
                    ">
                        {{ translate('protected_checkout_and_order_tracking') }}
                    </small>

                </div>

            </div>

        </div>

    </div>


    <!-- No Details -->
    @if (!$product['details'] && ($product->video_url == null || !(str_contains($product->video_url, "youtube.com/embed/"))))

        <div>
            <div class="text-center text-capitalize py-5">

                <img
                    class="mw-90"
                    src="{{theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg')}}"
                    alt="">

                <p class="text-capitalize mt-2">
                    <small>
                        {{translate('product_details_not_found')}}!
                    </small>
                </p>

            </div>
        </div>

    @endif

</div>

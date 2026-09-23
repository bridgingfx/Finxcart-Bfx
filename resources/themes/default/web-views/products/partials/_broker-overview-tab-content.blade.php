<div class="event-about-panel"
     style="
        width:100%;
        padding:30px;
        background:linear-gradient(135deg,#f7f9ff 0%,#ffffff 55%,#f4f7ff 100%);
        border:1px solid #e6eaf4;
        border-radius:18px;
        box-shadow:0 8px 30px rgba(31,41,81,0.06);
        box-sizing:border-box;
     ">

    <!-- OVERVIEW -->
    <div class="product-overview-layout"
         style="
            display:block;
            width:100%;
         ">

        <div class="product-overview-copy"
             style="
                width:100%;
                max-width:900px;
                margin:0 auto;
                min-width:0;
             ">

            <!-- Heading -->
            <div style="
                display:flex;
                align-items:center;
                gap:12px;
                margin-bottom:20px;
             ">

                <div style="
                    width:42px;
                    height:42px;
                    min-width:42px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    border-radius:12px;
                    background:linear-gradient(135deg,#4f46e5,#7c3aed);
                    color:#ffffff !important;
                    box-shadow:0 6px 15px rgba(79,70,229,0.25);
                 ">
                    <i class="tio-document-text"
                       style="font-size:20px;color:#ffffff !important;"></i>
                </div>

                <div>
                    <h3 class="event-about-heading"
                        style="
                            margin:0 !important;
                            padding:0 !important;
                            font-size:21px !important;
                            font-weight:700 !important;
                            line-height:1.3 !important;
                            color:#172033 !important;
                         ">
                        {{ translate('broker_overview') }}
                    </h3>

                    <div style="
                        width:45px;
                        height:3px;
                        margin-top:6px;
                        border-radius:10px;
                        background:linear-gradient(90deg,#4f46e5,#8b5cf6);
                     "></div>
                </div>

            </div>


            <!-- VIDEO -->
            @if($product->video_url != null && (str_contains($product->video_url, "youtube.com/embed/")))

                <div style="
                    width:100%;
                    max-width:900px;
                    margin:0 0 24px 0;
                    padding:7px;
                    background:#ffffff;
                    border:1px solid #e3e7f1;
                    border-radius:14px;
                    box-shadow:0 8px 22px rgba(30,41,80,0.08);
                    box-sizing:border-box;
                 ">

                    <iframe
                        src="{{$product->video_url}}"
                        style="
                            display:block;
                            width:100%;
                            height:380px;
                            border:0;
                            border-radius:9px;
                         "
                        allowfullscreen>
                    </iframe>

                </div>

            @endif


            <!-- DESCRIPTION -->
            @if ($product['details'])

                <div class="event-about-text rich-editor-html-content mb-3"
                     style="
                        width:100%;
                        max-width:900px;
                        padding:20px 22px;
                        background:#ffffff !important;
                        border-left:4px solid #6366f1;
                        border-radius:0 12px 12px 0;
                        box-shadow:0 4px 15px rgba(31,41,81,0.05);
                        color:#4b5563 !important;
                        font-size:14px !important;
                        line-height:1.75 !important;
                        overflow-wrap:break-word;
                        word-break:normal;
                        box-sizing:border-box;
                     ">

                    {!! clean_html($product['details']) !!}

                </div>

            @else

                <div style="
                    width:100%;
                    max-width:900px;
                    padding:18px 20px;
                    background:#ffffff;
                    border-left:4px solid #6366f1;
                    border-radius:0 12px 12px 0;
                    color:#4b5563 !important;
                    font-size:14px;
                    line-height:1.7;
                    box-shadow:0 4px 15px rgba(31,41,81,0.05);
                    box-sizing:border-box;
                 ">

                    {{ translate('broker_page_tagline') }}

                </div>

            @endif


            <!-- FEATURES -->
            <div style="
                width:100%;
                max-width:900px;
                margin-top:24px;
             ">

                <div style="
                    margin-bottom:12px;
                    font-size:14px;
                    font-weight:700;
                    color:#172033 !important;
                 ">
                    {{ translate('key_facts') }}
                </div>

                <div style="
                    display:grid;
                    grid-template-columns:repeat(2,minmax(0,1fr));
                    gap:10px;
                 ">

                    <!-- Feature 1 -->
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        min-width:0;
                        padding:13px 15px;
                        background:#ffffff;
                        border:1px solid #e5e9f3;
                        border-radius:11px;
                        box-shadow:0 3px 10px rgba(30,41,80,0.035);
                        box-sizing:border-box;
                     ">

                        <span style="
                            width:28px;
                            height:28px;
                            min-width:28px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:50%;
                            background:#ecfdf5;
                         ">
                            <i class="tio-checkmark-circle"
                               style="
                                  color:#10b981 !important;
                                  font-size:16px;
                               "></i>
                        </span>

                        <span style="
                            color:#374151 !important;
                            font-size:13px !important;
                            line-height:1.45 !important;
                            font-weight:500;
                         ">
                            {{ translate('trading_platforms_and_technology') }}
                        </span>

                    </div>


                    <!-- Feature 2 -->
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        min-width:0;
                        padding:13px 15px;
                        background:#ffffff;
                        border:1px solid #e5e9f3;
                        border-radius:11px;
                        box-shadow:0 3px 10px rgba(30,41,80,0.035);
                        box-sizing:border-box;
                     ">

                        <span style="
                            width:28px;
                            height:28px;
                            min-width:28px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:50%;
                            background:#eff6ff;
                         ">
                            <i class="tio-checkmark-circle"
                               style="
                                  color:#2563eb !important;
                                  font-size:16px;
                               "></i>
                        </span>

                        <span style="
                            color:#374151 !important;
                            font-size:13px !important;
                            line-height:1.45 !important;
                            font-weight:500;
                         ">
                            {{ translate('account_types_and_trading_conditions') }}
                        </span>

                    </div>


                    <!-- Feature 3 -->
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        min-width:0;
                        padding:13px 15px;
                        background:#ffffff;
                        border:1px solid #e5e9f3;
                        border-radius:11px;
                        box-shadow:0 3px 10px rgba(30,41,80,0.035);
                        box-sizing:border-box;
                     ">

                        <span style="
                            width:28px;
                            height:28px;
                            min-width:28px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:50%;
                            background:#f5f3ff;
                         ">
                            <i class="tio-checkmark-circle"
                               style="
                                  color:#7c3aed !important;
                                  font-size:16px;
                               "></i>
                        </span>

                        <span style="
                            color:#374151 !important;
                            font-size:13px !important;
                            line-height:1.45 !important;
                            font-weight:500;
                         ">
                            {{ translate('supported_instruments') }}
                        </span>

                    </div>


                    <!-- Feature 4 -->
                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                        min-width:0;
                        padding:13px 15px;
                        background:#ffffff;
                        border:1px solid #e5e9f3;
                        border-radius:11px;
                        box-shadow:0 3px 10px rgba(30,41,80,0.035);
                        box-sizing:border-box;
                     ">

                        <span style="
                            width:28px;
                            height:28px;
                            min-width:28px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:50%;
                            background:#ecfeff;
                         ">
                            <i class="tio-checkmark-circle"
                               style="
                                  color:#0891b2 !important;
                                  font-size:16px;
                               "></i>
                        </span>

                        <span style="
                            color:#374151 !important;
                            font-size:13px !important;
                            line-height:1.45 !important;
                            font-weight:500;
                         ">
                            {{ translate('customer_support_and_languages') }}
                        </span>

                    </div>

                </div>

            </div>

        </div>


        @php($brokerCategoryName = $product['subCategory']->name ?? $product['category']->name ?? null)

        @if($brokerCategoryName || $product->external_url || $product->contact_broker_url)

            <!-- KEY FACTS - FULL WIDTH ROW -->
            <div class="broker-facts-card"
                 style="
                    width:100%;
                    margin-top:28px;
                    padding:0;
                    background:#ffffff;
                    border:1px solid #e1e6f0;
                    border-radius:16px;
                    box-shadow:0 8px 25px rgba(31,41,81,0.07);
                    overflow:hidden;
                    box-sizing:border-box;
                 ">

                <!-- Header -->
                <div style="
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    padding:16px 20px;
                    background:linear-gradient(135deg,#1e293b,#334155);
                    color:#ffffff !important;
                 ">

                    <div style="
                        display:flex;
                        align-items:center;
                        gap:10px;
                     ">

                        <div style="
                            width:36px;
                            height:36px;
                            min-width:36px;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:10px;
                            background:rgba(255,255,255,0.12);
                            border:1px solid rgba(255,255,255,0.12);
                         ">

                            <i class="tio-info-outined"
                               style="
                                  color:#ffffff !important;
                                  font-size:18px;
                               "></i>

                        </div>

                        <div>
                            <div style="
                                color:#ffffff !important;
                                font-size:16px;
                                font-weight:700;
                                line-height:1.3;
                             ">
                                {{ translate('key_facts') }}
                            </div>

                            <div style="
                                color:#cbd5e1 !important;
                                font-size:11px;
                                margin-top:2px;
                             ">
                                Broker information
                            </div>
                        </div>

                    </div>

                </div>


                <!-- FACTS ROW -->
                <div style="
                    display:flex;
                    align-items:stretch;
                    width:100%;
                    padding:18px;
                    gap:0;
                    box-sizing:border-box;
                 ">

                    @if($brokerCategoryName)

                        <div style="
                            flex:1;
                            min-width:0;
                            padding:5px 22px;
                            border-right:1px solid #e5e7eb;
                            box-sizing:border-box;
                         ">

                            <div style="
                                display:flex;
                                align-items:center;
                                gap:9px;
                                margin-bottom:7px;
                             ">

                                <span style="
                                    width:30px;
                                    height:30px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    border-radius:8px;
                                    background:#eef2ff;
                                    color:#4f46e5 !important;
                                 ">
                                    <i class="tio-category"
                                       style="
                                          font-size:15px;
                                          color:#4f46e5 !important;
                                       "></i>
                                </span>

                                <span style="
                                    color:#64748b !important;
                                    font-size:11px;
                                    font-weight:600;
                                    text-transform:uppercase;
                                    letter-spacing:.3px;
                                 ">
                                    {{ translate('broker_type') }}
                                </span>

                            </div>

                            <div style="
                                color:#172033 !important;
                                font-size:14px;
                                font-weight:700;
                                line-height:1.5;
                                overflow-wrap:anywhere;
                             ">
                                {{ $brokerCategoryName }}
                            </div>

                        </div>

                    @endif


                    @if($product->external_url)

                        <div style="
                            flex:1;
                            min-width:0;
                            padding:5px 22px;
                            border-right:1px solid #e5e7eb;
                            box-sizing:border-box;
                         ">

                            <div style="
                                display:flex;
                                align-items:center;
                                gap:9px;
                                margin-bottom:7px;
                             ">

                                <span style="
                                    width:30px;
                                    height:30px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    border-radius:8px;
                                    background:#ecfdf5;
                                 ">
                                    <i class="tio-globe"
                                       style="
                                          font-size:15px;
                                          color:#059669 !important;
                                       "></i>
                                </span>

                                <span style="
                                    color:#64748b !important;
                                    font-size:11px;
                                    font-weight:600;
                                    text-transform:uppercase;
                                    letter-spacing:.3px;
                                 ">
                                    {{ translate('website') }}
                                </span>

                            </div>

                            <span style="
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                                padding:5px 10px;
                                border-radius:20px;
                                background:#ecfdf5;
                                color:#059669 !important;
                                font-size:11px;
                                font-weight:700;
                             ">
                                ✓ {{ translate('available') }}
                            </span>

                        </div>

                    @endif


                    @if($product->contact_broker_url)

                        <div style="
                            flex:1;
                            min-width:0;
                            padding:5px 22px;
                            box-sizing:border-box;
                         ">

                            <div style="
                                display:flex;
                                align-items:center;
                                gap:9px;
                                margin-bottom:7px;
                             ">

                                <span style="
                                    width:30px;
                                    height:30px;
                                    display:flex;
                                    align-items:center;
                                    justify-content:center;
                                    border-radius:8px;
                                    background:#eff6ff;
                                 ">
                                    <i class="tio-call"
                                       style="
                                          font-size:15px;
                                          color:#2563eb !important;
                                       "></i>
                                </span>

                                <span style="
                                    color:#64748b !important;
                                    font-size:11px;
                                    font-weight:600;
                                    text-transform:uppercase;
                                    letter-spacing:.3px;
                                 ">
                                    {{ translate('support') }}
                                </span>

                            </div>

                            <span style="
                                display:inline-flex;
                                align-items:center;
                                gap:5px;
                                padding:5px 10px;
                                border-radius:20px;
                                background:#eff6ff;
                                color:#2563eb !important;
                                font-size:11px;
                                font-weight:700;
                             ">
                                ✓ {{ translate('available') }}
                            </span>

                        </div>

                    @endif

                </div>

                <!-- Bottom Gradient -->
                <div style="
                    width:100%;
                    height:3px;
                    background:linear-gradient(90deg,#4f46e5,#7c3aed,#06b6d4);
                 "></div>

            </div>

        @endif

    </div>

</div>


@extends('layouts.front-end.app')

@section('title', translate('order_Details'))

@section('content')

    <div class="container pb-5 mb-2 mb-md-4 mt-3 rtl __inline-47 text-align-direction">
        <div class="row g-3">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9">
                @include('web-views.users-profile.account-details.partial',['order'=>$orderDetails])
                <div class="card border-0">
                    <div class="card-body">
                        <div>
                            @if($orderDetails->order_type == 'default_type' && getWebConfig(name: 'order_verification'))
                            <div class="d-flex gap-3 flex-wrap mb-4">
                                <div class="bg-light rounded py-2 px-3 d-flex align-items-center">
                                    <div class="fs-14">
                                        {{translate('order_verification_code') }} :
                                        <strong class="text-base">
                                            {{$orderDetails['verification_code']}}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($orderDetails->order_type == 'POS')
                                <div class="mb-5">
                                    <span class="pos-btn hover-none">{{translate('POS_Order')}}</span>
                                </div>
                            @endif
                        </div>

                        @if($orderDetails->children->count() > 0)
                            @foreach($orderDetails->children as $child)
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fs-14 font-bold">{{ translate('Shipment ID') }}: {{ $orderDetails->id }}-{{ $child->child_number }}</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge-soft-{{ in_array($child->order_status, ['confirmed','delivered','processing','processed','out_for_delivery']) ? 'success' : (in_array($child->order_status, ['canceled','failed','returned']) ? 'danger' : 'primary') }} text-capitalize fs-12">{{ translate($child->order_status) }}</span>
                                        <a href="{{ route('track-order.order-wise-result-view', ['order_id' => $child->id]) }}" class="btn btn--primary btn-sm fs-12">{{ translate('track') }}</a>
                                    </div>
                                </div>
                                @if($child->seller?->shop?->name)
                                <div class="fs-13 font-semi-bold text-dark mb-2">{{ $child->seller->shop->name }}</div>
                                @endif
                                @foreach($child->details as $detail)
                                    @php($p = json_decode($detail->product_details))
                                    <div class="fs-12 text-dark mb-1">• {{ $p->name ?? '' }} &times; {{ $detail->qty }} &mdash; {{ webCurrencyConverter($detail->price * $detail->qty) }}</div>
                                @endforeach
                                <div class="d-flex flex-wrap gap-3 mt-2 fs-12 text-secondary border-top pt-2">
                                    <span>{{ translate('Subtotal') }}: {{ webCurrencyConverter($child->details->sum(fn($d) => $d->price * $d->qty)) }}</span>
                                    <span>{{ translate('Shipping') }}: {{ $child->is_shipping_free ? translate('Free') : webCurrencyConverter($child->shipping_cost) }}</span>
                                    @if($child->delivery_type == 'third_party_delivery' && $child->delivery_service_name)
                                        <span>{{ $child->delivery_service_name }}{{ $child->third_party_delivery_tracking_id ? ' · '.$child->third_party_delivery_tracking_id : '' }}</span>
                                    @elseif($child->delivery_type == 'self_delivery' && $child->deliveryMan)
                                        <span>{{ translate('Delivery Man') }}: {{ $child->deliveryMan->f_name }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                        @if($orderDetails->parent_order_id)
                        <div class="d-flex align-items-center gap-2 mb-3 p-2 border rounded bg-light fs-13">
                            <span class="text-muted">{{ translate('Order ID') }}: <strong class="text-dark">#{{ $orderDetails->parent_order_id }}</strong></span>
                            <span class="text-muted">|</span>
                            <span class="text-muted">{{ translate('Shipment ID') }}: <strong class="text-dark">#{{ $orderDetails->parent_order_id }}-{{ $orderDetails->child_number }}</strong></span>
                        </div>
                        @endif
                        <ul class="nav nav-tabs media-tabs nav-justified order-track-info">

                            <li class="nav-item">
                                <div class="nav-link active-status">
                                    <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                        <div class="media-tab-media mx-sm-auto mb-3">
                                            <img
                                                src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-placed.png') }}"
                                                alt="">
                                        </div>
                                        <div class="media-body">
                                            <div class="text-sm-center">
                                                <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('order_placed') }}</h6>
                                            </div>
                                            <div
                                                class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                                <img
                                                    src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                    width="14" alt="">
                                                <span
                                                    class="text-muted fs-12">{{date('h:i A, d M Y',strtotime($orderDetails->created_at))}}</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </li>

                            @if ($orderDetails['order_status']!='returned' && $orderDetails['order_status']!='failed' && $orderDetails['order_status']!='canceled')
                                @if(!$isOrderOnlyDigital)
                                    <li class="nav-item ">
                                        <div
                                            class="nav-link {{($orderDetails['order_status']=='confirmed') || ($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-confirmed.png') }}"
                                                        alt="">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('order_confirmed') }}</h6>
                                                    </div>
                                                    @if(in_array($orderDetails['order_status'],['confirmed','processing','processed','out_for_delivery','delivered']) && \App\Utils\order_status_history($orderDetails['id'],'confirmed'))
                                                        <div
                                                            class="d-flex align-items-center justify-content-sm-center mt-2 gap-1">
                                                            <img width="14" alt=""
                                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}">
                                                            <span class="text-muted fs-12">
                                                                {{date('h:i A, d M Y',strtotime(\App\Utils\order_status_history($orderDetails['id'],'confirmed')))}}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <div
                                            class="nav-link {{($orderDetails['order_status']=='processing') || ($orderDetails['order_status']=='processed') || ($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img alt=""
                                                         src="{{theme_asset(path: 'public/assets/front-end/img/track-order/shipment.png') }}">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                            {{ translate('preparing_shipment') }}
                                                        </h6>
                                                    </div>
                                                    @if(in_array($orderDetails['order_status'],['processing','processed','out_for_delivery','delivered']) && \App\Utils\order_status_history($orderDetails['id'],'processing'))
                                                        <div
                                                            class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img width="14" alt=""
                                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}">
                                                            <span class="text-muted fs-12">
                                                                {{date('h:i A, d M Y',strtotime(\App\Utils\order_status_history($orderDetails['id'],'processing')))}}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <div
                                            class="nav-link {{($orderDetails['order_status']=='out_for_delivery') || ($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/track-order/on-the-way.png') }}"
                                                        alt="">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('out_for_delivery') }}</h6>
                                                    </div>
                                                    @if(in_array($orderDetails['order_status'],['out_for_delivery','delivered']) && \App\Utils\order_status_history($orderDetails['id'],'out_for_delivery'))
                                                        <div
                                                            class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img class="mx-1"
                                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                                 width="14" alt="">
                                                            <span class="text-muted fs-12">
                                                                {{date('h:i A, d M Y',strtotime(\App\Utils\order_status_history($orderDetails['id'],'out_for_delivery')))}}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    <li class="nav-item">
                                        <div class="nav-link {{($orderDetails['order_status']=='delivered')?'active-status' : ''}}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/track-order/delivered.png') }}"
                                                        alt="">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('delivered') }}</h6>
                                                    </div>
                                                    @if(($orderDetails['order_status']=='delivered') && \App\Utils\order_status_history($orderDetails['id'],'delivered'))
                                                        <div
                                                            class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img
                                                                src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                                width="14" alt="">
                                                            <span class="text-muted fs-12">
                                                            {{date('h:i A, d M Y',strtotime(\App\Utils\order_status_history($orderDetails['id'],'delivered')))}}
                                                        </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @else

                                    <?php
                                        $digitalProductProcessComplete = true;
                                        $digitalFileSentAt = null;
                                        $digitalFileAccessedAt = null;
                                        foreach ($orderDetails->orderDetails as $detail) {
                                            $productData = json_decode($detail->product_details);
                                            if ($productData->product_type == 'digital' && $productData->digital_product_type == 'ready_after_sell' && $detail->digital_file_after_sell == null) {
                                                $digitalProductProcessComplete = false;
                                            }
                                            if (!$digitalFileSentAt && !empty($detail->digital_file_sent_at)) {
                                                $digitalFileSentAt = $detail->digital_file_sent_at;
                                            }
                                            if (!$digitalFileAccessedAt && !empty($detail->digital_file_first_accessed_at)) {
                                                $digitalFileAccessedAt = $detail->digital_file_first_accessed_at;
                                            }
                                        }
                                        $digitalPaymentConfirmed = in_array($orderDetails['order_status'], ['confirmed','processing','processed','out_for_delivery','delivered']);
                                    ?>

                                    {{-- Stage 2: Payment Confirmed --}}
                                    <li class="nav-item">
                                        <div class="nav-link {{ $digitalPaymentConfirmed ? 'active-status' : '' }}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img alt=""
                                                         src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-confirmed.png') }}">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                            {{ translate('payment_confirmed') }}
                                                        </h6>
                                                    </div>
                                                    @if($digitalPaymentConfirmed && \App\Utils\order_status_history($orderDetails['id'], 'confirmed'))
                                                        <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img width="14" alt=""
                                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}">
                                                            <span class="text-muted fs-12">
                                                                {{ date('h:i A, d M Y', strtotime(\App\Utils\order_status_history($orderDetails['id'], 'confirmed'))) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    {{-- Stage 3: File Generated --}}
                                    <li class="nav-item">
                                        <div class="nav-link {{ $digitalProductProcessComplete ? 'active-status' : '' }}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img alt=""
                                                         src="{{theme_asset(path: 'public/assets/front-end/img/track-order/shipment.png') }}">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                            {{ translate('license_access_generated') }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    {{-- Stage 4: Email Sent --}}
                                    <li class="nav-item">
                                        <div class="nav-link {{ $digitalFileSentAt ? 'active-status' : '' }}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img alt=""
                                                         src="{{theme_asset(path: 'public/assets/front-end/img/track-order/on-the-way.png') }}">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                            {{ translate('email_sent') }}
                                                        </h6>
                                                    </div>
                                                    @if($digitalFileSentAt)
                                                        <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img width="14" alt=""
                                                                 src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}">
                                                            <span class="text-muted fs-12">
                                                                {{ date('h:i A, d M Y', strtotime($digitalFileSentAt)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>

                                    {{-- Stage 5: Accessed / Downloaded --}}
                                    <li class="nav-item">
                                        <div class="nav-link {{ $digitalFileAccessedAt ? 'active-status' : '' }}">
                                            <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                                <div class="media-tab-media mb-3 mx-sm-auto">
                                                    <img
                                                        src="{{theme_asset(path: 'public/assets/front-end/img/track-order/delivered.png') }}"
                                                        alt="">
                                                </div>
                                                <div class="media-body">
                                                    <div class="text-sm-center">
                                                        <h6 class="media-tab-title text-nowrap mb-0 fs-14">{{ translate('accessed_downloaded') }}</h6>
                                                    </div>
                                                    @if($digitalFileAccessedAt)
                                                        <div class="d-flex align-items-center justify-content-sm-center mt-2 gap-2">
                                                            <img
                                                                src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                                width="14" alt="">
                                                            <span class="text-muted fs-12">
                                                                {{ date('h:i A, d M Y', strtotime($digitalFileAccessedAt)) }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endif
                            @elseif(in_array($orderDetails['order_status'], ['returned', 'canceled']))
                                <li class="nav-item">
                                    <div class="nav-link active-status">
                                        <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                            <div class="media-tab-media mx-sm-auto mb-3">
                                                <img src="{{ theme_asset(path: 'public/assets/front-end/img/track-order/'.$orderDetails['order_status'].'.png') }}" alt="">
                                            </div>
                                            <div class="media-body">
                                                <div class="text-sm-center">
                                                    <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">
                                                        {{ translate('order') }} {{ translate($orderDetails['order_status']) }}
                                                    </h6>
                                                </div>
                                                @if(\App\Utils\order_status_history($orderDetails['id'], $orderDetails['order_status']))
                                                    <div class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/track-order/clock.png') }}"
                                                             width="14" alt="">
                                                        <span class="text-muted fs-12">
                                                        {{ date('h:i A, d M Y', strtotime(\App\Utils\order_status_history($orderDetails['id'], $orderDetails['order_status']))) }}
                                                    </span>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @else
                                <li class="nav-item">
                                    <div class="nav-link active-status">
                                        <div class="d-flex flex-sm-column gap-3 gap-sm-0">
                                            <div class="media-tab-media mx-sm-auto mb-3">
                                                <img
                                                    src="{{theme_asset(path: 'public/assets/front-end/img/track-order/order-failed.png') }}"
                                                    alt="">
                                            </div>
                                            <div class="media-body">
                                                <div class="text-sm-center">
                                                    <h6 class="media-tab-title text-nowrap mb-0 text-capitalize fs-14">{{ translate('Failed_to_Deliver') }}</h6>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-sm-center gap-1 mt-2">
                                                    <span class="text-muted fs-12">
                                                        {{ translate('sorry_we_can_not_complete_your_order') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </li>
                            @endif
                        </ul>
                        @endif

                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@extends('layouts.front-end.app')

@section('title', translate('my_Order_List'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/fx-commerce.css') }}?v=20260929">
@endpush

@section('content')
    <div class="container fx fx-page">
        <div class="fx-account-layout">
            @include('web-views.partials._profile-aside')

            <section class="fx-account-main">
                <div class="fx-card">
                    <div class="fx-card__body">
                        <h1 class="fx-page-title">{{ translate('my_Order') }}</h1>

                        <div class="fx-order-tabs" role="tablist">
                            <a href="{{ request()->fullUrlWithQuery(['show_order' => 'ongoing']) }}"
                               class="fx-order-tab {{ $show_order == 'ongoing' ? 'is-active' : '' }}">
                                {{ translate('in_progress') }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['show_order' => 'delivered']) }}"
                               class="fx-order-tab {{ $show_order == 'delivered' ? 'is-active' : '' }}">
                                {{ translate('purchased') }}
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['show_order' => 'canceled']) }}"
                               class="fx-order-tab {{ $show_order == 'canceled' ? 'is-active' : '' }}">
                                {{ translate('cancelled') }}
                            </a>
                        </div>

                        @if($orders->count() > 0)
                            <div class="fx-order-list">
                                @foreach($orders as $order)
                                    @php
                                        $isAllDigital = $order->details->isNotEmpty() && $order->details->every(fn($d) => \App\Enums\TransactionType::forProduct($d->product) === \App\Enums\TransactionType::Download);
                                        $statusClass = match(true) {
                                            $order['order_status'] == 'failed' || $order['order_status'] == 'canceled' => 'fx-status--danger',
                                            $order['order_status'] == 'confirmed' || $order['order_status'] == 'processing' || $order['order_status'] == 'delivered' => 'fx-status--success',
                                            default => 'fx-status--info',
                                        };
                                        $statusLabel = match(true) {
                                            $order['order_status'] == 'failed' => translate('failed_to_deliver'),
                                            $order['order_status'] == 'processing' => translate('packaging'),
                                            default => translate($order['order_status']),
                                        };
                                    @endphp
                                    <article class="fx-order-card">
                                        <a href="{{ route('account-order-details', ['id' => $order->id]) }}" class="fx-order-card__shop" aria-hidden="true" tabindex="-1">
                                            @if($order->seller_is == 'seller')
                                                <img alt="{{ translate('shop') }}" src="{{ getStorageImages(path: $order?->seller?->shop?->image_full_url, type: 'shop') }}">
                                            @elseif($order->seller_is == 'admin')
                                                <img alt="{{ translate('shop') }}" src="{{ getStorageImages(path: $web_config['fav_icon'], type: 'shop') }}">
                                            @endif
                                        </a>
                                        <div class="fx-order-card__info">
                                            <h2 class="fx-order-card__id">
                                                <a href="{{ route('account-order-details', ['id' => $order->id]) }}">{{ translate('order') }} #{{ $order['id'] }}</a>
                                            </h2>
                                            <div class="fx-order-card__meta">
                                                <span>{{ $order->children->count() > 0 ? $order->children->sum('order_details_sum_qty') : $order->order_details_sum_qty }} {{ translate('items') }}</span>
                                                @if($order->children->count() > 1)
                                                    <span class="fx-status fx-status--info">{{ $order->children->count() }} {{ translate('shipments') }}</span>
                                                @endif
                                                <span>{{ date('d M, Y h:i A', strtotime($order['created_at'])) }}</span>
                                            </div>
                                            <div class="fx-order-card__total">{{ webCurrencyConverter(amount: $order->order_amount) }}</div>
                                        </div>
                                        <div class="fx-order-card__side">
                                            <span class="fx-status {{ $statusClass }}">{{ $statusLabel }}</span>
                                            <div class="fx-order-card__actions">
                                                <a href="{{ route('account-order-details', ['id' => $order->id]) }}" class="fx-icon-btn" title="{{ translate('view_order_details') }}" aria-label="{{ translate('view_order_details') }}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('generate-invoice', [$order->id]) }}" class="fx-icon-btn" title="{{ translate('download_invoice') }}" aria-label="{{ translate('download_invoice') }}">
                                                    <i class="tio-download-to"></i>
                                                </a>
                                                @if($show_order == 'ongoing' && !$isAllDigital)
                                                    <a href="{{ route('track-order.order-wise-result-view', ['order_id' => $order->id]) }}" class="fx-icon-btn" title="{{ translate('track_order') }}" aria-label="{{ translate('track_order') }}">
                                                        <i class="tio-map-outlined"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                            <div class="fx-pagination">
                                {{ $orders->links() }}
                            </div>
                        @else
                            <div class="fx-empty" role="status">
                                <img src="{{ theme_asset(path: 'public/assets/front-end/img/empty-icons/empty-orders.svg') }}" alt="" width="100" height="100">
                                <h2>{{ translate('You_have_not_any_order_yet') }}!</h2>
                                <a href="{{ route('home') }}" class="fx-btn fx-btn-lg">{{ translate('continue_shopping') }}</a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

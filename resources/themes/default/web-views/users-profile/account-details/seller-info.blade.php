@extends('layouts.front-end.app')

@section('title', translate('order_Details'))

@section('content')

    <div class="container pb-5 mb-2 mb-md-4 mt-3 rtl __inline-47 text-start">
        <div class="row g-3">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9">
                {{-- {{ dd($order) }} --}}
                @include('web-views.users-profile.account-details.partial')
                @foreach($vendors as $vendorData)
                    @php($sellerOrder = $vendorData['order'])
                    @php($avg_rating = $vendorData['avg_rating'])
                    @php($rating_count = $vendorData['rating_count'])
                    <div class="card mt-3">
                        <div class="card-body">
                            @if($sellerOrder->seller_is =='seller')
                                <div class="media flex-wrap gap-2 gap-sm-3 border rounded p-3">
                                    <div class="aspect-1 d-flex justify-content-center align-items-center overflow-hidden rounded border">
                                        <img width="77"  alt=""
                                             src="{{ getStorageImages(path: $sellerOrder?->seller?->shop->image_full_url, type: 'shop') }}">
                                    </div>
                                    <div class="media-body">
                                        <div class="d-flex gap-2 gap-sm-3 align-items-sm-center justify-content-between">
                                            <div class="">
                                                <h6 class="text-capitalize seller-info-title mb-1 mb-sm-2">{{$sellerOrder->seller->shop->name}}</h6>
                                                <div class="rating-show justify-content-between">
                                                    <span class="d-inline-block font-size-sm text-body">
                                                        @for($inc=1;$inc<=5;$inc++)
                                                            @if ($inc <= (int)$avg_rating)
                                                                <i class="tio-star text-warning"></i>
                                                            @elseif ($avg_rating != 0 && $inc <= (int)$avg_rating + 1.1 && $avg_rating > ((int)$avg_rating))
                                                                <i class="tio-star-half text-warning"></i>
                                                            @else
                                                                <i class="tio-star-outlined text-warning"></i>
                                                            @endif
                                                        @endfor
                                                        <label class="badge-style">
                                                            ( {{number_format($avg_rating,1)}} )
                                                        </label>
                                                    </span>
                                                </div>
                                                <ul class="list-unstyled list-inline-dot fs-12 mb-0">
                                                    <li class="mb-0">{{ $rating_count }} {{('reviews')}} </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="media flex-wrap gap-3 border rounded p-3">
                                    <div class="aspect-1 d-flex justify-content-center align-items-center overflow-hidden rounded border">
                                        <img width="77"  alt=""
                                             src="{{ getStorageImages(path: $web_config['fav_icon'], type: 'shop') }}">
                                    </div>
                                    <div class="media-body">
                                        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                            <div>
                                                <h6 class="text-capitalize">{{$web_config['company_name']}}</h6>
                                                <div class="rating-show justify-content-between">
                                                    <span class="d-inline-block font-size-sm text-body">
                                                        @for($inc=1;$inc<=5;$inc++)
                                                            @if ($inc <= (int)$avg_rating)
                                                                <i class="tio-star text-warning"></i>
                                                            @elseif ($avg_rating != 0 && $inc <= (int)$avg_rating + 1.1 && $avg_rating > ((int)$avg_rating))
                                                                <i class="tio-star-half text-warning"></i>
                                                            @else
                                                                <i class="tio-star-outlined text-warning"></i>
                                                            @endif
                                                        @endfor
                                                        <label class="badge-style">
                                                            ( {{ number_format($avg_rating,1) }} )
                                                        </label>
                                                    </span>
                                                </div>
                                                <ul class="list-unstyled list-inline-dot fs-12 mb-0">
                                                    <li class="mb-0">{{$rating_count}} {{('reviews')}} </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>

    {{-- @include('layouts.front-end.partials.modal._chatting',['seller'=>$order->seller, 'user_type'=>$order->seller_is]) --}}
@endsection

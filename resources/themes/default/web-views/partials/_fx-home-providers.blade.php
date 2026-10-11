{{-- FinXCart redesign: featured providers — logo + name only, no verification claims.
     Expects $fxProviders from HomeController::default_theme(); never queries here. --}}
@php
    $providers = isset($fxProviders) ? collect($fxProviders) : collect();
@endphp
@if($providers->isNotEmpty())
    <section class="fx-home__section fx-reveal" aria-labelledby="fx-home-providers">
        <div class="fx-container">
            <div class="fx-home__section-head">
                <div>
                    <h2 class="fx-home__section-title" id="fx-home-providers">{{ translate('Featured providers') }}</h2>
                    <p class="fx-home__section-sub">{{ translate('Vendors currently listing on the marketplace.') }}</p>
                </div>
                <a class="fx-home__view-all" href="{{ route('vendors') }}">{{ translate('View all') }}</a>
            </div>
            <div class="fx-home__providers">
                @foreach($providers as $provider)
                    @php
                        $shop = $provider->shop;
                        $shopName = $shop?->name ?? trim($provider->f_name.' '.$provider->l_name);
                        $initials = mb_strtoupper(mb_substr($shopName, 0, 1));
                        $shopUrl = $shop ? route('shopView', ['id' => $shop->id]) : route('vendors');
                    @endphp
                    <a class="fx-card fx-home__provider" href="{{ $shopUrl }}">
                        <span class="fx-home__provider-logo">
                            @if($shop?->image)
                                <img src="{{ getStorageImages(path: $shop->image_full_url, type: 'avatar') }}" alt="" loading="lazy">
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <span>
                            <span class="fx-home__provider-name">{{ $shopName }}</span>
                            <p class="fx-home__provider-sub">{{ translate('Marketplace seller') }}</p>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

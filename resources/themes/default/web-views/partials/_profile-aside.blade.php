<aside class="fx-account-aside" aria-label="{{ translate('my_account') }}">
    <div class="fx-account-aside__card">
        <div class="fx-account-aside__user">
            <div class="fx-account-aside__avatar">
                {{ strtoupper(substr(auth('customer')->user()->f_name ?? auth('customer')->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="fx-account-aside__name">{{ auth('customer')->user()->f_name }} {{ auth('customer')->user()->l_name }}</div>
            <div class="fx-account-aside__email">{{ auth('customer')->user()->email }}</div>
        </div>

        <nav class="fx-account-nav">
            <a class="fx-account-nav__link {{ Request::is('user-account*') ? 'is-active' : '' }}" href="{{ route('user-account') }}">
                <i class="tio-user-outlined"></i><span>{{ translate('profile_info') }}</span>
            </a>
            <a class="fx-account-nav__link {{ Request::is('account-oder*') || Request::is('account-order-details*') || Request::is('track-order*') ? 'is-active' : '' }}" href="{{ route('account-oder') }}">
                <i class="tio-shopping-basket-outlined"></i><span>{{ translate('my_order') }}</span>
            </a>
            <a class="fx-account-nav__link {{ Request::is('wishlists*') ? 'is-active' : '' }}" href="{{ route('wishlists') }}">
                <i class="tio-heart-outlined"></i><span>{{ translate('wish_list') }}</span>
            </a>
            @if ($web_config['wallet_status'] == 1)
                <a class="fx-account-nav__link {{ Request::is('wallet') ? 'is-active' : '' }}" href="{{ route('wallet') }}">
                    <i class="tio-wallet-outlined"></i><span>{{ translate('my_wallet') }}</span>
                </a>
            @endif
            <a class="fx-account-nav__link {{ Request::is('hire/contracts*') ? 'is-active' : '' }}" href="{{ route('hire.contracts.index') }}">
                <i class="tio-briefcase-outlined"></i><span>{{ translate('my_hires') }}</span>
            </a>
            <a class="fx-account-nav__link {{ Request::is('hire/quotes*') ? 'is-active' : '' }}" href="{{ route('hire.quotes.index') }}">
                <i class="tio-receipt-outlined"></i><span>{{ translate('my_quote_requests') }}</span>
            </a>
            @if ($web_config['loyalty_point_status'] == 1)
                <a class="fx-account-nav__link {{ Request::is('loyalty') ? 'is-active' : '' }}" href="{{ route('loyalty') }}">
                    <i class="tio-medal-outlined"></i><span>{{ translate('my_loyalty_point') }}</span>
                </a>
            @endif
            @php
                $business_mode = getWebConfig(name: 'business_mode');
                $customerUnreadChatCount = 0;
                if (auth('customer')->check()) {
                    $unreadChatQuery = \App\Models\Chatting::where('user_id', auth('customer')->id())
                        ->where('seen_by_customer', 0)
                        ->where('sent_by_customer', 0);
                    $customerUnreadChatCount = $business_mode == 'multi'
                        ? (clone $unreadChatQuery)->where(function ($q) { $q->whereNotNull('seller_id')->orWhereNotNull('admin_id'); })->count()
                        : (clone $unreadChatQuery)->whereNotNull('delivery_man_id')->count();
                }
            @endphp
            <a class="fx-account-nav__link {{ Request::is('chat/vendor') || Request::is('chat/delivery-man') ? 'is-active' : '' }}" href="{{ route('chat', ['type' => $business_mode == 'multi' ? 'vendor' : 'delivery-man']) }}">
                <i class="tio-message-outlined"></i><span>{{ translate('inbox') }}</span>
                @if($customerUnreadChatCount > 0)
                    <span class="fx-badge">{{ $customerUnreadChatCount }}</span>
                @endif
            </a>
            <a class="fx-account-nav__link {{ Request::is('account-address*') ? 'is-active' : '' }}" href="{{ route('account-address') }}">
                <i class="tio-poi-outlined"></i><span>{{ translate('my_Address') }}</span>
            </a>
            <a class="fx-account-nav__link {{ Request::is('account-ticket*') || Request::is('support-ticket*') ? 'is-active' : '' }}" href="{{ route('account-tickets') }}">
                <i class="tio-support-outlined"></i><span>{{ translate('support_ticket') }}</span>
            </a>
            @if ($web_config['ref_earning_status'])
                <a class="fx-account-nav__link {{ Request::is('refer-earn*') ? 'is-active' : '' }}" href="{{ route('refer-earn') }}">
                    <i class="tio-gift-outlined"></i><span>{{ translate('refer_&_earn') }}</span>
                </a>
            @endif
            <a class="fx-account-nav__link {{ Request::is('user-coupons*') ? 'is-active' : '' }}" href="{{ route('user-coupons') }}">
                <i class="tio-discount-outlined"></i><span>{{ translate('coupons') }}</span>
            </a>
            <a class="fx-account-nav__link" href="{{ route('track-order.index') }}">
                <i class="tio-rocket-outlined"></i><span>{{ translate('track_order') }}</span>
            </a>
            <a class="fx-account-nav__link fx-account-nav__link--danger d-lg-none customer-account-delete-by-route" href="javascript:"
               data-route="{{ route('account-delete', [auth('customer')->id()]) }}"
               data-message="{{ translate('want_to_delete_this_account') }}?">
                <i class="tio-delete-outlined"></i><span>{{ translate('delete_account') }}</span>
            </a>
        </nav>
    </div>
</aside>

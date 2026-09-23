{{-- Variables: $product, $type, $rowIdx --}}
<tr>
    <th scope="row">{{ $rowIdx }}</th>
    <td>
        <a href="{{ route('vendor.products.view', [$product['id']]) }}"
           class="media align-items-center gap-2">
            <img src="{{ getStorageImages(path:$product->thumbnail_full_url,type:'backend-product')}}"
                 class="avatar border object-fit-cover" alt="">
            <div>
                <div class="media-body title-color hover-c1">
                    {{ Str::limit($product['name'], 20) }}
                </div>
                @if($product?->clearanceSale)
                    <div class="badge badge-soft-warning user-select-none">
                        {{ translate('Clearance_Sale') }}
                    </div>
                @endif
            </div>
        </a>
    </td>
    <td>
        @if($product->vendorTier && $product->vendorTier->tier)
            <span class="badge badge-soft-info">{{ $product->vendorTier->tier->name }}</span>
        @else
            <span class="badge badge-soft-danger">{{ translate('No_Tier') }}</span>
        @endif
    </td>
    <td class="text-center">{{ translate($product['product_type']) }}</td>
    <td class="text-center">
        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $product['unit_price']), currencyCode: getCurrencyCode()) }}
    </td>
    <td class="text-center">
        @if($product->request_status == 0)
            <label class="badge badge-soft-warning">{{translate('pending')}}</label>
        @elseif($product->request_status == 1)
            <label class="badge badge-soft-success">{{translate('approved')}}</label>
        @elseif($product->request_status == 2)
            <label class="badge badge-soft-danger">{{translate('denied')}}</label>
        @elseif($product->request_status == 3)
            <label class="badge badge-soft-danger">{{translate('cancelled/disabled')}}</label>
        @endif
    </td>
    @if($type != 'new-request')
        <td class="text-center">
            <form action="{{ route('vendor.products.featured-status') }}" method="post"
                  id="product-featured{{ $product['id'] }}-form"
                  class="admin-product-status-form" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $product['id'] }}">
                <label class="switcher mx-auto">
                    <input type="checkbox" class="switcher_input toggle-switch-message"
                           name="status"
                           id="product-featured{{ $product['id'] }}" value="1"
                           {{ $product['featured'] == 1 ? 'checked' : '' }}
                           data-modal-id="toggle-status-modal"
                           data-toggle-id="product-featured{{ $product['id'] }}"
                           data-on-image="product-status-on.png"
                           data-off-image="product-status-off.png"
                           data-on-title="{{ translate('Want_to_Add').' '.$product['name'].' '.translate('to_the_featured_section') }}"
                           data-off-title="{{ translate('Want_to_Remove').' '.$product['name'].' '.translate('to_the_featured_section') }}"
                           data-on-message="<p>{{ translate('if_enabled_this_product_will_be_shown_in_the_featured_product_on_the_website_and_customer_app') }}</p>"
                           data-off-message="<p>{{ translate('if_disabled_this_product_will_be_removed_from_the_featured_product_section_of_the_website_and_customer_app') }}</p>">
                    <span class="switcher_control"></span>
                </label>
            </form>
        </td>
        <td class="text-center">
            <form action="{{ route('vendor.products.status-update') }}" method="post"
                  id="product-status{{ $product['id'] }}-form"
                  class="admin-product-status-form" novalidate>
                @csrf
                <input type="hidden" name="id" value="{{ $product['id'] }}">
                <label class="switcher mx-auto">
                    <input type="checkbox" class="switcher_input toggle-switch-message"
                           name="status"
                           id="product-status{{ $product['id'] }}" value="1"
                           {{ $product['status'] == 1 ? 'checked' : '' }}
                           data-modal-id="toggle-status-modal"
                           data-toggle-id="product-status{{ $product['id'] }}"
                           data-on-image="product-status-on.png"
                           data-off-image="product-status-off.png"
                           data-on-title="{{ translate('Want_to_Turn_ON').' '.$product['name'].' '.translate('status') }}"
                           data-off-title="{{ translate('Want_to_Turn_OFF').' '.$product['name'].' '.translate('status') }}"
                           data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                           data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                    <span class="switcher_control"></span>
                </label>
            </form>
        </td>
    @endif
    <td>
        <div class="d-flex justify-content-center gap-2">
            @if($type != 'new-request')
                <a class="btn btn-outline-info btn-sm square-btn"
                   title="{{ translate('barcode') }}"
                   href="{{ route('vendor.products.barcode', [$product['id']]) }}">
                    <i class="tio-barcode"></i>
                </a>
                <a class="btn btn-outline-info btn-sm square-btn" title="{{ translate('view') }}"
                   href="{{ route('vendor.products.view', [$product['id']]) }}">
                    <i class="tio-invisible"></i>
                </a>
            @endif
            @if($product['request_status'] == 3)
                <span class="btn btn-outline--primary btn-sm square-btn disabled" title="{{ translate('cancelled/disabled') }}" style="opacity:.45;cursor:not-allowed;">
                    <i class="tio-edit"></i>
                </span>
            @else
                <a class="btn btn-outline--primary btn-sm square-btn"
                   title="{{ translate('edit') }}"
                   href="{{ route('vendor.products.update',[$product['id']]) }}">
                    <i class="tio-edit"></i>
                </a>
            @endif
            <span class="btn btn-outline-danger btn-sm square-btn delete-data"
                  title="{{ translate('delete') }}"
                  data-id="product-{{ $product['id']}}">
                <i class="tio-delete"></i>
            </span>
        </div>
        <form action="{{ route('vendor.products.delete',[$product['id']]) }}"
              method="post" id="product-{{ $product['id']}}" novalidate>
            @csrf @method('delete')
        </form>
    </td>
</tr>

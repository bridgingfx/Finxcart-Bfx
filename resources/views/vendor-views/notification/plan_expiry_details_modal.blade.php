<div class="modal-header border-0 pb-0 d-flex justify-content-end">
    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i class="tio-clear"></i></button>
</div>

<div class="modal-body px-4">
    <div class="text-center mb-4">
        <img width="60" src="{{dynamicAsset(path: 'public/assets/back-end/img/warning-2.png')}}" alt="">
        <h3 class="mt-3 text-danger">{{ translate('Plan_Expiring_Soon') }}</h3>
        <p class="text-muted">{{ $data->message }}</p>
    </div>

    {{-- 1. PLAN DETAILS --}}
    <div class="card mb-3 border-primary">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $vendorTier->tier->name ?? translate('Unknown_Plan') }}</h5>
                    <span class="badge badge-soft-info">
                        {{ translate('Expiry') }}: {{ $vendorTier->end_date->format('d M, Y') }}
                    </span>
                </div>
                <div class="text-right">
                    <h4 class="text-primary mb-0">{{ \App\Utils\Helpers::currency_converter($vendorTier->tier->monthly_fee_usd) }}</h4>
                    <small>{{ translate('For_30_Days') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. ASSOCIATED PRODUCTS LIST --}}
    <h5 class="mb-2">{{ translate('Products_Linked_to_this_Plan') }} ({{ $products->count() }})</h5>
    
    <div class="card mb-4" style="max-height: 200px; overflow-y: auto; border: 1px solid #eee;">
        <div class="table-responsive">
            <table class="table table-borderless table-striped table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>{{ translate('Image') }}</th>
                        <th>{{ translate('Name') }}</th>
                        <th>{{ translate('Price') }}</th>
                        <th>{{ translate('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'backend-product') }}" 
                                     class="rounded" width="30" alt="">
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                    {{ $product->name }}
                                </span>
                            </td>
                            <td>{{ \App\Utils\Helpers::currency_converter($product->unit_price) }}</td>
                            <td>
                                @if($product->status == 1)
                                    <span class="badge badge-soft-success">{{ translate('Active') }}</span>
                                @else
                                    <span class="badge badge-soft-secondary">{{ translate('Inactive') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">
                                {{ translate('No_products_assigned_to_this_plan_yet') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. RENEW / PAY NOW FORM --}}
    <div class="d-flex flex-column gap-2 justify-content-center align-items-center">
        <form action="{{ route('vendor.tier.renew') }}" method="POST" class="w-100" novalidate>
            @csrf
            <input type="hidden" name="tier_id" value="{{ $vendorTier->product_tier_id }}">
            <input type="hidden" name="vendor_tier_record_id" value="{{ $vendorTier->id }}">
            
            <button type="submit" class="btn btn--primary btn-block btn-lg">
                {{ translate('Pay_Now_&_Renew_for_30_Days') }}
            </button>
        </form>
        
        <p class="text-muted fs-12 mt-2">
            {{ translate('Clicking_Pay_Now_will_extend_this_plan_validity_by_30_days.') }}
        </p>
    </div>
</div>
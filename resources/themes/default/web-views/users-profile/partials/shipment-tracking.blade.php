@if($shipment)
    <div class="mt-2">
        @if($shipment->has_digital)
            <div class="fs-12 mb-2">
                <span class="font-semi-bold d-block mb-1">{{ translate('digital') }}:</span>
                @foreach($shipment->items->where('type', 'digital') as $digitalItem)
                    @php $itemProduct = json_decode(optional($digitalItem->orderDetail)->product_details); @endphp
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <span class="text-truncate" style="max-width: 200px;">{{ $itemProduct->name ?? translate('item') }}</span>
                        <span class="badge badge-soft-{{ $digitalItem->status == 'delivered' ? 'success' : 'primary' }} fs-11 text-capitalize">
                            {{ translate($digitalItem->status) }}
                        </span>
                        @if($digitalItem->delivery_mode == 'auto' && $digitalItem->status == 'delivered')
                            <a href="{{ route('digital-product-download', $digitalItem->order_detail_id) }}" class="btn btn-sm btn--primary py-0 px-2">
                                {{ translate('download') }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
        @if($shipment->has_physical)
            <div class="fs-12">
                <span class="font-semi-bold">{{ translate('physical') }}:</span>
                <span class="badge badge-soft-{{ $shipment->physical_status == 'delivered' ? 'success' : (in_array($shipment->physical_status, ['canceled', 'failed']) ? 'danger' : 'primary') }} fs-11 text-capitalize">
                    {{ translate($shipment->physical_status ?? 'pending') }}
                </span>
            </div>
        @endif
    </div>
@endif

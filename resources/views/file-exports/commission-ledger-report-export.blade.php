<html>
<table>
    <thead>
    <tr>
        <th>{{ translate('commission_Ledger_Report') }}</th>
    </tr>
    <tr>
        <th>{{ translate('filter_Criteria') .' -' }}</th>
        <th></th>
        <th>
            {{ translate('search_Bar_Content') .' - '. ($data['search'] ?? 'N/A') }}
            @if(isset($data['seller']) && $data['seller'] !== 'all')
                <br>
                {{ translate('store_Name') }} - {{ $data['seller']?->shop?->name ?? trim(($data['seller']?->f_name ?? '') . ' ' . ($data['seller']?->l_name ?? '')) }}
            @endif
            <br>
            {{ translate('date_type') .' - '. translate($data['dateType']) }}
            @if($data['from'] && $data['to'])
                <br>
                {{ translate('from') .' - '. date('d M, Y', strtotime($data['from'])) }}
                <br>
                {{ translate('to') .' - '. date('d M, Y', strtotime($data['to'])) }}
            @endif
        </th>
    </tr>
    <tr>
        <th>{{ translate('SL') }}</th>
        @if(($data['data-from'] ?? '') === 'admin')
            <th>{{ translate('vendor') }}</th>
        @endif
        <th>{{ translate('order_ID') }}</th>
        <th>{{ translate('transaction_ID') }}</th>
        <th>{{ translate('gross_Amount') }}</th>
        <th>{{ translate('platform_commission') }}</th>
        <th>{{ translate('service_Charge') }}</th>
        <th>{{ translate('net_Vendor_Payout') }}</th>
        <th>{{ translate('date') }}</th>
    </tr>
    @foreach($data['ledgers'] as $key => $ledger)
        <tr>
            <td>{{ $key + 1 }}</td>
            @if(($data['data-from'] ?? '') === 'admin')
                <td>{{ $ledger->seller?->shop?->name ?? trim(($ledger->seller?->f_name ?? '') . ' ' . ($ledger->seller?->l_name ?? '')) }}</td>
            @endif
            <td>{{ $ledger->order_id ?? translate('not_available') }}</td>
            <td>{{ $ledger->order?->orderTransaction?->transaction_id ?? $ledger->reference_id ?? translate('not_available') }}</td>
            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->gross_amount), currencyCode: getCurrencyCode()) }}</td>
            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->company_share_amount), currencyCode: getCurrencyCode()) }}</td>
            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->service_charge_amount), currencyCode: getCurrencyCode()) }}</td>
            <td>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $ledger->net_vendor_payout), currencyCode: getCurrencyCode()) }}</td>
            <td>{{ $ledger->created_at?->format('d F Y h:i:s a') }}</td>
        </tr>
    @endforeach
    <tr>
        <td></td>
    </tr>
    <tr>
        <th>{{ translate('total') }}</th>
        @if(($data['data-from'] ?? '') === 'admin')
            <th></th>
        @endif
        <th></th>
        <th></th>
        <th>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_gross']), currencyCode: getCurrencyCode()) }}</th>
        <th>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_platform_commission']), currencyCode: getCurrencyCode()) }}</th>
        <th>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_service_charge']), currencyCode: getCurrencyCode()) }}</th>
        <th>{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $data['total_net_payout']), currencyCode: getCurrencyCode()) }}</th>
        <th></th>
    </tr>
    </thead>
</table>
</html>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ 'Commission Ledger Statement - '.$data['duration'] }}</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/admin/order-transaction.css') }}">
</head>
<body>
<table class="content-position">
    <tr>
        <td>
            <table class="bs-0">
                <tr>
                    <th class="h3 p-0 text-left">{{ translate('commission_Ledger_Statement') }}</th>
                    <th class="p-0 text-right">
                        @php($invoiceSettings = getWebConfig(name: 'invoice_settings'))
                        @if(isset($invoiceSettings['invoice_logo_status']) && $invoiceSettings['invoice_logo_status'] == 1)
                            <img height="40" src="{{ getStorageImages(path: getWebConfig(name: 'company_web_logo_png'), type:'backend-logo') }}" alt="" style="margin-bottom:5px;object-fit: contain">
                        @endif
                    </th>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="pt-0">
            <table class="bs-0">
                <tr>
                    <td class="p-0 text-left"><b class="bold black">{{ translate('date') }}</b> : {{ date('F d, Y') }}</td>
                </tr>
                <tr>
                    <td class="p-0 text-left"><b class="bold black">{{ translate('duration') }}</b> : <span class="text-capitalize">{{ $data['duration'] }}</span></td>
                </tr>
                <tr>
                    <td class="p-0 text-left"><b class="bold black">{{ translate('store_Name') }}</b> : {{ $data['shop_name'] }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="content-position">
    <tr>
        <td class="pt-0">
            <table class="bs-0 __product-table inter">
                <tbody>
                <tr>
                    <td style="background-color: #0177CD !important; color: white; font-weight: bold;text-align:center">{{ translate('SL') }}</td>
                    <td style="background-color: #0177CD !important; color: white; font-weight: bold">{{ translate('details') }}</td>
                    <td class="text-right" style="background-color: #0177CD !important; color: white; font-weight: bold">{{ translate('amount') }}</td>
                </tr>
                @foreach($data['summary'] as $key => $summary)
                    <tr>
                        <td class="text-center">{{ $key + 1 }}</td>
                        <td>{{ $summary['label'] }}</td>
                        <td class="text-right">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $summary['amount']), currencyCode: getCurrencyCode()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </td>
    </tr>
</table>

<table>
    <tr>
        <th class="content-position-y bg-light py-4 footer">
            <div>{{ translate('phone') }} : {{ $data['company_phone'] }}</div>
            <div>{{ translate('email') }} : {{ $data['company_email'] }}</div>
            <div>{{ url('/') }}</div>
            <div>{{ translate('all_copy_right_reserved_©_'.date('Y').'_').$data['company_name'] }}</div>
        </th>
    </tr>
</table>
</body>
</html>

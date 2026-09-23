<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>New Withdrawal Request</title>
<style>
    body { margin:0; padding:0; background:#f4f6f9; font-family: Arial, sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .header { padding:32px 40px; text-align:center; background:linear-gradient(135deg,#0d6efd,#3b82f6); }
    .header h1 { margin:0; color:#fff; font-size:22px; }
    .header p  { margin:8px 0 0; color:rgba(255,255,255,.85); font-size:14px; }
    .body { padding:32px 40px; }
    .amount-box { background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:20px 24px; text-align:center; margin-bottom:28px; }
    .amount-box .label { font-size:13px; color:#6b7280; margin-bottom:4px; }
    .amount-box .amount { font-size:32px; font-weight:700; color:#1d4ed8; }
    .details { margin:24px 0; }
    .details table { width:100%; border-collapse:collapse; }
    .details td { padding:10px 0; border-bottom:1px solid #e5e7eb; font-size:14px; }
    .details td.label { color:#6b7280; width:40%; }
    .details td.value { color:#111827; font-weight:600; text-align:right; }
    .footer { text-align:center; padding:24px 40px; background:#f9fafb; border-top:1px solid #e5e7eb; }
    .footer p { margin:0; font-size:12px; color:#9ca3af; }
    .btn { display:inline-block; margin-top:20px; padding:12px 28px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>New Withdrawal Request</h1>
        <p>Request #{{ $withdrawRequest->id }} — {{ date('d F Y', strtotime($withdrawRequest->created_at)) }}</p>
    </div>

    <div class="body">
        <p>Hello Admin,</p>
        <p>A vendor has submitted a new withdrawal request that requires your review.</p>

        <div class="amount-box">
            <div class="label">Requested Amount</div>
            <div class="amount">
                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest->amount), currencyCode: getCurrencyCode(type: 'default')) }}
            </div>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td class="label">Vendor</td>
                    <td class="value">{{ trim(($withdrawRequest->seller->f_name ?? '') . ' ' . ($withdrawRequest->seller->l_name ?? '')) ?: $withdrawRequest->seller->email }}</td>
                </tr>
                @if($withdrawRequest->seller?->shop?->name)
                <tr>
                    <td class="label">Shop</td>
                    <td class="value">{{ $withdrawRequest->seller->shop->name }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Submitted On</td>
                    <td class="value">{{ date('d M Y, h:i A', strtotime($withdrawRequest->created_at)) }}</td>
                </tr>
            </table>
        </div>

        <div style="text-align:center;">
            <a class="btn" href="{{ route('admin.vendors.withdraw_list') }}">Review Request</a>
        </div>
    </div>

    <div class="footer">
        <p>{{ getWebConfig(name: 'company_name') ?? config('app.name') }} &mdash; This is an automated notification. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>

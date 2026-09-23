<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Withdrawal Receipt #{{ $withdrawRequest->id }}</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size:12px; color:#222; margin:0; padding:0; }
    .page { padding:40px 50px; }
    .header { border-bottom:3px solid #166534; padding-bottom:18px; margin-bottom:24px; display:table; width:100%; }
    .header-left { display:table-cell; vertical-align:middle; }
    .header-right { display:table-cell; text-align:right; vertical-align:middle; }
    .header-right .receipt-no { font-size:18px; font-weight:700; color:#166534; }
    h1.company { margin:0; font-size:20px; color:#111; }
    .sub { color:#6b7280; font-size:11px; margin-top:3px; }
    .badge-approved { background:#166534; color:#fff; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .badge-denied   { background:#dc2626; color:#fff; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .section { margin-bottom:22px; }
    .section-title { font-size:11px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:.6px; border-bottom:1px solid #e5e7eb; padding-bottom:5px; margin-bottom:10px; }
    .row-pair { display:table; width:100%; margin-bottom:7px; }
    .row-label { display:table-cell; width:40%; color:#6b7280; font-size:11px; vertical-align:top; }
    .row-value { display:table-cell; font-weight:600; color:#111; font-size:12px; }
    .amount-highlight { font-size:24px; font-weight:700; color:#166534; margin:6px 0; }
    .timeline-item { display:table; width:100%; margin-bottom:10px; }
    .tl-dot-cell { display:table-cell; width:24px; vertical-align:top; padding-top:3px; }
    .tl-dot { width:10px; height:10px; border-radius:50%; background:#d1d5db; display:inline-block; }
    .tl-dot.done { background:#16a34a; }
    .tl-content-cell { display:table-cell; vertical-align:top; }
    .tl-title { font-weight:600; font-size:12px; color:#111; }
    .tl-time  { font-size:11px; color:#6b7280; }
    .footer { margin-top:40px; border-top:1px solid #e5e7eb; padding-top:14px; text-align:center; color:#9ca3af; font-size:10px; }
    .watermark { position:fixed; bottom:60px; right:40px; opacity:.06; font-size:72px; font-weight:900; color:#166534; transform:rotate(-30deg); }
</style>
</head>
<body>
<div class="watermark">PAID</div>
<div class="page">
    {{-- Header --}}
    <div class="header">
        <div class="header-left">
            <h1 class="company">{{ getWebConfig(name: 'company_name') ?? config('app.name') }}</h1>
            <div class="sub">Vendor Withdrawal Receipt</div>
        </div>
        <div class="header-right">
            <div class="receipt-no">Receipt #{{ str_pad($withdrawRequest->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="sub" style="margin-top:4px;">{{ date('d F Y', strtotime($withdrawRequest->approved_at ?? $withdrawRequest->updated_at)) }}</div>
            <div style="margin-top:6px;">
                @if($withdrawRequest->approved == 1)
                    <span class="badge-approved">APPROVED</span>
                @else
                    <span class="badge-denied">DENIED</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Amount --}}
    <div class="section">
        <div class="section-title">Withdrawal Amount</div>
        <div class="amount-highlight">
            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest->amount), currencyCode: getCurrencyCode(type: 'default')) }}
        </div>
    </div>

    {{-- Vendor Info --}}
    <div class="section">
        <div class="section-title">Vendor Information</div>
        <div class="row-pair">
            <div class="row-label">Name</div>
            <div class="row-value">{{ $withdrawRequest->seller->f_name ?? '' }} {{ $withdrawRequest->seller->l_name ?? '' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">Email</div>
            <div class="row-value">{{ $withdrawRequest->seller->email ?? '—' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">Phone</div>
            <div class="row-value">{{ $withdrawRequest->seller->phone ?? '—' }}</div>
        </div>
        @if($withdrawRequest->seller->shop)
        <div class="row-pair">
            <div class="row-label">Shop</div>
            <div class="row-value">{{ $withdrawRequest->seller->shop->name ?? '—' }}</div>
        </div>
        @endif
    </div>

    {{-- Bank Info --}}
    <div class="section">
        <div class="section-title">Bank Details</div>
        <div class="row-pair">
            <div class="row-label">Account Holder</div>
            <div class="row-value">{{ $withdrawRequest->seller->holder_name ?? '—' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">Bank Name</div>
            <div class="row-value">{{ $withdrawRequest->seller->bank_name ?? '—' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">Account / IBAN</div>
            <div class="row-value">{{ $withdrawRequest->seller->account_no ?? '—' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">SWIFT / BIC</div>
            <div class="row-value">{{ $withdrawRequest->seller->swift_code ?? '—' }}</div>
        </div>
        <div class="row-pair">
            <div class="row-label">Bank Country</div>
            <div class="row-value">{{ $withdrawRequest->seller->bank_country ?? '—' }}</div>
        </div>
        @if($withdrawRequest->seller->branch)
        <div class="row-pair">
            <div class="row-label">Branch</div>
            <div class="row-value">{{ $withdrawRequest->seller->branch }}</div>
        </div>
        @endif
        @if($withdrawRequest->seller->currency_preference)
        <div class="row-pair">
            <div class="row-label">Currency</div>
            <div class="row-value">{{ $withdrawRequest->seller->currency_preference }}</div>
        </div>
        @endif
    </div>

    {{-- Timeline --}}
    <div class="section">
        <div class="section-title">Request Timeline</div>

        <div class="timeline-item">
            <div class="tl-dot-cell"><div class="tl-dot done"></div></div>
            <div class="tl-content-cell">
                <div class="tl-title">Request Submitted</div>
                <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->created_at)) }}</div>
            </div>
        </div>

        @if($withdrawRequest->approved == 1 && $withdrawRequest->approved_at)
        <div class="timeline-item">
            <div class="tl-dot-cell"><div class="tl-dot done"></div></div>
            <div class="tl-content-cell">
                <div class="tl-title">Approved by Admin</div>
                <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->approved_at)) }}</div>
            </div>
        </div>
        @endif

        @if($withdrawRequest->delivery_status === 'in_transfer' && $withdrawRequest->delivery_status_at)
        <div class="timeline-item">
            <div class="tl-dot-cell"><div class="tl-dot done"></div></div>
            <div class="tl-content-cell">
                <div class="tl-title">Transfer Initiated</div>
                <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->delivery_status_at)) }}</div>
            </div>
        </div>
        @endif

        @if($withdrawRequest->delivery_status === 'transferred' && $withdrawRequest->delivery_status_at)
        <div class="timeline-item">
            <div class="tl-dot-cell"><div class="tl-dot done"></div></div>
            <div class="tl-content-cell">
                <div class="tl-title">Funds Transferred</div>
                <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->delivery_status_at)) }}</div>
            </div>
        </div>
        @endif
    </div>

    @if($withdrawRequest->transaction_note)
    <div class="section">
        <div class="section-title">Admin Note</div>
        <p style="margin:0;color:#374151;">{{ $withdrawRequest->transaction_note }}</p>
    </div>
    @endif

    <div class="footer">
        This is an official withdrawal receipt issued by {{ getWebConfig(name: 'company_name') ?? config('app.name') }}.
        Request ID: #{{ $withdrawRequest->id }} &mdash; Generated: {{ date('d M Y H:i:s') }}
    </div>
</div>
</body>
</html>

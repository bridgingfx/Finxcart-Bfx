<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Withdrawal Request {{ $statusLabel }}</title>
<style>
    body { margin:0; padding:0; background:#f4f6f9; font-family: Arial, sans-serif; color:#333; }
    .wrapper { max-width:600px; margin:40px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
    .header { padding:32px 40px; text-align:center; }
    .header.approved { background:linear-gradient(135deg,#1a8754,#20c997); }
    .header.denied   { background:linear-gradient(135deg,#b91c1c,#ef4444); }
    .header h1 { margin:0; color:#fff; font-size:22px; }
    .header p  { margin:8px 0 0; color:rgba(255,255,255,.85); font-size:14px; }
    .body { padding:32px 40px; }
    .amount-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:20px 24px; text-align:center; margin-bottom:28px; }
    .amount-box.denied { background:#fef2f2; border-color:#fecaca; }
    .amount-box .label { font-size:13px; color:#6b7280; margin-bottom:4px; }
    .amount-box .amount { font-size:32px; font-weight:700; color:#166534; }
    .amount-box.denied .amount { color:#991b1b; }
    .timeline { margin:24px 0; }
    .timeline h3 { font-size:14px; color:#374151; margin-bottom:16px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
    .tl-item { display:flex; gap:16px; margin-bottom:16px; }
    .tl-dot { width:12px; height:12px; border-radius:50%; background:#d1d5db; margin-top:4px; flex-shrink:0; }
    .tl-dot.done { background:#16a34a; }
    .tl-dot.denied { background:#dc2626; }
    .tl-content .tl-title { font-size:14px; font-weight:600; color:#111827; }
    .tl-content .tl-time  { font-size:12px; color:#6b7280; margin-top:2px; }
    .note-box { background:#fffbeb; border:1px solid #fde68a; border-radius:6px; padding:16px; margin-top:20px; }
    .note-box .note-label { font-size:12px; font-weight:600; color:#92400e; margin-bottom:6px; }
    .note-box p { margin:0; font-size:14px; color:#78350f; }
    .footer { text-align:center; padding:24px 40px; background:#f9fafb; border-top:1px solid #e5e7eb; }
    .footer p { margin:0; font-size:12px; color:#9ca3af; }
    .btn { display:inline-block; margin-top:20px; padding:12px 28px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
    <div class="header {{ strtolower($statusLabel) }}">
        <h1>
            @if($statusLabel === 'Approved')
                ✓ Withdrawal Request Approved
            @else
                ✗ Withdrawal Request Denied
            @endif
        </h1>
        <p>Request #{{ $withdrawRequest->id }} — {{ date('d F Y', strtotime($withdrawRequest->created_at)) }}</p>
    </div>

    <div class="body">
        <p>Hello <strong>{{ $withdrawRequest->seller->f_name ?? 'Vendor' }}</strong>,</p>

        @if($statusLabel === 'Approved')
            <p>Great news! Your withdrawal request has been <strong>approved</strong>. The funds will be transferred to your registered bank account within 3–5 business days.</p>
        @else
            <p>We're sorry to inform you that your withdrawal request has been <strong>denied</strong>. Please review the note below and contact support if you have questions.</p>
        @endif

        <div class="amount-box {{ strtolower($statusLabel) === 'denied' ? 'denied' : '' }}">
            <div class="label">Withdrawal Amount</div>
            <div class="amount">
                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $withdrawRequest->amount), currencyCode: getCurrencyCode(type: 'default')) }}
            </div>
        </div>

        {{-- Status timeline --}}
        <div class="timeline">
            <h3>Request Timeline</h3>
            <div class="tl-item">
                <div class="tl-dot done"></div>
                <div class="tl-content">
                    <div class="tl-title">Request Submitted</div>
                    <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->created_at)) }}</div>
                </div>
            </div>
            @if($statusLabel === 'Approved' && $withdrawRequest->approved_at)
            <div class="tl-item">
                <div class="tl-dot done"></div>
                <div class="tl-content">
                    <div class="tl-title">Approved by Admin</div>
                    <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->approved_at)) }}</div>
                </div>
            </div>
            <div class="tl-item">
                <div class="tl-dot {{ in_array($withdrawRequest->delivery_status, ['in_transfer','transferred']) ? 'done' : '' }}"></div>
                <div class="tl-content">
                    <div class="tl-title">Transfer Initiated</div>
                    <div class="tl-time">Pending — within 3–5 business days</div>
                </div>
            </div>
            @elseif($statusLabel === 'Denied' && $withdrawRequest->denied_at)
            <div class="tl-item">
                <div class="tl-dot denied"></div>
                <div class="tl-content">
                    <div class="tl-title">Denied by Admin</div>
                    <div class="tl-time">{{ date('d M Y, h:i A', strtotime($withdrawRequest->denied_at)) }}</div>
                </div>
            </div>
            @endif
        </div>

        @if($withdrawRequest->transaction_note)
        <div class="note-box">
            <div class="note-label">Admin Note</div>
            <p>{{ $withdrawRequest->transaction_note }}</p>
        </div>
        @endif

        @if($statusLabel === 'Approved')
        <p style="margin-top:20px;font-size:13px;color:#6b7280;">
            A PDF receipt is attached to this email with the full request details.
        </p>
        @endif
    </div>

    <div class="footer">
        <p>{{ getWebConfig(name: 'company_name') ?? config('app.name') }} &mdash; This is an automated notification. Please do not reply to this email.</p>
    </div>
</div>
</body>
</html>

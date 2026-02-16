<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        .header { margin-bottom: 14px; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .header-table { width: 100%; border: 0; }
        .header-table td { border: 0; vertical-align: top; }
        .company-title { font-size: 17px; font-weight: 700; margin: 0 0 4px 0; }
        .doc-title { font-size: 22px; font-weight: 700; margin: 8px 0 4px 0; letter-spacing: .5px; }
        .meta { margin: 0; color: #374151; }
        .logo { width: 120px; height: auto; margin-left: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .totals { margin-top: 10px; }
        .signature-wrap { margin-top: 24px; width: 100%; }
        .signature-box { width: 240px; margin-left: auto; text-align: center; }
        .stamp-signature { width: 180px; height: auto; margin: 8px auto 6px auto; }
        .signatory-name { font-weight: 700; text-decoration: underline; margin-top: 6px; }
        .footnote { margin-top: 16px; padding-top: 8px; border-top: 1px dashed #9ca3af; color: #374151; font-size: 11px; }
    </style>
</head>
<body>
<div class="header">
    <table class="header-table">
        <tr>
            <td>
                <p class="company-title">{{ $tenant?->name ?? 'Perusahaan' }}</p>
                @if(!empty($tenant?->address))
                    <p class="meta">{{ $tenant->address }}</p>
                @endif
                <p class="meta">
                    @if(!empty($tenant?->email)){{ $tenant->email }}@endif
                    @if(!empty($tenant?->phone)) | {{ $tenant->phone }}@endif
                </p>
            </td>
            <td style="width: 140px;">
                @if(!empty($branding['logo_data_uri']))
                    <img class="logo" src="{{ $branding['logo_data_uri'] }}" alt="Logo">
                @endif
            </td>
        </tr>
    </table>
</div>

<p class="doc-title">INVOICE</p>
<p class="meta"><strong>No:</strong> {{ $invoice->number }}</p>
<p class="meta"><strong>Tanggal:</strong> {{ $invoice->date }}</p>
<p class="meta"><strong>Jatuh Tempo:</strong> {{ $invoice->due_date }}</p>
<p class="meta"><strong>Customer:</strong> {{ $invoice->customer->name }}</p>

<table>
    <thead>
    <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
    </thead>
    <tbody>
    @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td class="right">{{ number_format($item->qty, 2) }}</td>
            <td class="right">{{ number_format($item->price, 2) }}</td>
            <td class="right">{{ number_format($item->line_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="totals">
    <p class="right"><strong>Grand Total: {{ number_format($invoice->grand_total, 2) }}</strong></p>
    <p class="right">Paid: {{ number_format($invoice->paid_total, 2) }} | Balance: {{ number_format($invoice->balance_total, 2) }}</p>
</div>

<div class="signature-wrap">
    <div class="signature-box">
        <p>Hormat kami,</p>
        @if(!empty($branding['stamp_signature_data_uri']))
            <img class="stamp-signature" src="{{ $branding['stamp_signature_data_uri'] }}" alt="Cap & Tanda Tangan">
        @endif
        <p class="signatory-name">{{ $branding['signatory_name'] ?? 'Authorized Signatory' }}</p>
        @if(!empty($branding['signatory_position']))
            <p>{{ $branding['signatory_position'] }}</p>
        @endif
    </div>
</div>

@if(!empty($branding['document_notes']))
    <p class="footnote">{{ $branding['document_notes'] }}</p>
@endif
</body>
</html>

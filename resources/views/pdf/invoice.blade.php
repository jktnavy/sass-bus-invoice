@php
    use App\Support\IndonesianFormat;
    use App\Support\Terbilang;
    use Illuminate\Support\Str;

    $tenantSettings = is_array($tenant?->settings) ? $tenant->settings : [];
    $invoiceDate = $invoice->date ? IndonesianFormat::dateLong($invoice->date) : '-';
    $dueDate = $invoice->due_date ? IndonesianFormat::dateLong($invoice->due_date) : '-';

    $sourceQuotation = $invoice->sourceQuotation;
    $recipientCompany = $sourceQuotation?->customer?->name ?: $invoice->customer?->name ?: '-';
    $sourcePic =
        $sourceQuotation?->recipient_company_line2 ?:
        $sourceQuotation?->customer?->pics?->sortByDesc('is_primary')->first()?->name ?? null;

    $usageStart = $sourceQuotation?->usage_date ? IndonesianFormat::dateLong($sourceQuotation->usage_date) : null;
    $usageEnd = $sourceQuotation?->usage_end_date ? IndonesianFormat::dateLong($sourceQuotation->usage_end_date) : null;

    $usageDurationDays = null;
    if ($sourceQuotation?->usage_date && $sourceQuotation?->usage_end_date) {
        $usageDurationDays =
            \Carbon\Carbon::parse($sourceQuotation->usage_date)
                ->startOfDay()
                ->diffInDays(\Carbon\Carbon::parse($sourceQuotation->usage_end_date)->startOfDay()) + 1;
    }

    $includedText = trim((string) ($sourceQuotation?->included_text ?? ''));
    $excludedText = trim((string) ($sourceQuotation?->excluded_text ?? ''));
    $facilitiesText = trim((string) ($sourceQuotation?->facilities_text ?? ''));

    $bank = $tenantSettings['bank_name'] ?? null;
    $holder = $tenantSettings['bank_account_holder'] ?? null;
    $number = $tenantSettings['bank_account_number'] ?? null;

    $signatoryName = $tenantSettings['signatory_name'] ?? 'Authorized Signatory';
    $signatoryTitle = $tenantSettings['signatory_position'] ?? null;

    $grandTotalTerbilang = Str::title(Terbilang::make((float) $invoice->grand_total) . ' Rupiah');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 1cm;
        }

        body {
            margin: 0;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.5;
            /* Menggunakan sans-serif agar terlihat modern & corporate */
            font-family: 'Helvetica', 'Arial', sans-serif;
        }

        /* Helpers */
        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .text-slate-500 {
            color: #64748b;
        }

        /* Layout */
        .header-table {
            margin-bottom: 30px;
        }

        .logo {
            max-width: 180px;
            height: auto;
        }

        .company-info h1 {
            font-size: 16pt;
            margin: 0;
            color: #0f172a;
        }

        .doc-type {
            font-size: 24pt;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
            letter-spacing: -1px;
        }

        /* Info Grid */
        .info-grid {
            margin-bottom: 30px;
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px 0;
        }

        .info-column {
            vertical-align: top;
        }

        .label-sm {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 10px;
            font-size: 9pt;
            text-transform: uppercase;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .bg-gray {
            background-color: #f8fafc;
        }

        /* Summary Area */
        .summary-container {
            margin-top: 20px;
        }

        .summary-table {
            width: 300px;
            float: right;
        }

        .summary-table td {
            padding: 4px 0;
        }

        .grand-total-row td {
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            font-size: 11pt;
            font-weight: bold;
        }

        .terbilang-box {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-style: italic;
            font-size: 9pt;
        }

        /* Footer & Signature */
        .bottom-section {
            margin-top: 40px;
            clear: both;
        }

        .payment-box {
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            width: 60%;
        }

        .signature-box {
            width: 200px;
            text-align: center;
        }

        .signature-space {
            height: 80px;
            position: relative;
        }

        .stamp-img {
            position: absolute;
            width: 120px;
            top: 0;
            left: 40px;
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <table class="header-table" width="100%">
        <tr>
            <td width="50%">
                @if (!empty($branding['logo_data_uri']))
                    <img src="{{ $branding['logo_data_uri'] }}" class="logo">
                @else
                    <div class="company-info">
                        <h1>{{ $tenant?->name }}</h1>
                    </div>
                @endif
            </td>
            <td width="50%" class="text-right">
                <h2 class="doc-type">INVOICE</h2>
                <div class="text-slate-500">No: <strong>{{ $invoice->number }}</strong></div>
            </td>
        </tr>
    </table>

    <table class="info-grid" width="100%">
        <tr>
            <td class="info-column" width="35%">
                <div class="label-sm">Diterbitkan Oleh:</div>
                <div class="font-bold">{{ $tenant?->name }}</div>
                <div class="text-slate-500" style="font-size: 9pt;">
                    {!! nl2br(e($tenant?->address)) !!}<br>
                    Email: {{ $tenant?->email }} | Telp: {{ $tenant?->phone }}
                </div>
            </td>
            <td class="info-column" width="35%">
                <div class="label-sm">Ditagihkan Kepada:</div>
                <div class="font-bold">{{ $recipientCompany }}</div>
                @if ($sourcePic)
                    <div class="text-slate-500">Attn: {{ $sourcePic }}</div>
                @endif
                @if ($sourceQuotation?->customer?->address)
                    <div class="text-slate-500" style="font-size: 9pt;">
                        {{ $sourceQuotation?->customer?->address }}
                    </div>
                @endif
            </td>
            <td class="info-column" width="30%">
                <table width="100%">
                    <tr>
                        <td class="label-sm">Tanggal</td>
                        <td class="text-right font-bold">{{ $invoiceDate }}</td>
                    </tr>
                    <tr>
                        <td class="label-sm">Jatuh Tempo</td>
                        <td class="text-right font-bold" style="color: #ef4444;">{{ $dueDate }}</td>
                    </tr>
                    @if ($sourceQuotation)
                        <tr>
                            <td class="label-sm">Ref. No</td>
                            <td class="text-right font-bold">{{ $sourceQuotation->number ?: '-' }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if ($sourceQuotation?->subject_text || $usageStart)
        <div style="margin-bottom: 20px;">
            <div class="label-sm">Perihal / Periode Penggunaan:</div>
            <div style="font-size: 10pt;">
                <strong>{{ $sourceQuotation?->subject_text ?: 'Invoice Penagihan Layanan' }}</strong>
                @if ($usageStart)
                    <span class="text-slate-500">({{ $usageStart }} s/d {{ $usageEnd }})
                        {{ $usageDurationDays ? "[$usageDurationDays Hari]" : '' }}</span>
                @endif
            </div>
        </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="45%" class="text-left">Deskripsi Layanan</th>
                <th width="10%">Qty</th>
                <th width="10%">Satuan</th>
                <th width="15%" class="text-right">Harga Satuan</th>
                <th width="15%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoice->items->sortBy('sort_order') as $index => $item)
                <tr class="{{ $index % 2 == 0 ? '' : 'bg-gray' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $item->name }}</div>
                        @if (filled($item->description))
                            <div class="text-slate-500" style="font-size: 8.5pt;">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}
                    </td>
                    <td class="text-center">{{ $item->uom }}</td>
                    <td class="text-right">{{ IndonesianFormat::rupiah((float) $item->price) }}</td>
                    <td class="text-right font-bold">{{ IndonesianFormat::rupiah((float) $item->line_total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-container">
        <div style="width: 55%; float: left;">
            <div class="terbilang-box">
                <strong>Terbilang:</strong><br>
                {{ $grandTotalTerbilang }}
            </div>

            @if ($includedText || $excludedText)
                <div style="font-size: 8pt; margin-top: 10px;">
                    @if ($includedText)
                        <strong>Termasuk:</strong> {{ $includedText }}<br>
                    @endif
                    @if ($excludedText)
                        <strong>Tidak Termasuk:</strong> {{ $excludedText }}
                    @endif
                </div>
            @endif
        </div>

        <table class="summary-table">
            <tr>
                <td class="text-slate-500">Sub Total</td>
                <td class="text-right font-bold">{{ IndonesianFormat::rupiah((float) $invoice->sub_total) }}</td>
            </tr>
            @if ($invoice->discount_total > 0)
                <tr>
                    <td class="text-slate-500">Diskon</td>
                    <td class="text-right" style="color: #ef4444;">-
                        {{ IndonesianFormat::rupiah((float) $invoice->discount_total) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-slate-500">Pajak (PPN)</td>
                <td class="text-right font-bold">{{ IndonesianFormat::rupiah((float) $invoice->tax_total) }}</td>
            </tr>
            <tr class="grand-total-row">
                <td class="uppercase">Grand Total</td>
                <td class="text-right">{{ IndonesianFormat::rupiah((float) $invoice->grand_total) }}</td>
            </tr>
            @if ($invoice->paid_total > 0)
                <tr>
                    <td class="text-slate-500" style="font-size: 9pt;">Sudah Dibayar</td>
                    <td class="text-right" style="font-size: 9pt;">
                        {{ IndonesianFormat::rupiah((float) $invoice->paid_total) }}</td>
                </tr>
                <tr style="background: #fef2f2;">
                    <td class="font-bold uppercase" style="font-size: 10pt; padding: 5px;">Sisa Tagihan</td>
                    <td class="text-right font-bold" style="font-size: 10pt; padding: 5px;">
                        {{ IndonesianFormat::rupiah((float) $invoice->balance_total) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="bottom-section">
        <table width="100%">
            <tr>
                <td width="60%" class="info-column">
                    <div class="payment-box">
                        <div class="label-sm" style="margin-bottom: 8px;">Informasi Pembayaran:</div>
                        <div style="font-size: 9pt;">
                            Bank: <strong>{{ $bank ?? '-' }}</strong><br>
                            Nomor Rekening: <strong>{{ $number ?? '-' }}</strong><br>
                            Atas Nama: <strong>{{ $holder ?? '-' }}</strong><br>
                            <em class="text-slate-500">*Mohon lampirkan bukti transfer saat melakukan pembayaran.</em>
                        </div>
                    </div>
                </td>
                <td width="40%" class="text-right">
                    <div class="signature-box" style="margin-left: auto;">
                        <div>{{ $sourceQuotation?->city ?? 'Jakarta' }}, {{ $invoiceDate }}</div>
                        <div class="font-bold" style="margin-top: 5px;">Hormat Kami,</div>
                        <div class="signature-space">
                            @if (!empty($branding['stamp_signature_data_uri']))
                                <img src="{{ $branding['stamp_signature_data_uri'] }}" class="stamp-img">
                            @endif
                        </div>
                        <div class="font-bold" style="text-decoration: underline;">{{ $signatoryName }}</div>
                        <div class="text-slate-500" style="font-size: 9pt;">{{ $signatoryTitle }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if (!empty($branding['document_notes']))
        <div
            style="margin-top: 30px; border-top: 1px dashed #cbd5e1; padding-top: 10px; font-size: 8pt; color: #64748b;">
            <strong>Catatan:</strong> {{ $branding['document_notes'] }}
        </div>
    @endif
</body>

</html>

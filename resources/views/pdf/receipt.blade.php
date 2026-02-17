@php
    use App\Support\IndonesianFormat;
    use App\Support\Terbilang;
    use Illuminate\Support\Str;

    $tenantSettings = is_array($tenant?->settings) ? $tenant->settings : [];
    $sourceQuotation = $invoice->sourceQuotation;
    $isPaid = ((float) $invoice->balance_total <= 0) || ((int) $invoice->status === 3);
    $recipientCompany = $sourceQuotation?->customer?->name ?: $invoice->customer?->name ?: '-';

    $sourcePic = $sourceQuotation?->recipient_company_line2
        ?: ($sourceQuotation?->customer?->pics?->sortByDesc('is_primary')->first()?->name ?? null);

    $invoiceDate = $invoice->date ? IndonesianFormat::dateLong($invoice->date) : '-';
    $receiptDate = $invoice->updated_at ? IndonesianFormat::dateLong($invoice->updated_at) : $invoiceDate;

    $amountReceived = $isPaid ? (float) $invoice->grand_total : (float) $invoice->paid_total;
    $amountReceivedText = IndonesianFormat::rupiah($amountReceived);
    $amountTerbilang = Str::title(Terbilang::make($amountReceived) . ' Rupiah');

    $signatoryName = $tenantSettings['signatory_name'] ?? 'Authorized Signatory';
    $signatoryTitle = $tenantSettings['signatory_position'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Kwitansi - {{ $invoice->number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 14mm;
        }

        body {
            margin: 0;
            color: #1e293b;
            font-size: 11pt;
            line-height: 1.45;
            font-family: "Times New Roman", Times, serif;
        }

        .header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .logo {
            max-width: 150px;
            height: auto;
        }

        .title {
            text-align: center;
            font-size: 24pt;
            font-weight: 800;
            letter-spacing: .8px;
            margin: 0 0 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta .label {
            width: 180px;
            font-weight: 700;
        }

        .meta .colon {
            width: 16px;
            text-align: center;
        }

        .amount-box {
            border: 2px dashed #0f172a;
            padding: 10px 12px;
            margin: 14px 0 10px 0;
            background: #f8fafc;
        }

        .amount-box .label {
            font-size: 10pt;
            color: #475569;
        }

        .amount-box .value {
            font-size: 22pt;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }

        .terbilang {
            font-style: italic;
            color: #334155;
            margin-top: 4px;
        }

        .status-lunas {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 12px;
            border: 2px solid #166534;
            color: #166534;
            font-size: 11pt;
            font-weight: 900;
            border-radius: 4px;
            letter-spacing: .6px;
        }

        .signature-table {
            margin-top: 24px;
        }

        .signature-box {
            width: 240px;
            margin-left: auto;
            text-align: center;
        }

        .signature-rows td {
            padding: 0;
            text-align: center;
            vertical-align: top;
            line-height: 1.15;
        }

        .signature-rows .row-greeting td {
            padding-bottom: 2px;
        }

        .signature-rows .row-stamp td {
            padding: 1px 0 2px 0;
            line-height: 1;
        }

        .signature-rows .row-name td {
            padding-top: 2px;
        }

        .stamp-signature {
            width: 130px;
            max-height: none;
            margin: 2px auto 2px auto;
            display: block;
        }

        .signatory-name {
            font-weight: 700;
            text-decoration: underline;
            margin-top: 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <div style="font-size:16pt; font-weight:700;">{{ $tenant?->name ?? 'Perusahaan' }}</div>
                    <div>{{ $tenant?->address }}</div>
                    <div>{{ $tenant?->email }} @if ($tenant?->phone)
                            | {{ $tenant->phone }}
                        @endif
                    </div>
                </td>
                <td style="width:170px; text-align:right;">
                    @if (!empty($branding['logo_data_uri']))
                        <img src="{{ $branding['logo_data_uri'] }}" class="logo" alt="Logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="title">KWITANSI</div>

    <table class="meta">
        <tr>
            <td class="label">No. Kwitansi</td>
            <td class="colon">:</td>
            <td>KW-{{ $invoice->number }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="colon">:</td>
            <td>{{ $receiptDate }}</td>
        </tr>
        <tr>
            <td class="label">Diterima dari</td>
            <td class="colon">:</td>
            <td>
                {{ $recipientCompany }}
                @if ($sourcePic)
                    <br>{{ $sourcePic }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Untuk pembayaran</td>
            <td class="colon">:</td>
            <td>Pelunasan tagihan Invoice {{ $invoice->number }}</td>
        </tr>
        @if ($sourceQuotation?->number)
            <tr>
                <td class="label">Referensi Quotation</td>
                <td class="colon">:</td>
                <td>{{ $sourceQuotation->number }}</td>
            </tr>
        @endif
    </table>

    <div class="amount-box">
        <div class="label">Jumlah diterima</div>
        <div class="value">{{ $amountReceivedText }}</div>
        <div class="terbilang">Terbilang: {{ $amountTerbilang }}</div>
        @if ($isPaid)
            <div class="status-lunas">LUNAS</div>
        @endif
    </div>

    <table class="signature-table">
        <tr>
            <td style="width: 58%;"></td>
            <td style="width: 42%;">
                <div class="signature-box">
                    <table class="signature-rows">
                        <tr>
                            <td>{{ $sourceQuotation?->city ?? 'Jakarta' }}, {{ $receiptDate }}</td>
                        </tr>
                        <tr class="row-greeting">
                            <td>Hormat Kami</td>
                        </tr>
                        <tr class="row-stamp">
                            <td>
                                @if (!empty($branding['stamp_signature_data_uri']))
                                    <img class="stamp-signature" src="{{ $branding['stamp_signature_data_uri'] }}" alt="Cap dan tanda tangan">
                                @else
                                    <div style="height: 40px;"></div>
                                @endif
                            </td>
                        </tr>
                        <tr class="row-name">
                            <td class="signatory-name">{{ $signatoryName }}</td>
                        </tr>
                        @if ($signatoryTitle)
                            <tr>
                                <td>{{ $signatoryTitle }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>

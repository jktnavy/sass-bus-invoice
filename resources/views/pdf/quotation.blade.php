@php
    use App\Support\IndonesianFormat;
    use App\Support\Terbilang;
    use Illuminate\Support\Str;

    $city = $quotation->city ?: 'Jakarta';
    $letterDate = IndonesianFormat::dateLong($quotation->date);
    $usageDate = $quotation->usage_date ? IndonesianFormat::dateLong($quotation->usage_date) : '-';
    $fareAmount = IndonesianFormat::rupiah($quotation->fare_amount);
    $fareSpelled = Terbilang::make($quotation->fare_amount).' rupiah';
    $signatoryName = $quotation->signatory_name ?: ($branding['signatory_name'] ?? '-');
    $signatoryTitle = $quotation->signatory_title ?: ($branding['signatory_position'] ?? '-');
    $opening = $quotation->opening_paragraph ?: 'Kami dari PT. Sumber Tali Asih (STA Trans) dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:';
    $closing = $quotation->closing_paragraph ?: 'Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        @page {
            size: legal portrait;
            margin: 22mm 20mm 28mm 20mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            color: #111;
            line-height: 1.35;
        }
        .header-table, .detail-table, .sign-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td,
        .detail-table td,
        .sign-table td,
        .footer-table td {
            vertical-align: top;
            padding: 0;
        }
        .header-table .label {
            width: 70px;
        }
        .header-table .colon {
            width: 10px;
            text-align: center;
        }
        .header-table .right {
            text-align: right;
            width: 220px;
        }
        .row-gap {
            height: 4px;
        }
        .subject {
            font-weight: 700;
        }
        .paragraph {
            margin-top: 12px;
            text-align: justify;
        }
        .detail-table {
            margin-top: 8px;
        }
        .detail-table .left-label {
            width: 180px;
            font-weight: 700;
            padding: 2px 0;
        }
        .detail-table .colon {
            width: 12px;
            text-align: center;
            padding: 2px 0;
        }
        .detail-table .value {
            padding: 2px 0;
        }
        .closing {
            margin-top: 12px;
            text-align: justify;
        }
        .sign-table {
            margin-top: 12px;
        }
        .sign-cell {
            width: 45%;
            text-align: center;
        }
        .stamp-signature {
            width: 150px;
            height: auto;
            margin: 8px auto 6px auto;
            display: block;
        }
        .sign-name {
            font-weight: 700;
            text-decoration: underline;
        }
        .footer {
            position: fixed;
            left: 20mm;
            right: 20mm;
            bottom: 9mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            border-top: 1px solid #444;
            padding-top: 4px;
            color: #222;
        }
        .muted {
            color: #333;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="label">No</td>
            <td class="colon">:</td>
            <td>{{ $quotation->number }}</td>
            <td class="right">{{ $city }}, {{ $letterDate }}</td>
        </tr>
        <tr class="row-gap"><td colspan="4"></td></tr>
        <tr>
            <td colspan="3">
                Kepada<br>
                {{ $quotation->recipient_title_line1 ?: '-' }}<br>
                {{ $quotation->recipient_company_line2 ?: '-' }}
            </td>
            <td></td>
        </tr>
        <tr class="row-gap"><td colspan="4"></td></tr>
        <tr>
            <td class="label">Lampiran</td>
            <td class="colon">:</td>
            <td>{{ $quotation->attachment_text ?: '-' }}</td>
            <td></td>
        </tr>
        <tr>
            <td class="label">Perihal</td>
            <td class="colon">:</td>
            <td class="subject">{{ $quotation->subject_text ?: 'Penawaran Sewa Kendaraan' }}</td>
            <td></td>
        </tr>
    </table>

    <div class="paragraph">
        Dengan Hormat,<br>
        {{ $opening }}
    </div>

    <table class="detail-table">
        <tr>
            <td class="left-label">Jenis Kendaraan</td>
            <td class="colon">:</td>
            <td class="value">{{ $quotation->vehicle_type_text ?: '-' }}</td>
        </tr>
        <tr>
            <td class="left-label">Rute layanan</td>
            <td class="colon">:</td>
            <td class="value">{{ $quotation->service_route_text ?: '-' }}</td>
        </tr>
        <tr>
            <td class="left-label">Rincian tarif</td>
            <td class="colon">:</td>
            <td class="value">{{ $quotation->fare_text_label ?: 'Harga sewa bus' }} : {{ $fareAmount }} ( {{ Str::lower($fareSpelled) }} )</td>
        </tr>
        <tr>
            <td class="left-label">Tanggal Pemakaian</td>
            <td class="colon">:</td>
            <td class="value">{{ $usageDate }}</td>
        </tr>
        <tr>
            <td class="left-label">Harga sudah termasuk</td>
            <td class="colon">:</td>
            <td class="value">{{ $quotation->included_text ?: '-' }}</td>
        </tr>
        <tr>
            <td class="left-label">Fasilitas</td>
            <td class="colon">:</td>
            <td class="value">{{ $quotation->facilities_text ?: '-' }}</td>
        </tr>
        <tr>
            <td class="left-label">Metode pembayaran</td>
            <td class="colon">:</td>
            <td class="value">{!! nl2br(e($quotation->payment_method_text ?: '-')) !!}</td>
        </tr>
    </table>

    <div class="closing">{{ $closing }}</div>

    <table class="sign-table">
        <tr>
            <td style="width:55%"></td>
            <td class="sign-cell">
                {{ $city }}, {{ $letterDate }}<br>
                Hormat Kami
                @if(!empty($branding['stamp_signature_data_uri']))
                    <img class="stamp-signature" src="{{ $branding['stamp_signature_data_uri'] }}" alt="Cap & Tanda Tangan">
                @else
                    <div style="height:80px"></div>
                @endif
                <div class="sign-name">{{ $signatoryName }}</div>
                <div>{{ $signatoryTitle }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="muted">{{ $tenant?->address ?: '-' }}</td>
            </tr>
            <tr>
                <td class="muted">
                    Phone : {{ $tenant?->phone ?: '-' }}
                    @if(!empty($branding['company_website'])) | Website: {{ $branding['company_website'] }} @endif
                    @if(!empty($tenant?->email)) | Email: {{ $tenant->email }} @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>


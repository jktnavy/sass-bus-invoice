@php
    use App\Support\IndonesianFormat;

    $city = $quotation->city ?: 'Jakarta';
    $letterDate = IndonesianFormat::dateLong($quotation->date);
    $usageDate = $quotation->usage_date ? IndonesianFormat::dateLong($quotation->usage_date) : '-';

    $tenantSettings = is_array($tenant?->settings) ? $tenant->settings : [];

    $defaultOpening = 'Kami dari '.($tenant?->name ?: 'Perusahaan').' dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:';
    $opening = trim(strip_tags((string) ($quotation->opening_paragraph ?? '')));
    if ($opening === '' || mb_strlen($opening) < 20) {
        $opening = $defaultOpening;
    }

    $closing = trim(strip_tags((string) ($quotation->closing_paragraph ?? '')));
    if ($closing === '') {
        $closing = 'Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.';
    }

    $signatoryName = $tenantSettings['signatory_name'] ?? $quotation->signatory_name ?? '-';
    $signatoryTitle = $tenantSettings['signatory_position'] ?? $quotation->signatory_title ?? '-';

    $bank = $tenantSettings['bank_name'] ?? null;
    $holder = $tenantSettings['bank_account_holder'] ?? null;
    $number = $tenantSettings['bank_account_number'] ?? null;
    $paymentLines = array_filter([
        'Transfer Rekening',
        $bank ? "Bank: {$bank}" : null,
        $holder ? "Account Holder: {$holder}" : null,
        $number ? "Account Number: {$number}" : null,
        'Atau Cash',
    ]);

    $includedText = filled($quotation->included_text) ? $quotation->included_text : 'Tol, Parkir, BBM, Premi Driver, dan Kernet';
    $footerAddress = $tenant?->address ?: '-';
    $footerPhone = $tenant?->phone ?: '-';
    $footerWebsite = $branding['company_website'] ?? '-';
    $footerEmail = $tenant?->email ?: '-';

    $items = $quotation->items->sortBy('sort_order')->values();
    $recipientLine1 = $quotation->customer?->name ?: ($quotation->recipient_title_line1 ?: '-');
    $recipientLine2 = $quotation->recipient_company_line2 ?: '-';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        @page {
            size: legal portrait;
            margin: 10mm 18mm 10mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #111;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .letterhead {
            margin-top: -5mm;
            margin-bottom: 10px;
            text-align: left;
        }

        .company-logo {
            height: 250px;
            width: auto;
            display: block;
            object-fit: contain;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
        }

        .header-table td {
            font-size: 11pt;
        }

        .c-label {
            width: 80px;
        }

        .c-colon {
            width: 15px;
            text-align: center;
        }

        .subject {
            font-weight: bold;
        }

        .details {
            margin-top: 12px;
        }

        .details td {
            padding: 2px 0;
        }

        .details .label {
            width: 180px;
            font-weight: bold;
        }

        .items-table {
            margin-top: 10px;
            border: 1px solid #333;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 4px 6px;
            font-size: 10.5pt;
        }

        .items-table th {
            background: #f5f5f5;
            text-align: center;
            font-weight: bold;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .totals {
            margin-top: 8px;
            width: 52%;
            margin-left: auto;
        }

        .totals td {
            padding: 2px 0;
        }

        .totals .label {
            width: 60%;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            bottom: 5mm;
            left: 0;
            right: 0;
            width: 100%;
            border-top: 1px solid #000;
            padding-top: 5px;
            font-family: Arial, sans-serif;
            font-size: 8.5pt;
            color: #333;
            text-align: center;
        }

        .signature-container {
            margin-top: 30px;
            float: right;
            width: 250px;
            text-align: center;
        }

        .stamp-signature {
            width: 130px;
            margin: -15px auto -10px auto;
            display: block;
        }

        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    @if (!empty($branding['logo_data_uri']))
        <div class="letterhead">
            <img class="company-logo" src="{{ $branding['logo_data_uri'] }}" alt="Logo">
        </div>
    @endif

    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <table>
                    <tr>
                        <td class="c-label">No</td>
                        <td class="c-colon">:</td>
                        <td>{{ $quotation->number }}</td>
                    </tr>
                    <tr>
                        <td class="c-label">Lampiran</td>
                        <td class="c-colon">:</td>
                        <td>{{ $quotation->attachment_text ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="c-label">Perihal</td>
                        <td class="c-colon">:</td>
                        <td class="subject">{{ $quotation->subject_text ?: 'Penawaran Sewa Kendaraan' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align: left; padding-left: 20px;">
                {{ $city }}, {{ $letterDate }}<br>
                Kepada<br>
                <span style="font-weight: bold;">{{ $recipientLine1 }}</span><br>
                <span style="font-weight: bold;">{{ $recipientLine2 }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px;">Dengan Hormat,</div>
    <div style="text-align: justify; margin-top: 5px;">
        {!! nl2br(e($opening)) !!}
    </div>

    <table class="details">
        <tr>
            <td class="label">Tanggal Pemakaian</td>
            <td class="c-colon">:</td>
            <td>{{ $usageDate }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 46%;">Item</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 18%;">Harga</th>
                <th style="width: 20%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->name }}
                        @if (filled($item->description))
                            <br><span style="font-size: 9.5pt; color: #444;">{{ $item->description }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ rtrim(rtrim(number_format((float) $item->qty, 2, '.', ''), '0'), '.') }}</td>
                    <td class="text-right">{{ IndonesianFormat::rupiah((float) $item->price) }}</td>
                    <td class="text-right">{{ IndonesianFormat::rupiah((float) $item->line_total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada item quotation.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Sub Total</td>
            <td class="c-colon">:</td>
            <td style="text-align: right;">{{ IndonesianFormat::rupiah((float) $quotation->sub_total) }}</td>
        </tr>
        <tr>
            <td class="label">Total Diskon</td>
            <td class="c-colon">:</td>
            <td style="text-align: right;">{{ IndonesianFormat::rupiah((float) $quotation->discount_total) }}</td>
        </tr>
        <tr>
            <td class="label">Total Pajak</td>
            <td class="c-colon">:</td>
            <td style="text-align: right;">{{ IndonesianFormat::rupiah((float) $quotation->tax_total) }}</td>
        </tr>
        <tr>
            <td class="label">Grand Total</td>
            <td class="c-colon">:</td>
            <td style="text-align: right; font-weight: bold;">{{ IndonesianFormat::rupiah((float) $quotation->grand_total) }}</td>
        </tr>
    </table>

    <table class="details" style="margin-top: 10px;">
        <tr>
            <td class="label">Harga sudah termasuk</td>
            <td class="c-colon">:</td>
            <td>{!! nl2br(e($includedText)) !!}</td>
        </tr>
        <tr>
            <td class="label">Fasilitas</td>
            <td class="c-colon">:</td>
            <td>{!! nl2br(e($quotation->facilities_text ?: '-')) !!}</td>
        </tr>
        <tr>
            <td class="label">Metode pembayaran</td>
            <td class="c-colon">:</td>
            <td>{!! nl2br(e(implode("\n", $paymentLines))) !!}</td>
        </tr>
    </table>

    <div style="margin-top: 15px; text-align: justify;">
        {!! nl2br(e($closing)) !!}
    </div>

    <div class="signature-container">
        {{ $city }}, {{ $letterDate }}<br>
        Hormat Kami<br>

        @if (!empty($branding['stamp_signature_data_uri']))
            <img class="stamp-signature" src="{{ $branding['stamp_signature_data_uri'] }}" alt="Cap dan tanda tangan">
        @else
            <div style="height: 60px;"></div>
        @endif

        <div class="sign-name">{{ $signatoryName }}</div>
        <div>{{ $signatoryTitle }}</div>
    </div>

    <div class="clear"></div>

    <div class="footer">
        <div style="width: 100%;">
            {{ $footerAddress }}<br>
            Phone : {{ $footerPhone }}<br>
            Website: {{ $footerWebsite }}. Email: {{ $footerEmail }}
        </div>
    </div>
</body>

</html>

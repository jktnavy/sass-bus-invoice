@php
    use App\Support\IndonesianFormat;
    use App\Support\Terbilang;
    use Illuminate\Support\Str;

    $city = $quotation->city ?: 'Jakarta';
    $letterDate = IndonesianFormat::dateLong($quotation->date);
    $usageDate = $quotation->usage_date ? IndonesianFormat::dateLong($quotation->usage_date) : '-';
    $fareAmount = IndonesianFormat::rupiah($quotation->fare_amount);
    $fareSpelled = Str::lower(Terbilang::make($quotation->fare_amount) . ' rupiah');

    $signatoryName = $quotation->signatory_name ?: $branding['signatory_name'] ?? '-';
    $signatoryTitle = $quotation->signatory_title ?: $branding['signatory_position'] ?? '-';

    $opening = trim(strip_tags((string) ($quotation->opening_paragraph ?? '')));
    if ($opening === '' || mb_strlen($opening) < 20) {
        $opening = 'Kami dari PT. Sumber Tali Asih (STA Trans) dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:';
    }

    $footerAddress = $tenant?->address ?: 'Jl. Akses Tol Cimanggis No. 73 Leuwinanggung, Tapos , Depok 16456';
    $footerPhone = $tenant?->phone ?: '081287163189';
    $footerWebsite = $branding['company_website'] ?? 'https://statransport.co.id/';
    $footerEmail = $tenant?->email ?: 'suhendi.sta@gmail.com';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        /* Pengaturan Kertas */
        @page {
            size: legal portrait;
            /* Margin diperkecil agar logo bisa sangat dekat ke atas dan footer ke bawah */
            margin: 10mm 18mm 10mm 18mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            /* Menyesuaikan agar proporsional di Legal */
            color: #111;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        /* Logo Section - Mepet ke atas dan Ukuran Besar */
        .letterhead {
            margin-top: -5mm;
            /* Menaikkan logo lebih tinggi lagi melewati margin standar */
            margin-bottom: 10px;
            text-align: left;
        }

        .company-logo {
            height: 250px;
            /* Sesuai permintaan Anda */
            width: auto;
            display: block;
            object-fit: contain;
            /* Menjaga rasio logo agar tidak gepeng */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 1px 0;
        }

        /* Header Info (No & Kepada) */
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

        /* Details Table */
        .details {
            margin-top: 15px;
        }

        .details td {
            padding: 2px 0;
        }

        .details .label {
            width: 180px;
            font-weight: bold;
        }

        /* Footer - Posisi Fixed Sangat Bawah */
        .footer {
            position: fixed;
            bottom: 5mm;
            /* Mendekati tepi bawah kertas */
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
                        <td class="subject">Penawaran Sewa Kendaraan</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; text-align: left; padding-left: 20px;">
                {{ $city }}, {{ $letterDate }}<br>
                Kepada<br>
                <span
                    style="font-weight: bold;">{{ $quotation->recipient_title_line1 ?: 'Ketua Bidang Perempuan' }}</span><br>
                <span style="font-weight: bold;">{{ $quotation->recipient_company_line2 ?: 'DPP Partai Golkar' }}</span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px;">Dengan Hormat,</div>
    <div style="text-align: justify; margin-top: 5px;">
        {!! nl2br(e($opening)) !!}
    </div>

    <table class="details">
        <tr>
            <td class="label">Jenis Kendaraan</td>
            <td class="c-colon">:</td>
            <td>{!! nl2br(e($quotation->vehicle_type_text)) !!}</td>
        </tr>
        <tr>
            <td class="label">Rute layanan</td>
            <td class="c-colon">:</td>
            <td>{!! nl2br(e($quotation->service_route_text)) !!}</td>
        </tr>
        <tr>
            <td class="label">Rincian tarif</td>
            <td class="c-colon">:</td>
            <td>
                {{ $quotation->fare_text_label ?: 'Harga sewa bus' }} : {{ $fareAmount }} ( {{ $fareSpelled }}
                )<br>
                : {{ $fareAmount }} ( {{ $fareSpelled }} )
            </td>
        </tr>
        <tr>
            <td class="label">Tanggal Pemakaian</td>
            <td class="c-colon">:</td>
            <td>{{ $usageDate }}</td>
        </tr>
        <tr>
            <td class="label">Harga sudah termasuk</td>
            <td class="c-colon">:</td>
            <td>{{ $quotation->included_text ?: 'Parkir, Tol, Bahan bakar, premi sopir, dan kernet. ( All-Ins )' }}
            </td>
        </tr>
        <tr>
            <td class="label">Fasilitas</td>
            <td class="c-colon">:</td>
            <td>{{ $quotation->facilities_text ?: 'AC, TV, DVD, karaoke' }}</td>
        </tr>
        <tr>
            <td class="label">Metode pembayaran</td>
            <td class="c-colon">:</td>
            <td>
                Transfer Rekening<br>
                Bank: BCA<br>
                Account Holder: Suhendi<br>
                Account Number: 406 061 5352<br>
                Atau Cash
            </td>
        </tr>
    </table>

    <div style="margin-top: 15px; text-align: justify;">
        Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang
        Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.
    </div>

    <div class="signature-container">
        {{ $city }}, {{ $letterDate }}<br>
        Hormat Kami<br>

        @if (!empty($branding['stamp_signature_data_uri']))
            <img class="stamp-signature" src="{{ $branding['stamp_signature_data_uri'] }}">
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

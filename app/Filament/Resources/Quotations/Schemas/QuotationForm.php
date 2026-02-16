<?php

namespace App\Filament\Resources\Quotations\Schemas;

use App\Models\Customer;
use App\Models\CustomerPic;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\Tax;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Header Quotation')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('number')
                            ->label('Nomor Quotation')
                            ->helperText('Nomor otomatis dari Number Sequence.')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(4),
                        Select::make('status')->options([
                            0 => 'Draft',
                            1 => 'Sent',
                            2 => 'Accepted',
                            3 => 'Rejected',
                            4 => 'Void',
                        ])->required()->default(0)->columnSpan(4),
                        TextInput::make('currency')
                            ->label('Mata Uang')
                            ->default('IDR')
                            ->required()
                            ->maxLength(3)
                            ->placeholder('IDR')
                            ->columnSpan(4),
                        DatePicker::make('date')
                            ->label('Tanggal Quotation')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if (! $state) {
                                    return;
                                }

                                $set('valid_until', Carbon::parse($state)->addDays(7)->toDateString());
                            })
                            ->columnSpan(4),
                        DatePicker::make('valid_until')
                            ->label('Berlaku Sampai')
                            ->helperText('Otomatis +7 hari dari Tanggal Quotation, namun tetap bisa diubah manual.')
                            ->columnSpan(4),
                        TextInput::make('city')
                            ->label('Kota Surat')
                            ->default('Jakarta')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(4),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::query()->orderBy('name')->get()->mapWithKeys(fn (Customer $customer) => [
                                $customer->id => trim(($customer->code ? "{$customer->code} - " : '').$customer->name),
                            ])->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                $set('customer_pic_id', null);

                                if (! $state) {
                                    return;
                                }

                                $customer = Customer::query()->find($state);

                                if ($customer && blank($get('recipient_company_line2'))) {
                                    $set('recipient_company_line2', $customer->name);
                                }
                            })
                            ->helperText(function ($state): string {
                                if (! $state) {
                                    return 'Pilih customer untuk menampilkan informasi PIC.';
                                }

                                $customer = Customer::query()->find($state);

                                if (! $customer) {
                                    return 'Customer tidak ditemukan.';
                                }

                                $pics = CustomerPic::query()->where('customer_id', $customer->id)->orderByDesc('is_primary')->orderBy('name')->get();

                                if ($pics->isEmpty()) {
                                    return 'Belum ada PIC. Tambahkan di Customer > relation PIC.';
                                }

                                $primary = $pics->first();

                                return sprintf(
                                    'PIC utama: %s | Phone: %s | Total PIC: %d',
                                    $primary?->name ?: '-',
                                    $primary?->phone ?: '-',
                                    $pics->count()
                                );
                            })
                            ->required()
                            ->columnSpan(4),
                        Select::make('customer_pic_id')
                            ->label('PIC Customer')
                            ->dehydrated(false)
                            ->searchable()
                            ->live()
                            ->options(function (callable $get): array {
                                $customerId = $get('customer_id');

                                if (! $customerId) {
                                    return [];
                                }

                                return CustomerPic::query()
                                    ->where('customer_id', $customerId)
                                    ->orderByDesc('is_primary')
                                    ->orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn (CustomerPic $pic) => [
                                        $pic->id => trim($pic->name.($pic->position ? " ({$pic->position})" : '').($pic->phone ? " - {$pic->phone}" : '')),
                                    ])
                                    ->all();
                            })
                            ->helperText('Pilih PIC untuk mengisi otomatis blok penerima pada surat.')
                            ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                if (! $state) {
                                    return;
                                }

                                $pic = CustomerPic::query()->find($state);

                                if (! $pic) {
                                    return;
                                }

                                $set('recipient_title_line1', trim($pic->name.($pic->position ? " - {$pic->position}" : '')));

                                $customer = Customer::query()->find($get('customer_id'));

                                if ($customer && blank($get('recipient_company_line2'))) {
                                    $set('recipient_company_line2', $customer->name);
                                }
                            })
                            ->columnSpan(4),
                        Textarea::make('notes')->label('Catatan')->columnSpanFull(),
                    ]),

                Section::make('Format Surat Penawaran')
                    ->description('Isi data surat sesuai format resmi quotation.')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('recipient_title_line1')
                            ->label('Penerima Baris 1')
                            ->placeholder('Contoh: Ketua Bidang Perempuan')
                            ->columnSpan(6),
                        TextInput::make('recipient_company_line2')
                            ->label('Penerima Baris 2 (Instansi)')
                            ->placeholder('Contoh: DPP Partai Golkar')
                            ->columnSpan(6),
                        TextInput::make('attachment_text')
                            ->label('Lampiran')
                            ->default('-')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('subject_text')
                            ->label('Perihal')
                            ->default('Penawaran Sewa Kendaraan')
                            ->required()
                            ->columnSpan(8),
                        RichEditor::make('opening_paragraph')
                            ->label('Paragraf Pembuka')
                            ->default(fn (): string => self::defaultOpeningParagraph())
                            ->columnSpanFull(),
                        TextInput::make('vehicle_type_text')
                            ->label('Jenis Kendaraan')
                            ->placeholder('Contoh: Bus Besar AC kapasitas 59 seats.')
                            ->columnSpan(6),
                        TextInput::make('service_route_text')
                            ->label('Rute Layanan')
                            ->placeholder('Contoh: DPP Golkar - Drop Hotel 3G Bogor')
                            ->columnSpan(6),
                        TextInput::make('fare_text_label')
                            ->label('Label Rincian Tarif')
                            ->default('Harga sewa bus')
                            ->columnSpan(6),
                        TextInput::make('fare_amount')
                            ->label('Nominal Tarif')
                            ->numeric()
                            ->default(0)
                            ->helperText('Format rupiah otomatis di PDF. Contoh input: 2650000')
                            ->columnSpan(3),
                        DatePicker::make('usage_date')
                            ->label('Tanggal Pemakaian')
                            ->columnSpan(3),
                        Textarea::make('included_text')
                            ->label('Harga Sudah Termasuk')
                            ->rows(3)
                            ->placeholder('Contoh: Parkir, Tol, Bahan bakar, premi sopir, dan kernet. ( All-Ins )')
                            ->columnSpan(6),
                        Textarea::make('facilities_text')
                            ->label('Fasilitas')
                            ->rows(3)
                            ->placeholder('Contoh: AC, TV, DVD, karaoke')
                            ->columnSpan(6),
                        Textarea::make('payment_method_text')
                            ->label('Metode Pembayaran')
                            ->rows(3)
                            ->default(fn (): string => self::defaultPaymentMethodText())
                            ->afterStateHydrated(function ($state, callable $set): void {
                                if (filled($state)) {
                                    return;
                                }

                                $set('payment_method_text', self::defaultPaymentMethodText());
                            })
                            ->helperText('Boleh multi-line. Contoh: Transfer Rekening [bank] lalu baris kedua Atau Cash.')
                            ->columnSpan(6),
                        TextInput::make('signatory_name')
                            ->label('Nama Penandatangan')
                            ->default(function (): ?string {
                                $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
                                $tenantSignatory = is_array($tenant?->settings) ? ($tenant->settings['signatory_name'] ?? null) : null;

                                return $tenantSignatory ?: auth()->user()?->name;
                            })
                            ->columnSpan(3),
                        TextInput::make('signatory_title')
                            ->label('Jabatan Penandatangan')
                            ->default(function (): ?string {
                                $tenant = Tenant::query()->find(auth()->user()?->tenant_id);

                                return is_array($tenant?->settings) ? ($tenant->settings['signatory_position'] ?? null) : null;
                            })
                            ->columnSpan(3),
                        RichEditor::make('closing_paragraph')
                            ->label('Paragraf Penutup')
                            ->default('Demikian surat penawaran ini kami sampaikan, besar harapan kami agar dapat bekerja sama dengan instansi yang Bapak / Ibu pimpin. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Item Quotation')
                    ->description('Tambahkan item satu per satu. Setiap baris merepresentasikan 1 baris dokumen.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->columnSpanFull()
                            ->defaultItems(1)
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                Select::make('item_id')
                                    ->label('Item Master')
                                    ->helperText('Pilih item master untuk isi otomatis nama, UOM, harga default.')
                                    ->options(fn () => Item::query()->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->columnSpan(12)
                                    ->afterStateUpdated(function ($state, callable $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $item = Item::query()->find($state);

                                        if (! $item) {
                                            return;
                                        }

                                        $set('name', $item->name);
                                        $set('uom', $item->uom);
                                        $set('price', (float) $item->default_price);
                                        $set('tax_id', $item->tax_id);
                                    }),
                                TextInput::make('name')
                                    ->label('Nama Item di Dokumen')
                                    ->placeholder('Contoh: Sewa Bus Pariwisata 2 Hari')
                                    ->helperText('Bisa disesuaikan untuk nama komersial di quotation.')
                                    ->required()
                                    ->columnSpan(6),
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Contoh: Termasuk BBM & driver, tidak termasuk tol')
                                    ->columnSpan(6),
                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->helperText('Jumlah unit/trip/hari. Contoh: 2')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('uom')
                                    ->label('Satuan (UOM)')
                                    ->required()
                                    ->default('unit')
                                    ->placeholder('trip / hari / unit')
                                    ->columnSpan(2),
                                TextInput::make('price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->helperText('Contoh: 3750000')
                                    ->columnSpan(3),
                                TextInput::make('discount')
                                    ->label('Diskon Baris')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Nominal diskon, bukan persen. Contoh: 250000')
                                    ->columnSpan(3),
                                Select::make('tax_id')
                                    ->label('Pajak')
                                    ->options(fn () => Tax::query()->where('is_active', 1)->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->columnSpan(2),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Urutan tampil di PDF.')
                                    ->columnSpan(2),
                            ])
                            ->columns(12),
                    ]),

                Section::make('Ringkasan Nilai')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('sub_total')->label('Sub Total')->numeric()->disabled()->dehydrated(false)->columnSpan(3),
                        TextInput::make('discount_total')->label('Total Diskon')->numeric()->disabled()->dehydrated(false)->columnSpan(3),
                        TextInput::make('tax_total')->label('Total Pajak')->numeric()->disabled()->dehydrated(false)->columnSpan(3),
                        TextInput::make('grand_total')->label('Grand Total')->numeric()->disabled()->dehydrated(false)->columnSpan(3),
                    ]),
            ]);
    }

    private static function defaultOpeningParagraph(): string
    {
        $tenantName = Tenant::query()->find(auth()->user()?->tenant_id)?->name ?? 'Perusahaan';

        return "Kami dari {$tenantName} dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:";
    }

    private static function defaultPaymentMethodText(): string
    {
        $tenant = Tenant::query()->find(auth()->user()?->tenant_id);
        $settings = is_array($tenant?->settings) ? $tenant->settings : [];

        $bank = $settings['bank_name'] ?? null;
        $holder = $settings['bank_account_holder'] ?? null;
        $number = $settings['bank_account_number'] ?? null;

        if ($bank || $holder || $number) {
            return trim(implode("\n", array_filter([
                'Transfer Rekening',
                $bank ? "Bank: {$bank}" : null,
                $holder ? "Account Holder: {$holder}" : null,
                $number ? "Account Number: {$number}" : null,
                'Atau Cash',
            ])));
        }

        return "Transfer Rekening\nAtau Cash";
    }
}

<?php

namespace App\Filament\Resources\Quotations\Schemas;

use App\Models\Customer;
use App\Models\CustomerPic;
use App\Models\Item;
use App\Models\Tenant;
use App\Models\Tax;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

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
                        DatePicker::make('usage_date')
                            ->label('Tanggal Awal Pemakaian')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if (! $state) {
                                    return;
                                }

                                // Saat tanggal awal berubah, tanggal akhir otomatis mengikuti.
                                $set('usage_end_date', $state);
                            })
                            ->helperText('Tanggal mulai pelaksanaan layanan/sewa.')
                            ->columnSpan(4),
                        DatePicker::make('usage_end_date')
                            ->label('Tanggal Akhir Pemakaian')
                            ->live()
                            ->rule('after_or_equal:usage_date')
                            ->helperText('Tanggal selesai pelaksanaan layanan/sewa.')
                            ->columnSpan(4),
                        Placeholder::make('usage_duration_days')
                            ->label('Durasi Pemakaian')
                            ->content(function (callable $get): HtmlString {
                                $start = $get('usage_date');
                                $end = $get('usage_end_date');

                                if (! $start || ! $end) {
                                    return new HtmlString('<span style="color:#6b7280;">Isi tanggal awal dan akhir untuk menghitung durasi.</span>');
                                }

                                $startDate = Carbon::parse($start)->startOfDay();
                                $endDate = Carbon::parse($end)->startOfDay();

                                if ($endDate->lt($startDate)) {
                                    return new HtmlString('<span style="color:#dc2626; font-weight:600;">Tanggal akhir tidak boleh sebelum tanggal awal.</span>');
                                }

                                $days = $startDate->diffInDays($endDate) + 1;

                                return new HtmlString('<strong>'.$days.' hari</strong>');
                            })
                            ->columnSpan(4),
                        TextInput::make('city')
                            ->label('Kota Surat')
                            ->default('Jakarta')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(4),
                        TextInput::make('subject_text')
                            ->label('Perihal')
                            ->default('Penawaran Sewa Kendaraan')
                            ->required()
                            ->columnSpan(8),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn (callable $get) => Customer::query()
                                ->where(function ($query) use ($get): void {
                                    $query->where('is_active', 1);

                                    if ($get('customer_id')) {
                                        $query->orWhere('id', $get('customer_id'));
                                    }
                                })
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Customer $customer) => [
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

                                if ($customer) {
                                    // Baris 1 penerima = nama instansi (customer)
                                    $set('recipient_title_line1', $customer->name);
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

                                // Baris 2 penerima = nama PIC customer
                                $set('recipient_company_line2', trim($pic->name.($pic->position ? " - {$pic->position}" : '')));
                            })
                            ->columnSpan(4),
                        Hidden::make('recipient_title_line1'),
                        Hidden::make('recipient_company_line2'),
                        Textarea::make('notes')->label('Catatan')->columnSpan(4),
                    ]),

                Section::make('Ketentuan Layanan')
                    ->description('Pengaturan tambahan sebelum item quotation.')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Textarea::make('included_text')
                            ->label('Harga Sudah Termasuk')
                            ->rows(3)
                            ->default(self::allInIncludedText())
                            ->placeholder('Contoh: Tol, Parkir, BBM, Premi Driver, dan Kernet')
                            ->helperText('Default otomatis terisi. Jika dikosongkan, baris ini tidak tampil di PDF.')
                            ->columnSpan(6),
                        Textarea::make('excluded_text')
                            ->label('Harga Tidak Termasuk')
                            ->rows(3)
                            ->placeholder('Contoh: Tips kru, penginapan kru, biaya masuk objek wisata')
                            ->helperText('Jika diisi, PDF akan menampilkan baris "Harga Tidak Termasuk".')
                            ->columnSpan(6),
                        Textarea::make('facilities_text')
                            ->label('Informasi Fasilitas')
                            ->rows(3)
                            ->default('AC, TV, Karaoke, Reclining Seats')
                            ->placeholder('Contoh: AC, TV, Karaoke, Reclining Seats')
                            ->helperText('Default fasilitas sudah terisi, namun tetap bisa diubah.')
                            ->columnSpan(12),
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

    public static function defaultOpeningParagraph(): string
    {
        $tenantName = Tenant::query()->find(auth()->user()?->tenant_id)?->name ?? 'Perusahaan';

        return "Kami dari {$tenantName} dengan segala kerendahan hati ingin menyampaikan niat baik kami untuk mendukung kelancaran kegiatan Bapak/Ibu. Kami dengan ini mengajukan penawaran harga sewa bus pariwisata dengan rincian sebagai berikut:";
    }

    public static function defaultPaymentMethodText(): string
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

    public static function allInIncludedText(): string
    {
        return 'Tol, Parkir, BBM, Premi Driver, dan Kernet';
    }
}

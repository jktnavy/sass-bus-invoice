<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Customer;
use App\Models\Invoice;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Pembayaran')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('number')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Nomor otomatis dibuat saat data disimpan.')
                            ->columnSpan(4),
                        Select::make('status')
                            ->options([0 => 'Draft', 1 => 'Posted', 2 => 'Reversed'])
                            ->required()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Status berubah melalui aksi Post/Reverse di halaman edit.')
                            ->columnSpan(4),
                        DatePicker::make('date')
                            ->required()
                            ->disabled(fn ($record): bool => (int) ($record?->status ?? 0) !== 0)
                            ->columnSpan(4),
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
                                ])
                                ->all())
                            ->required()
                            ->searchable()
                            ->live()
                            ->disabled(fn ($record): bool => (int) ($record?->status ?? 0) !== 0)
                            ->helperText(function ($state): string {
                                if (! $state) {
                                    return 'Pilih customer untuk menampilkan PIC dan daftar invoice yang masih outstanding.';
                                }

                                $customer = Customer::query()->with('pics')->find($state);
                                $primaryPic = $customer?->pics?->sortByDesc('is_primary')->first();

                                if (! $primaryPic) {
                                    return 'Belum ada PIC customer. Tambahkan pada modul Customer > PIC.';
                                }

                                return sprintf(
                                    'PIC utama: %s%s%s',
                                    $primaryPic->name,
                                    $primaryPic->position ? " ({$primaryPic->position})" : '',
                                    $primaryPic->phone ? " | {$primaryPic->phone}" : ''
                                );
                            })
                            ->columnSpan(6),
                        Select::make('method')->options([
                            'cash' => 'Cash',
                            'transfer' => 'Transfer',
                            'va' => 'VA',
                            'other' => 'Other',
                        ])->required()->disabled(fn ($record): bool => (int) ($record?->status ?? 0) !== 0)->columnSpan(3),
                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->disabled(fn ($record): bool => (int) ($record?->status ?? 0) !== 0)
                            ->helperText('Nominal hanya bisa diubah saat status Draft.')
                            ->columnSpan(3),
                        TextInput::make('unapplied_amount')->numeric()->disabled()->dehydrated(false)->columnSpan(4),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
                Section::make('Arah Pembayaran')
                    ->description('Payment dapat dialokasikan ke satu/lebih invoice pada tab Allocations setelah status Posted.')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Placeholder::make('workflow_info')
                            ->label('Workflow')
                            ->content(new HtmlString('1) Simpan Payment sebagai <b>Draft</b>.<br>2) Klik aksi <b>Post</b> di halaman edit.<br>3) Buka tab <b>Allocations</b> untuk memilih invoice dan nominal alokasi.'))
                            ->columnSpan(4),
                        Placeholder::make('customer_pic_info')
                            ->label('Informasi PIC Customer')
                            ->content(function (callable $get): string {
                                $customerId = $get('customer_id');

                                if (! $customerId) {
                                    return '-';
                                }

                                $customer = Customer::query()->with('pics')->find($customerId);
                                $pics = $customer?->pics?->sortByDesc('is_primary');

                                if (! $pics || $pics->isEmpty()) {
                                    return 'PIC belum tersedia.';
                                }

                                return $pics->take(3)->map(function ($pic): string {
                                    return trim($pic->name.($pic->position ? " ({$pic->position})" : '').($pic->phone ? " - {$pic->phone}" : ''));
                                })->implode("\n");
                            })
                            ->columnSpan(4),
                        Placeholder::make('open_invoice_info')
                            ->label('Invoice Outstanding Customer')
                            ->content(function (callable $get): HtmlString {
                                $customerId = $get('customer_id');

                                if (! $customerId) {
                                    return new HtmlString('<span style="color:#6b7280;">Pilih customer terlebih dahulu.</span>');
                                }

                                $invoices = Invoice::query()
                                    ->where('customer_id', $customerId)
                                    ->whereIn('status', [1, 2])
                                    ->where('balance_total', '>', 0)
                                    ->orderBy('due_date')
                                    ->get();

                                if ($invoices->isEmpty()) {
                                    return new HtmlString('<span style="color:#6b7280;">Tidak ada invoice outstanding.</span>');
                                }

                                $rows = $invoices->map(function (Invoice $invoice): string {
                                    $dueDate = $invoice->due_date?->format('Y-m-d') ?? '-';
                                    $balance = number_format((float) $invoice->balance_total, 2, ',', '.');

                                    return "<tr><td style=\"padding:2px 4px; border-bottom:1px solid #e5e7eb;\">{$invoice->number}</td><td style=\"padding:2px 4px; border-bottom:1px solid #e5e7eb;\">{$dueDate}</td><td style=\"padding:2px 4px; border-bottom:1px solid #e5e7eb; text-align:right;\">{$balance}</td></tr>";
                                })->implode('');

                                $table = '<table style="width:100%; border-collapse:collapse; font-size:12px;"><thead><tr><th style="text-align:left; padding:2px 4px;">Invoice</th><th style="text-align:left; padding:2px 4px;">Due</th><th style="text-align:right; padding:2px 4px;">Balance</th></tr></thead><tbody>'.$rows.'</tbody></table>';

                                return new HtmlString($table);
                            })
                            ->columnSpan(4),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Quotation;
use App\Models\Tax;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Placeholder::make('void_notice')
                    ->hiddenLabel()
                    ->content(new HtmlString('<div class="rounded-xl border border-danger-300 bg-danger-50 p-4 text-lg font-bold text-danger-700">Invoice ini sudah berstatus VOID. Data invoice tidak dapat diubah lagi.</div>'))
                    ->visible(fn ($record): bool => (int) ($record?->status ?? -1) === 4)
                    ->columnSpanFull(),

                Section::make('Header Invoice')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('number')
                            ->label('Nomor Invoice')
                            ->helperText('Nomor otomatis dari Number Sequence.')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(4),
                        Select::make('source_quotation_id')
                            ->label('Ambil dari Quotation')
                            ->helperText('Pilih quotation untuk menarik item, lalu edit harga jika ada negosiasi.')
                            ->options(fn () => Quotation::query()->orderByDesc('date')->pluck('number', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->columnSpan(8)
                            ->afterStateUpdated(function ($state, callable $set): void {
                                if (! $state) {
                                    return;
                                }

                                $quotation = Quotation::query()->with('items')->find($state);

                                if (! $quotation) {
                                    return;
                                }

                                $set('customer_id', $quotation->customer_id);
                                $set('currency', $quotation->currency);
                                $set('notes', $quotation->notes);
                                $set('items', $quotation->items->map(fn ($item): array => [
                                    'item_id' => $item->item_id,
                                    'name' => $item->name,
                                    'description' => $item->description,
                                    'qty' => (float) $item->qty,
                                    'uom' => $item->uom,
                                    'price' => (float) $item->price,
                                    'discount' => (float) $item->discount,
                                    'tax_id' => $item->tax_id,
                                    'sort_order' => $item->sort_order,
                                ])->values()->all());
                            }),
                        Select::make('status')->options([
                            0 => 'Draft',
                            1 => 'Sent',
                            2 => 'Partial',
                            3 => 'Paid',
                            4 => 'Void',
                        ])->required()->default(0)->columnSpan(4),
                        DatePicker::make('date')->label('Tanggal Invoice')->required()->columnSpan(4),
                        DatePicker::make('due_date')->label('Jatuh Tempo')->required()->columnSpan(4),
                        Select::make('customer_id')
                            ->options(fn (callable $get) => Customer::query()
                                ->where(function ($query) use ($get): void {
                                    $query->where('is_active', 1);

                                    if ($get('customer_id')) {
                                        $query->orWhere('id', $get('customer_id'));
                                    }
                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->required()
                            ->searchable()
                            ->columnSpan(4),
                        TextInput::make('currency')->required()->default('IDR')->columnSpan(4),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),

                Section::make('Item Invoice')
                    ->description('Item dapat diubah untuk penyesuaian hasil negosiasi sebelum invoice disimpan.')
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
                                    ->helperText('Pilih item master untuk auto-fill data dasar.')
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
                                    ->label('Nama Item di Invoice')
                                    ->placeholder('Contoh: Sewa Bus Pariwisata 2 Hari')
                                    ->helperText('Nama item yang tampil di invoice/PDF.')
                                    ->required()
                                    ->columnSpan(6),
                                TextInput::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Contoh: Include driver, exclude tol & parkir')
                                    ->columnSpan(6),
                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->helperText('Contoh: 2')
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
                                    ->helperText('Nominal per satuan. Contoh: 3750000')
                                    ->columnSpan(3),
                                TextInput::make('discount')
                                    ->label('Diskon Baris')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Nominal diskon. Contoh: 250000')
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
                                    ->helperText('Urutan item di dokumen.')
                                    ->columnSpan(2),
                            ])
                            ->columns(12),
                    ]),

                Section::make('Ringkasan Nilai')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('grand_total')->label('Grand Total')->numeric()->disabled()->dehydrated(false)->columnSpan(4),
                        TextInput::make('paid_total')->label('Total Terbayar')->numeric()->disabled()->dehydrated(false)->columnSpan(4),
                        TextInput::make('balance_total')->label('Sisa Tagihan')->numeric()->disabled()->dehydrated(false)->columnSpan(4),
                    ]),
            ]);
    }
}

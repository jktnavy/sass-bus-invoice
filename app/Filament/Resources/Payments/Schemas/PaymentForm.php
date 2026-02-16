<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Models\Customer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        TextInput::make('number')->disabled()->dehydrated()->required()->columnSpan(4),
                        Select::make('status')->options([0 => 'Draft', 1 => 'Posted', 2 => 'Reversed'])->required()->default(0)->columnSpan(4),
                        DatePicker::make('date')->required()->columnSpan(4),
                        Select::make('customer_id')->options(fn () => Customer::query()->pluck('name', 'id')->all())->required()->searchable()->columnSpan(6),
                        Select::make('method')->options([
                            'cash' => 'Cash',
                            'transfer' => 'Transfer',
                            'va' => 'VA',
                            'other' => 'Other',
                        ])->required()->columnSpan(3),
                        TextInput::make('amount')->required()->numeric()->columnSpan(3),
                        TextInput::make('unapplied_amount')->numeric()->disabled()->dehydrated(false)->columnSpan(4),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}

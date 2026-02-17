<?php

namespace App\Filament\Resources\NumberSequences\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NumberSequenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Konfigurasi Penomoran')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Select::make('doc_type')->options([
                            'quotation' => 'Quotation',
                            'invoice' => 'Invoice',
                            'receipt' => 'Receipt',
                            'payment' => 'Payment',
                        ])->required()->columnSpan(4),
                        TextInput::make('prefix')->required()->maxLength(30)->columnSpan(4),
                        TextInput::make('suffix')->maxLength(30)->columnSpan(4),
                        TextInput::make('padding')->numeric()->required()->default(6)->columnSpan(3),
                        TextInput::make('current_value')->numeric()->required()->default(0)->columnSpan(3),
                        Select::make('reset_policy')->options(['none' => 'None', 'yearly' => 'Yearly', 'monthly' => 'Monthly'])->required()->default('none')->columnSpan(3),
                        TextInput::make('branch_id')->uuid()->columnSpan(3),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Customer')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('code')->required()->maxLength(40)->columnSpan(4),
                        TextInput::make('name')->required()->maxLength(200)->columnSpan(8),
                        TextInput::make('email')->email()->maxLength(255)->columnSpan(4),
                        TextInput::make('npwp')->maxLength(40)->columnSpan(4),
                        TextInput::make('payment_terms_days')->numeric()->default(0)->columnSpan(4),
                        TextInput::make('pic_name')->maxLength(150)->columnSpan(6),
                        TextInput::make('pic_phone')->maxLength(30)->columnSpan(6),
                        Textarea::make('billing_address')->columnSpanFull(),
                        Textarea::make('notes')->columnSpanFull(),
                    ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Taxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Pajak')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(100)->columnSpan(4),
                        TextInput::make('rate')->required()->numeric()->default(0)->columnSpan(4),
                        Select::make('applies_to')->options(['item' => 'Item', 'document' => 'Document'])->required()->columnSpan(4),
                        Select::make('is_active')->options([1 => 'Active', 0 => 'Inactive'])->default(1)->required()->columnSpan(4),
                    ]),
            ]);
    }
}

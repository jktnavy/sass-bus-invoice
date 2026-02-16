<?php

namespace App\Filament\Resources\Items\Schemas;

use App\Models\Tax;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Item')
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        TextInput::make('code')->required()->maxLength(40)->columnSpan(4),
                        TextInput::make('name')->required()->maxLength(200)->columnSpan(8),
                        Select::make('type')->options(['service' => 'Service', 'good' => 'Good'])->required()->columnSpan(4),
                        TextInput::make('uom')->required()->maxLength(20)->default('unit')->columnSpan(4),
                        TextInput::make('default_price')->required()->numeric()->default(0)->columnSpan(4),
                        Select::make('tax_id')
                            ->options(fn () => Tax::query()->where('is_active', 1)->pluck('name', 'id')->all())
                            ->searchable()
                            ->columnSpan(6),
                    ]),
            ]);
    }
}

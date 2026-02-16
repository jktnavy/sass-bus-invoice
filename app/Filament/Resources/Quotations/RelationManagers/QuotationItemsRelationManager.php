<?php

namespace App\Filament\Resources\Quotations\RelationManagers;

use App\Models\Item;
use App\Models\Tax;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotationItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('item_id')->options(fn () => Item::query()->pluck('name', 'id')->all())->searchable(),
            TextInput::make('name')->required(),
            TextInput::make('description'),
            TextInput::make('qty')->numeric()->required()->default(1),
            TextInput::make('uom')->required()->default('unit'),
            TextInput::make('price')->numeric()->required()->default(0),
            TextInput::make('discount')->numeric()->default(0),
            Select::make('tax_id')->options(fn () => Tax::query()->pluck('name', 'id')->all()),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('qty'),
                TextColumn::make('price')->money('IDR'),
                TextColumn::make('line_total')->money('IDR'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

}

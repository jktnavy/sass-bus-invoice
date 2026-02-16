<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->searchable(),
                TextColumn::make('date')->date(),
                TextColumn::make('customer.name')->label('Customer')->searchable(),
                TextColumn::make('method'),
                TextColumn::make('amount')->money('IDR'),
                TextColumn::make('unapplied_amount')->money('IDR'),
                TextColumn::make('status'),
            ])
            ->filters([
                SelectFilter::make('status')->options([0 => 'Draft', 1 => 'Posted', 2 => 'Reversed']),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}

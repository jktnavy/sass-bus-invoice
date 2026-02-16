<?php

namespace App\Filament\Resources\Items\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('type'),
                TextColumn::make('default_price')->money('IDR'),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                Action::make('clone')
                    ->label('Clone Record')
                    ->icon('heroicon-o-squares-plus')
                    ->action(function ($record): void {
                        $new = $record->replicate();
                        $new->code = Str::limit($record->code, 32, '').'-COPY-'.Str::upper(Str::random(4));
                        $new->name = $record->name.' (Copy)';
                        $new->save();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

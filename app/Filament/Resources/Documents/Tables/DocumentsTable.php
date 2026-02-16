<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('owner_table'),
                TextColumn::make('filename'),
                TextColumn::make('mime'),
                TextColumn::make('size'),
                TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([])
            ->recordActions([
                Action::make('open')
                    ->label('Open PDF')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => route('documents.open', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => route('documents.download', ['id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([]);
    }
}
